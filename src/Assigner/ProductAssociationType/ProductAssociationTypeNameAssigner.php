<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\ProductAssociationType;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;

final readonly class ProductAssociationTypeNameAssigner implements ProductAssociationTypeNameAssignerInterface
{
    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        ProductAssociationTypeInterface $attribute,
        array $names,
    ): void {
        foreach ($names as $name) {
            $language = $name['language'];
            $languageMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $language,
                RelationType::LANGUAGE_EXTERNAL_ID,
            );

            if (null === $languageMapping) {
                continue;
            }

            if (null === $name['value']) {
                continue;
            }

            $translation = $attribute->getTranslation($languageMapping->getSyliusValue());
            $translation->setName($name['value']);
        }
    }
}
