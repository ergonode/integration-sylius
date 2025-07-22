<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Storage;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

interface VariantCodesStorageInterface
{
    public function initialize(IntegrationInterface $integration): void;

    public function getVariantByCode(string $code): ?array;

    public function hasVariantByCode(string $code): bool;

    public function storeVariant(array $variantNode): void;

    public function getVariantLists(): array;
}
