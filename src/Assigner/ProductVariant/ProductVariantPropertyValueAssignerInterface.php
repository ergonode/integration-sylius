<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\ProductVariant;

use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface ProductVariantPropertyValueAssignerInterface
{
    public function assign(
        ProductVariantInterface $productVariant,
        ProductVariantPropertyMapping $propertyMapping,
        ResourceRelationMappingInterface $resourceRelationMapping,
        array $productValue,
    ): void;
}
