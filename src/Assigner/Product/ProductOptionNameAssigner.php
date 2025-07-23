<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;

final readonly class ProductOptionNameAssigner implements ProductOptionNameAssignerInterface
{
    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private TranslationAssignerInterface $productOptionTranslationAssigner,
        private LoggerInterface $logger,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        ProductOptionInterface $productOption,
        array $bindingAttributeNames,
    ): void {
        foreach ($bindingAttributeNames as $name) {
            $language = $name['language'];
            $languageMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $language,
                RelationType::LANGUAGE_EXTERNAL_ID,
            );
            $localeCode = $languageMapping?->getSyliusValue();

            if (null === $localeCode) {
                $this->logger->info(sprintf('Locale with code "%s" not found.', $language));

                continue;
            }

            if (null === $name['value']) {
                continue;
            }

            $translation = $productOption->getTranslation($localeCode);
            if ($localeCode !== $translation->getLocale()) {
                $this->productOptionTranslationAssigner->assign($productOption, $localeCode);
                $translation = $productOption->getTranslation($localeCode);
            }

            $translation->setName($name['value']);
        }
    }
}
