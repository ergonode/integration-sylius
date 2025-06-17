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
use BitBag\SyliusErgonodePlugin\Downloader\ErgonodeFileDownloader;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\CategorySynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use Sylius\Component\Core\Model\TaxonImageInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Resource\Factory\FactoryInterface;

readonly class TaxonPropertyValueAssigner implements TaxonPropertyValueAssignerInterface
{
    public function __construct(
        private MainLanguageContextInterface $mainLanguageContext,
        private ErgonodeFileDownloader $ergonodeFileDownloader,
        private FactoryInterface $taxonImageFactory,
        private TextValueResolverInterface $textValueResolver,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        TaxonInterface $taxon,
        ResourceRelationMappingInterface $propertyMapping,
        array $attributeValue,
    ): void {
        if (null === $propertyMapping->getSyliusValue()) {
            throw CategorySynchronizationException::missingSyliusValue($propertyMapping);
        }

        switch (CategoryPropertyMapping::tryFrom($propertyMapping->getSyliusValue())) {
            case CategoryPropertyMapping::DESCRIPTION:
                $this->assignDescription($taxon, $attributeValue);

                return;
            case CategoryPropertyMapping::SLUG:
                $this->assignSlug($taxon, $attributeValue);

                return;
            case CategoryPropertyMapping::ENABLED:
                $this->assignEnabled($integration, $attributeValue);

                return;
            case CategoryPropertyMapping::MEDIA:
                $this->assignMedia($taxon, $integration, $attributeValue);

                return;
        }
    }

    private function assignDescription(TaxonInterface $taxon, array $attributeValue): void
    {
        foreach ($attributeValue as $locale => $value) {
            $value = $this->textValueResolver->resolve($locale, $value);
            if (null === $value) {
                continue;
            }

            $translation = $taxon->getTranslation($locale);
            if ($locale !== $translation->getLocale()) {
                continue;
            }

            $translation->setDescription($value);
        }
    }

    private function assignSlug(TaxonInterface $taxon, array $attributeValue): void
    {
        foreach ($attributeValue as $locale => $value) {
            $value = $this->textValueResolver->resolve($locale, $value);
            if (null === $value) {
                continue;
            }

            $translation = $taxon->getTranslation($locale);
            if ($locale !== $translation->getLocale()) {
                continue;
            }

            $translation->setSlug($value);
        }
    }

    private function assignEnabled(IntegrationInterface $integration, array $attributeValue): bool
    {
        $mainLanguage = $this->mainLanguageContext->getMainLanguageMapping($integration);
        $mainValue = $attributeValue[$mainLanguage->getErgonodeValue()] ?? null;

        if (is_array($mainValue) && array_key_exists('code', $mainValue)) {
            return (bool) $mainValue['code'];
        }

        return (bool) ($mainValue ?? false);
    }

    private function assignMedia(TaxonInterface $taxon, IntegrationInterface $integration, array $attributeValue): void
    {
        $mainLanguage = $this->mainLanguageContext->getMainLanguageMapping($integration);
        $mainLanguageValue = $attributeValue[$mainLanguage->getSyliusValue()] ?? [];

        if ([] === $mainLanguageValue) {
            return;
        }

        /** @var array $value */
        foreach ($mainLanguageValue as $value) {
            /** @var TaxonImageInterface $image */
            $image = $this->taxonImageFactory->createNew();
            $this->ergonodeFileDownloader->download($value, $image);

            $taxon->addImage($image);
        }
    }
}
