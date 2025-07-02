<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Checker\Product;

use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;

final class ProductTranslatablePropertyChecker implements ProductTranslatablePropertyCheckerInterface
{
    public function check(ProductPropertyMapping $productPropertyMapping): bool
    {
        return in_array(
            $productPropertyMapping,
            [
                ProductPropertyMapping::NAME,
                ProductPropertyMapping::DESCRIPTION,
                ProductPropertyMapping::SLUG,
                ProductPropertyMapping::SHORT_DESCRIPTION,
                ProductPropertyMapping::META_KEYWORDS,
                ProductPropertyMapping::META_DESCRIPTION,
            ],
            true,
        );
    }
}
