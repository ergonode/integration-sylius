<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Collector;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

interface DeletedProductsCollectorInterface
{
    public function collect(IntegrationInterface $integration): void;

    public function isInDeletedList(string $productSku): bool;

    public function getDeletedList(): array;

    public function getCursorEnd(): ?string;
}
