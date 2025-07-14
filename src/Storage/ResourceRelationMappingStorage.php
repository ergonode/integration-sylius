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
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Repository\ResourceRelationMappingRepositoryInterface;

final class ResourceRelationMappingStorage implements ResourceRelationMappingStorageInterface
{
    private array $resolvedIdentifiers = [];

    public function __construct(
        private readonly ResourceRelationMappingRepositoryInterface $resourceRelationMappingRepository,
        private readonly RelationType $relationType,
    ) {
    }

    public function preload(IntegrationInterface $integration): void
    {
        $mappings = $this->resourceRelationMappingRepository->findAllByIntegrationAndType($integration, $this->relationType);

        $this->resolvedIdentifiers[$integration->getId()] = $mappings;
    }

    public function resolveForSyliusValue(IntegrationInterface $integration, string $syliusValue): ?ResourceRelationMappingInterface
    {
        if ([] === $this->resolvedIdentifiers) {
            $this->preload($integration);
        }

        $mappingArray = array_filter($this->resolvedIdentifiers[$integration->getId()], function (ResourceRelationMappingInterface $mapping) use ($syliusValue) {
            return $mapping->getSyliusValue() === $syliusValue;
        });

        return array_shift($mappingArray);
    }

    public function resolveForErgonodeValue(IntegrationInterface $integration, string $ergonodeValue): ?ResourceRelationMappingInterface
    {
        if ([] === $this->resolvedIdentifiers) {
            $this->preload($integration);
        }

        $mappingArray = array_filter(
            $this->resolvedIdentifiers[$integration->getId()],
            function (ResourceRelationMappingInterface $mapping) use ($ergonodeValue) {
                return $mapping->getErgonodeValue() === $ergonodeValue;
            },
        );

        return array_shift($mappingArray);
    }

    public function getMappings(IntegrationInterface $integration): array
    {
        if ([] === $this->resolvedIdentifiers) {
            $this->preload($integration);
        }

        return $this->resolvedIdentifiers[$integration->getId()];
    }
}
