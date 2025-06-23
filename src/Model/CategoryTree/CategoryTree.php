<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Model\CategoryTree;

final class CategoryTree
{
    public function __construct(
        private readonly string $code,
        private readonly CategoryTreeLeafList $categoryTreeLeafList,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCategoryTreeLeafList(): CategoryTreeLeafList
    {
        return $this->categoryTreeLeafList;
    }
}
