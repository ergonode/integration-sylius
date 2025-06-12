<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Checker\Category;

use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;

interface CategoryTranslatablePropertyCheckerInterface
{
    public function check(?CategoryPropertyMapping $categoryPropertyMapping): bool;
}
