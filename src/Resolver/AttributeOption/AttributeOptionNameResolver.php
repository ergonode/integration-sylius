<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver\AttributeOption;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;

final readonly class AttributeOptionNameResolver implements AttributeOptionNameResolverInterface
{
    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
    ) {
    }

    public function resolve(array $optionNames, IntegrationInterface $integration): array
    {
        $names = [];
        foreach ($optionNames as $name) {
            $language = $name['language'];
            $languageMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $language,
                RelationType::LANGUAGE_EXTERNAL_ID,
            );

            if (null === $languageMapping || null === $name['value']) {
                continue;
            }
            $names[$languageMapping->getSyliusValue()] = $name['value'];
        }

        return $names;
    }
}
