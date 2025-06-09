<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\Client;

use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryStream;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryTreeDeletedStream;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryTreeLeafCodes;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryTreeStream;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final class CategoryApiClient extends AbstractErgonodeClient
{
    private const LIMIT = 40;

    public function getCategories(IntegrationInterface $integration): array
    {
        return $this->query(new CategoryStream(
            self::LIMIT,
            $integration->getCategoryStreamCursorEnd(),
        ), $integration);
    }

    public function getCategoryTreeLeafCodes(IntegrationInterface $integration): array
    {
        return $this->query(new CategoryTreeLeafCodes(
            $integration->getCategoryTreeCode(),
        ), $integration);
    }

    public function getCategoryTrees(IntegrationInterface $integration): array
    {
        return $this->query(new CategoryTreeStream(
            self::LIMIT,
            $integration->getCategoryTreeStreamCursorEnd(),
        ), $integration);
    }

    public function getCategoryTreeDeleted(IntegrationInterface $integration): array
    {
        return $this->query(new CategoryTreeDeletedStream(
            self::LIMIT,
            $integration->getCategoryTreeDeletedStreamCursorEnd(),
        ), $integration);
    }

    public function getCategoryDeleted(IntegrationInterface $integration): array
    {
        return $this->query(new CategoryTreeStream(
            self::LIMIT,
            $integration->getCategoryDeletedStreamCursorEnd(),
        ), $integration);
    }
}
