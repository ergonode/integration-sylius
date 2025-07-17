<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Manager\Product;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Provider\MappedProductsProviderInterface;
use BitBag\SyliusErgonodePlugin\Provider\ProductAssociationProviderInterface;
use BitBag\SyliusErgonodePlugin\Storage\ProductsRelationsStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class ProductAssociationSynchronizationManager implements ProductAssociationSynchronizationManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MappedProductsProviderInterface $mappedProductsProvider,
        private readonly ProductsRelationsStorageInterface $productsRelationsStorage,
        private readonly ProductAssociationProviderInterface $productAssociationProvider,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function synchronizeAssociations(IntegrationInterface $integration): void
    {
        try {
            $products = $this->mappedProductsProvider->provide($integration);

            $productRelations = $this->productsRelationsStorage->getAssociations($integration);
            $productAssociations = [];

            foreach ($productRelations as $code => $associations) {
                /** @var ProductInterface $product */
                $product = $products[$code];
                foreach ($associations as $association) {
                    try {
                        $associationTypeCode = $association['type'];
                        foreach ($association['associated'] as $associatedProductCode) {
                            $associatedProduct = $products[$associatedProductCode] ?? null;
                            if (null === $associatedProduct) {
                                $this->logger->info(sprintf('Associated product with code `%s` not found in Sylius', $associatedProductCode));

                                continue;
                            }

                            if (!is_string($associationTypeCode)) {
                                throw ProductSynchronizationException::invalidAssociationTypeCode($associationTypeCode);
                            }

                            if (!array_key_exists($code, $productAssociations)) {
                                $productAssociations[$code] = [];
                            }

                            if (!array_key_exists($associationTypeCode, $productAssociations[$code])) {
                                $productAssociations[$code][$associationTypeCode] = $this->productAssociationProvider->provide(
                                    $product,
                                    $associatedProduct,
                                    $associationTypeCode,
                                );
                            }

                            $association = $productAssociations[$code][$associationTypeCode];
                            $association->addAssociatedProduct($associatedProduct);
                        }
                    } catch(\Throwable $e) {
                        $this->logger->error('An error occurred while assigning product associations', [
                            'message' => $e->getMessage(),
                            'product' => $product->getCode(),
                            'associationTypeCode' => is_string($associationTypeCode) ? $associationTypeCode : 'not a string',
                            'associatedProduct' => $association['associated'],
                        ]);
                    }
                }

                $this->entityManager->flush();
            }
        } catch (\Throwable $e) {
            $this->logger->critical('An unexpected error occurred during assigning association process', [
                'message' => $e->getMessage(),
            ]);
        }
        $this->entityManager->flush();
    }
}
