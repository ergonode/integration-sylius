<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Checker\Variant;

use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;

interface ProductVariantTranslatablePropertyCheckerInterface
{
    public function check(ProductVariantPropertyMapping $productVariantPropertyMapping): bool;
}
