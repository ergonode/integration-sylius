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

interface ResourceRelationMappingStorageRegistryInterface
{
    public function addStorage(RelationType $relationType, ResourceRelationMappingStorageInterface $storage): void;

    public function getStorage(RelationType $relationType): ResourceRelationMappingStorageInterface;

    public function hasStorage(RelationType $relationType): bool;
}
