<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\DependencyInjection\Registry;

use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Storage\ResourceRelationMappingStorageInterface;

final class ResourceRelationMappingStorageRegistry implements ResourceRelationMappingStorageRegistryInterface
{
    /** @var array<string, ResourceRelationMappingStorageInterface> */
    private array $storages = [];

    public function addStorage(RelationType $relationType, ResourceRelationMappingStorageInterface $storage): void
    {
        $this->storages[$relationType->value] = $storage;
    }

    public function hasStorage(RelationType $relationType): bool
    {
        return isset($this->storages[$relationType->value]);
    }

    public function getStorage(RelationType $relationType): ResourceRelationMappingStorageInterface
    {
        if (!isset($this->storages[$relationType->value])) {
            throw new \InvalidArgumentException(
                sprintf('No storage found for relation type: %s', $relationType->value),
            );
        }

        return $this->storages[$relationType->value];
    }
}
