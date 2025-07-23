<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Taxon;

use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\CategoryNode;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\LocalizedText;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final readonly class TaxonNameAssigner implements TaxonNameAssignerInterface
{
    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private TranslationAssignerInterface $taxonTranslationAssigner,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        TaxonInterface $taxon,
        CategoryNode $categoryNode,
    ): void {
        /** @var LocalizedText $name */
        foreach ($categoryNode->getName() as $name) {
            $language = $name->getLanguage();
            $languageMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $language,
                RelationType::LANGUAGE_EXTERNAL_ID,
            );

            $localeCode = $languageMapping?->getSyliusValue();

            if (null === $localeCode) {
                continue;
            }

            if (null === $name->getValue()) {
                continue;
            }

            $translation = $taxon->getTranslation($localeCode);
            if ($localeCode !== $translation->getLocale()) {
                $this->taxonTranslationAssigner->assign($taxon, $localeCode);
                $translation = $taxon->getTranslation($localeCode);
            }

            $translation->setName($name->getValue());
        }
    }
}
