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
use Doctrine\ORM\QueryBuilder;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/**
 * @template T of IntegrationInterface
 *
 * @extends RepositoryInterface<T>
 */
interface IntegrationRepositoryInterface extends RepositoryInterface
{
    public function createEnabledQueryBuilder(): QueryBuilder;

    public function findOneEnabledById(int $id): ?IntegrationInterface;
}
