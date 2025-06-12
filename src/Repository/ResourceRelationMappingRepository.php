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
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class ResourceRelationMappingRepository extends EntityRepository implements ResourceRelationMappingRepositoryInterface
{
    public function createIntegrationAndTypeQueryBuilder(
        IntegrationInterface $integration,
        RelationType $type
    ): QueryBuilder
    {
        return $this->createQueryBuilder('rrm')
            ->andWhere('rrm.relationType = :type')
            ->andWhere('rrm.integration = :integration')
            ->setParameter('type', $type->value)
            ->setParameter('integration', $integration)
        ;
    }

    public function findOneByIntegrationAndTypeAndSyliusValue(
        IntegrationInterface $integration,
        RelationType $type,
        string $syliusValue
    ): ?ResourceRelationMappingInterface {
        return $this->createIntegrationAndTypeQueryBuilder($integration, $type)
            ->andWhere('rrm.syliusValue = :syliusValue')
            ->setParameter('syliusValue', $syliusValue)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByIntegrationAndTypeAndErgonodeValue(
        IntegrationInterface $integration,
        RelationType $type,
        string $ergonodeValue
    ): ?ResourceRelationMappingInterface
    {
        return $this->createIntegrationAndTypeQueryBuilder($integration, $type)
            ->andWhere('rrm.ergonodeValue = :ergonodeValue')
            ->setParameter('ergonodeValue', $ergonodeValue)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
