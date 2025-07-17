<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Manager\Product;

use BitBag\SyliusErgonodePlugin\Collector\DeletedProductsCollectorInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;

final class ProductDeletedSynchronizationManager implements ProductDeletedSynchronizationManagerInterface
{
    public function __construct(
        private readonly DeletedProductsCollectorInterface $deletedProductsCollector,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function synchronizeDeletedProducts(IntegrationInterface $integration): void
    {
        $codes = $this->deletedProductsCollector->getDeletedList();

        foreach ($this->productRepository->findBy(['code' => $codes]) as $product) {
            $this->entityManager->remove($product);
        }

        foreach ($this->productVariantRepository->findBy(['code' => $codes]) as $variant) {
            $this->entityManager->remove($variant);
        }

        $integration->setProductDeletedStreamCursorEnd($this->deletedProductsCollector->getCursorEnd());

        $this->entityManager->flush();
    }
}
