<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Storage;

use Sylius\Component\Core\Model\ProductInterface;

final class ProductsRelationsStorage implements ProductsRelationsStorageInterface
{
    private array $associations = [];

    private array $productsCodes = [];

    public function storeAssociation(ProductInterface $product, string $associationCode, array $associatedSkus): void
    {
        $productCode = $product->getCode();
        $this->associations[$productCode][] = [
            'type' => $associationCode,
            'associated' => array_shift($associatedSkus),
        ];

        $this->productsCodes[] = $productCode;

        /** @var string $associatedSku */
        foreach ($associatedSkus as $associatedSku) {
            $this->productsCodes[] = $associatedSku;
        }
    }

    public function getProductsCodes(): array
    {
        return $this->productsCodes;
    }

    public function getAssociations(): array
    {
        return $this->associations;
    }
}
