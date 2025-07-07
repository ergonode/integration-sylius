<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use Sylius\Component\Product\Model\ProductAttributeValueInterface;

interface StorageBasedProductAttributeValueAssignerInterface
{
    public function assign(ProductAttributeValueInterface $attributeValue, mixed $value): void;
}
