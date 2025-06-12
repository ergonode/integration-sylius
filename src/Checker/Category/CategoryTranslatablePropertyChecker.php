<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Checker\Category;

use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;

final class CategoryTranslatablePropertyChecker implements CategoryTranslatablePropertyCheckerInterface
{
    public function check(?CategoryPropertyMapping $categoryPropertyMapping): bool
    {
        return null !== $categoryPropertyMapping && in_array($categoryPropertyMapping, [
                CategoryPropertyMapping::NAME,
                CategoryPropertyMapping::DESCRIPTION,
                CategoryPropertyMapping::SLUG,
            ], true
        );
    }
}
