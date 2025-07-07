<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\Client;

use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryAttributesList;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryDeletedStream;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryStream;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Category\CategoryTreeLeafCodes;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;

final readonly class CategoryApiClient extends AbstractErgonodeClient implements CategoryApiClientInterface
{
    private const LIMIT = 40;

    public function getCategories(IntegrationInterface $integration): array
    {
        return $this->query(new CategoryStream(
            self::LIMIT,
            $integration->getCategoryStreamCursorEnd(),
        ), $integration);
    }

    public function getCategoryAttributes(IntegrationInterface $integration, string $code, ?string $endCursor = null): array
    {
        return $this->query(new CategoryAttributesList(
            self::LIMIT,
            $code,
            $endCursor ?? $integration->getCategoryDeletedStreamCursorEnd(),
        ), $integration);
    }

    public function getCategoryTreeLeafCodes(IntegrationInterface $integration): array
    {
        if (null === $categoryTreeCode = $integration->getCategoryTreeCode()) {
            throw SynchronizationException::missingCategoryTreeCode($integration);
        }

        return $this->query(new CategoryTreeLeafCodes(
            $categoryTreeCode,
        ), $integration);
    }

    public function getCategoryDeleted(IntegrationInterface $integration, ?string $endCursor = null): array
    {
        return $this->query(new CategoryDeletedStream(
            self::LIMIT,
            $endCursor ?? $integration->getCategoryDeletedStreamCursorEnd(),
        ), $integration);
    }
}
