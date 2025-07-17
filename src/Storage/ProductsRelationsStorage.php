<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Storage;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Webmozart\Assert\Assert;

final class ProductsRelationsStorage implements ProductsRelationsStorageInterface
{
    private array $associations = [];

    private array $productsCodes = [];

    public function storeAssociation(IntegrationInterface $integration, ProductInterface $product, string $associationCode, array $associatedSkus): void
    {
        $integrationCode = $integration->getCode();
        Assert::notNull($integrationCode);

        $productCode = $product->getCode();
        $this->associations[$integrationCode][$productCode][] = [
            'type' => $associationCode,
            'associated' => array_shift($associatedSkus),
        ];

        $this->productsCodes[$integrationCode][] = $productCode;

        /** @var string $associatedSku */
        foreach ($associatedSkus as $associatedSku) {
            $this->productsCodes[$integrationCode][] = $associatedSku;
        }
    }

    public function getProductsCodes(IntegrationInterface $integration): array
    {
        $integrationCode = $integration->getCode();
        Assert::notNull($integrationCode);

        return $this->productsCodes[$integrationCode] ?? [];
    }

    public function getAssociations(IntegrationInterface $integration): array
    {
        $integrationCode = $integration->getCode();
        Assert::notNull($integrationCode);

        return $this->associations[$integrationCode] ?? [];
    }
}
