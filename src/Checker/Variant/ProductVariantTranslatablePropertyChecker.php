<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Checker\Variant;

use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;

final class ProductVariantTranslatablePropertyChecker implements ProductVariantTranslatablePropertyCheckerInterface
{
    public function check(ProductVariantPropertyMapping $productVariantPropertyMapping): bool
    {
        return $productVariantPropertyMapping === ProductVariantPropertyMapping::NAME;
    }
}
