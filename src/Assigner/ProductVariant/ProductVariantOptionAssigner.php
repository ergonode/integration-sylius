<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\ProductVariant;

use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ProductVariantOptionAssigner implements ProductVariantOptionAssignerInterface
{
    public function __construct(
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly MainLanguageContextInterface $mainLanguageContext,
        private readonly FactoryInterface $productOptionValueTranslationFactory,
        private readonly FactoryInterface $productOptionValueFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        ProductVariantInterface $productVariant,
        ProductOptionInterface $productOption,
        array $attributeValues,
    ): void {
        $optionValue = $productVariant->getOptionValues()->findFirst(function (int $key, ProductOptionValueInterface $optionValue) use ($productOption) {
            return $optionValue->getOption()?->getCode() === $productOption->getCode();
        });

        if (null === $optionValue) {
            $optionValue = $this->createOptionValue($productOption);
        }

        $mainLanguageMapping = $this->mainLanguageContext->getMainLanguageMapping($integration);
        foreach ($attributeValues as $value) {
            $language = $value['language'];
            $bindingValue = $value['value'];

            if ($language !== $mainLanguageMapping->getErgonodeValue()) {
                continue;
            }
            $optionValue->setCode($bindingValue['code']);
            $productVariant->addOptionValue($optionValue);
            $this->entityManager->persist($optionValue);

            $bindingValueName = $bindingValue['name'];

            foreach ($bindingValueName as $name) {
                $nameLanguage = $name['language'];

                if (null === $nameValue = $name['value']) {
                    continue;
                }

                $languageMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                    $integration,
                    $nameLanguage,
                    RelationType::LANGUAGE_EXTERNAL_ID,
                );

                $localeCode = $languageMapping?->getSyliusValue();
                if (null === $localeCode) {
                    continue;
                }

                /** @var ProductOptionValueTranslationInterface $translation */
                $translation = $optionValue->getTranslation($localeCode);
                if ($localeCode !== $translation->getLocale()) {
                    /** @var ProductOptionValueTranslationInterface $translation */
                    $translation = $this->productOptionValueTranslationFactory->createNew();
                    $translation->setLocale($localeCode);
                    $optionValue->addTranslation($translation);
                }
                $translation->setValue($nameValue);
            }
        }
    }

    private function createOptionValue(
        ProductOptionInterface $productOption,
    ): ProductOptionValueInterface {
        /** @var ProductOptionValueInterface $productOptionValue */
        $productOptionValue = $this->productOptionValueFactory->createNew();
        $productOptionValue->setOption($productOption);

        return $productOptionValue;
    }
}
