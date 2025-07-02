<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\Client;

use BitBag\SyliusErgonodePlugin\API\RequestModel\Product\ProductStream;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final readonly class ProductApiClient extends AbstractErgonodeClient implements ProductApiClientInterface
{
    private const LIMIT = 50;

    public function getProducts(IntegrationInterface $integration, ?string $cursorEnd = null): array
    {
        return $this->query(new ProductStream(
            self::LIMIT,
            $cursorEnd,
        ), $integration);
    }
}
