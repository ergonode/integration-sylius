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
use BitBag\SyliusErgonodePlugin\Repository\ResourceRelationMappingRepositoryInterface;

final class ResourceRelationMappingResolver implements ResourceRelationMappingResolverInterface
{
    private array $resolvedIdentifiers = [];

    public function __construct(
        private readonly ResourceRelationMappingRepositoryInterface $resourceRelationMappingRepository,
    ) {
    }

    public function resolveForSyliusValue(IntegrationInterface $integration, string $syliusValue, RelationType $relationType): ?ResourceRelationMappingInterface
    {
        $identifier = sprintf(
            '%s-%s-sylius-%s',
            $integration->getId(),
            $relationType->value,
            $syliusValue,
        );

        if (!array_key_exists($identifier, $this->resolvedIdentifiers)) {
            $this->resolvedIdentifiers[$identifier] = $this->resourceRelationMappingRepository->findOneByIntegrationAndTypeAndSyliusValue(
                $integration,
                $relationType,
                $syliusValue,
            );
        }

        return $this->resolvedIdentifiers[$identifier];
    }

    public function resolveForErgonodeValue(IntegrationInterface $integration, string $ergonodeValue, RelationType $relationType): ?ResourceRelationMappingInterface
    {
        $identifier = sprintf(
            '%s-%s-ergonode-%s',
            $integration->getId(),
            $relationType->value,
            $ergonodeValue,
        );

        if (!array_key_exists($identifier, $this->resolvedIdentifiers)) {
            $this->resolvedIdentifiers[$identifier] = $this->resourceRelationMappingRepository->findOneByIntegrationAndTypeAndErgonodeValue(
                $integration,
                $relationType,
                $ergonodeValue,
            );
        }

        return $this->resolvedIdentifiers[$identifier];
    }
}
