<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Checker\Category;

use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;

final class CategoryTranslatablePropertyChecker implements CategoryTranslatablePropertyCheckerInterface
{
    public function check(?CategoryPropertyMapping $categoryPropertyMapping): bool
    {
        return null !== $categoryPropertyMapping && in_array(
            $categoryPropertyMapping,
            [
                CategoryPropertyMapping::DESCRIPTION,
                CategoryPropertyMapping::SLUG,
            ],
            true,
        );
    }
}
