<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Collector;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

interface DeletedAttributesCollectorInterface
{
    public function collect(IntegrationInterface $integration): void;

    public function isInDeletedList(string $attributeCode): bool;

    public function getDeletedList(): array;

    public function getCursorEnd(): string;
}
