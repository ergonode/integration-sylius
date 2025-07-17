<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Synchronizer;

use BitBag\SyliusErgonodePlugin\Collector\DeletedProductsCollectorInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Manager\Product\ProductAssociationSynchronizationManagerInterface;
use BitBag\SyliusErgonodePlugin\Manager\Product\ProductDeletedSynchronizationManagerInterface;
use BitBag\SyliusErgonodePlugin\Manager\Product\ProductSynchronizationManagerInterface;
use BitBag\SyliusErgonodePlugin\Manager\Product\ProductVariantsSynchronizationManagerInterface;
use BitBag\SyliusErgonodePlugin\Storage\VariantCodesStorageInterface;

final class ProductSynchronizer implements SynchronizerInterface
{
    public function __construct(
        private readonly ProductSynchronizationManagerInterface $productSynchronizationManager,
        private readonly ProductVariantsSynchronizationManagerInterface $productVariantsSynchronizationManager,
        private readonly ProductDeletedSynchronizationManagerInterface $productDeletedSynchronizationManager,
        private readonly ProductAssociationSynchronizationManagerInterface $productAssociationSynchronizationManager,
        private readonly VariantCodesStorageInterface $variantCodesStorage,
        private readonly DeletedProductsCollectorInterface $deletedProductsCollector,
    ) {
    }

    public function synchronize(IntegrationInterface $integration): void
    {
        $this->variantCodesStorage->initialize($integration);
        $this->deletedProductsCollector->collect($integration);
        $this->productSynchronizationManager->manage($integration);
        $this->productAssociationSynchronizationManager->synchronizeAssociations($integration);
        $this->productVariantsSynchronizationManager->synchronizeVariants($integration);
        $this->productDeletedSynchronizationManager->synchronizeDeletedProducts($integration);
    }
}
