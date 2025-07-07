<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;

interface ProductVariantAttributeValueResolverInterface
{
    public function resolve(
        IntegrationInterface $integration,
        ResourceRelationMappingInterface $mainLanguageMapping,
        string $typename,
        array $productVariantAttribute,
        array $productVariantAttributeValue,
        ?ResourceRelationMappingInterface $propertyMapping,
    ): array;
}
