<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\Client;

use BitBag\SyliusErgonodePlugin\API\RequestModel\Attribute\AttributeStream;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Attribute\DeletedAttributeStream;
use BitBag\SyliusErgonodePlugin\API\RequestModel\Attribute\SelectAttributeOptions;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final readonly class AttributeApiClient extends AbstractErgonodeClient implements AttributeApiClientInterface
{
    private const LIMIT = 40;

    public function getAttributes(IntegrationInterface $integration): array
    {
        return $this->query(new AttributeStream(
            self::LIMIT,
            $integration->getAttributeStreamCursorEnd(),
        ), $integration);
    }

    public function getDeletedAttributes(IntegrationInterface $integration): array
    {
        return $this->query(new DeletedAttributeStream(
            self::LIMIT,
            $integration->getAttributeDeletedStreamCursorEnd(),
        ), $integration);
    }

    public function getSelectAttributeOptions(IntegrationInterface $integration, string $attributeCode): array
    {
        return $this->query(new SelectAttributeOptions(
            $attributeCode,
            self::LIMIT,
            $integration->getAttributeStreamCursorEnd(),
        ), $integration);
    }
}
