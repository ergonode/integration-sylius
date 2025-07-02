<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use Sylius\Component\Core\Model\ProductInterface;

interface ProductPropertyValueAssignerInterface
{
    public function assign(
        ProductInterface $product,
        ProductPropertyMapping $propertyMapping,
        ResourceRelationMappingInterface $resourceRelationMapping,
        array $productValue,
    ): void;
}
