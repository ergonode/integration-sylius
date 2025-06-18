<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver\CategoryAttribute;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;

abstract readonly class ValueResolver implements ValueResolverInterface
{
    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
    ) {
    }

    protected function getSyliusLocaleCode(IntegrationInterface $integration, string $ergonodeLocaleCode): ?string
    {
        $mapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $ergonodeLocaleCode,
            RelationType::LANGUAGE_EXTERNAL_ID,
        );

        return $mapping?->getSyliusValue() ?? null;
    }
}
