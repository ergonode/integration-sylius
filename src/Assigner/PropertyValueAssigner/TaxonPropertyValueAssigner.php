<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner;

use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;
use Sylius\Resource\Factory\FactoryInterface;

final readonly class TaxonPropertyValueAssigner implements TaxonPropertyValueAssignerInterface
{
    private const MISSING_PROPERTY_LOG_FORMAT = 'Missing property relation mapping for integration id `%s`, relation type %s and value %s';

    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private MainLanguageContextInterface $mainLanguageContext,
        private CategoryAttributeValueResolverInterface $categoryAttributeValueResolver,
        private LoggerInterface $logger,
        private FactoryInterface $taxonTranslationFactory,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        TaxonInterface $taxon,
        array $attributeNode,
    ): void {
        [$typename, $attribute, $value] = $attributeNode;
        $ergonodeAttributeCode = $attribute['code'];

        $propertyMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $ergonodeAttributeCode,
            RelationType::ATTRIBUTE_PROPERTY,
        );

        if (null === $propertyMapping) {
            $this->logger->warning(
                sprintf(
                    self::MISSING_PROPERTY_LOG_FORMAT,
                    $integration->getId(),
                    $ergonodeAttributeCode,
                    RelationType::ATTRIBUTE_PROPERTY->value,
                ),
            );

            return;
        }

        $ergonodeLanguageMapping = $this->mainLanguageContext->getMainLanguageMapping($integration);

        $attributeValue = $this->categoryAttributeValueResolver->resolve(
            $ergonodeLanguageMapping,
            $propertyMapping,
            $typename,
            $value,
        );

        switch ($propertyMapping->getSyliusValue()) {
            case CategoryPropertyMapping::NAME:
                $this->assignName($integration, $taxon, $attributeValue);
                return;
            case CategoryPropertyMapping::DESCRIPTION:
                $this->assignDescription($integration, $taxon, $attributeValue);
                return;
        }
    }

    private function assignName(IntegrationInterface $integration, TaxonInterface $taxon, array $attributeValue): void
    {
        foreach ($attributeValue as $locale => $value) {
            $value = $this->resolveTextValue($integration, $taxon, $locale, (string) $value);
            $taxon->setName($value);
        }
    }

    private function assignDescription(IntegrationInterface $integration, TaxonInterface $taxon, array $attributeValue): void
    {
        foreach ($attributeValue as $locale => $value) {
            $value = $this->resolveTextValue($integration, $taxon, $locale, (string) $value);
            $taxon->setDescription($value);
        }
    }

    private function assignSlug(IntegrationInterface $integration, TaxonInterface $taxon, array $attributeValue): void
    {
        foreach ($attributeValue as $locale => $value) {
            $value = $this->resolveTextValue($integration, $taxon, $locale, (string) $value);
            $taxon->setSlug($value);
        }
    }

    private function resolveTextValue(IntegrationInterface $integration, TaxonInterface $taxon, string $locale, mixed $value): ?string
    {
        if ($this->isStringable($value)) {
            $this->logger->warning(sprintf('Value for locale %s is not stringable. Value: %s', $locale,
                json_encode($value)
            ));

            return null;
        }

        if (null === $syliusLocaleMapping = $this->resolveLocaleMapping($integration, $locale)) {
            return null;
        }

        if (null === $taxon->getTranslation($syliusLocaleMapping->getSyliusValue())) {
            $this->createTranslation($syliusLocaleMapping, $taxon);
        }

        $taxon->setCurrentLocale($syliusLocaleMapping->getSyliusValue());

        return $value;
    }

    private function resolveLocaleMapping(IntegrationInterface $integration, string $locale): ?ResourceRelationMappingInterface
    {
        return $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $locale,
            RelationType::LANGUAGE_EXTERNAL_ID
        );
    }

    public function createTranslation(
        ResourceRelationMappingInterface $syliusLocaleMapping,
        TaxonInterface $taxon
    ): TaxonTranslationInterface {
        /** @var TaxonTranslationInterface $translation */
        $translation = $this->taxonTranslationFactory->createNew();
        $translation->setLocale($syliusLocaleMapping->getSyliusValue());
        $taxon->addTranslation($translation);

        return $translation;
    }

    function isStringable(mixed $value): bool
    {
        if (is_null($value) || is_scalar($value)) {
            return true;
        }

        if ($value instanceof \Stringable) {
            return true;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return true;
        }

        return false;
    }
}
