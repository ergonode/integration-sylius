<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Provider;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Storage\ProductsRelationsStorageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

final class MappedProductsProvider implements MappedProductsProviderInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductsRelationsStorageInterface $productsRelationsStorage,
    ) {
    }

    public function provide(IntegrationInterface $integration): array
    {
        /** @var array<ProductInterface> $products */
        $products = $this->productRepository->findBy(['code' => $this->productsRelationsStorage->getProductsCodes($integration)]);

        $mappedProducts = array_map(
            function (ProductInterface $product) {
                return [$product->getCode() => $product];
            },
            $products,
        );

        return array_merge(...$mappedProducts);
    }
}
