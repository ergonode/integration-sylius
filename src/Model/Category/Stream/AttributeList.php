<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Model\Category\Stream;

class AttributeList
{
    private array $pageInfo;

    private array $edges;

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function setEdges(array $edges): void
    {
        $this->edges = $edges;
    }

    public function getPageInfo(): array
    {
        return $this->pageInfo;
    }

    public function setPageInfo(array $pageInfo): void
    {
        $this->pageInfo = $pageInfo;
    }
}
