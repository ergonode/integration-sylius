<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;

interface ResourceRelationMappingResolverInterface
{
    public function resolveForSyliusValue(
        IntegrationInterface $integration,
        string $syliusValue,
        RelationType $relationType,
    ): ?ResourceRelationMappingInterface;

    public function resolveForErgonodeValue(
        IntegrationInterface $integration,
        string $ergonodeValue,
        RelationType $relationType,
    ): ?ResourceRelationMappingInterface;
}
