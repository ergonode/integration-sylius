<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\Client;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

interface CategoryApiClientInterface
{
    public function getCategories(IntegrationInterface $integration): array;

    public function getCategoryAttributes(IntegrationInterface $integration, string $code, ?string $endCursor = null): array;

    public function getCategoryTreeLeafCodes(IntegrationInterface $integration): array;

    public function getCategoryDeleted(IntegrationInterface $integration, ?string $endCursor = null): array;
}
