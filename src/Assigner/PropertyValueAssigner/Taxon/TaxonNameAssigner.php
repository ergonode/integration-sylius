<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Taxon;

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

            if (null === $languageMapping) {
                continue;
            }

            if (null === $name->getValue()) {
                continue;
            }

            $translation = $taxon->getTranslation($languageMapping->getSyliusValue());
            $translation->setName($name->getValue());
        }
    }
}
