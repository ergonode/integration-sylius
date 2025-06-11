<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Repository;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use Doctrine\ORM\QueryBuilder;

interface ResourceRelationMappingRepositoryInterface
{
    public function createIntegrationAndTypeQueryBuilder(IntegrationInterface $integration, RelationType $type): QueryBuilder;

    public function findOneByIntegrationAndTypeAndSyliusValue(IntegrationInterface $integration, RelationType $type, string $syliusValue): ?ResourceRelationMappingInterface;

    public function findOneByIntegrationAndTypeAndErgonodeValue(IntegrationInterface $integration, RelationType $type, string $ergonodeValue): ?ResourceRelationMappingInterface;
}
