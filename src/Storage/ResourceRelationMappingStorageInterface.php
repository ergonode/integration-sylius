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
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;

interface ResourceRelationMappingStorageInterface
{
    public function preload(IntegrationInterface $integration): void;

    public function resolveForSyliusValue(
        IntegrationInterface $integration,
        string $syliusValue,
    ): ?ResourceRelationMappingInterface;

    public function resolveForErgonodeValue(
        IntegrationInterface $integration,
        string $ergonodeValue,
    ): ?ResourceRelationMappingInterface;
}
