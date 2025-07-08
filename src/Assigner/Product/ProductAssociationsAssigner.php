<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Storage\ProductAssociationTypeStorageInterface;
use BitBag\SyliusErgonodePlugin\Storage\ProductsRelationsStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ProductAssociationsAssigner implements ProductAssociationsAssignerInterface
{
    public function __construct(
        private readonly ProductsRelationsStorageInterface $productsRelationsStorage,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductAssociationTypeStorageInterface $productAssociationTypeStorage,
        private readonly FactoryInterface $productAssociationFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function assign(): void
    {
        $products = $this->getMappedProducts();

        $productRelations = $this->productsRelationsStorage->getAssociations();
        $productAssociations = [];

        foreach ($productRelations as $code => $associations) {
            /** @var ProductInterface $product */
            $product = $products[$code];
            foreach ($associations as $association) {
                try {
                    $associationTypeCode = $association['type'];
                    $associatedProduct = $products[$association['associated']];

                    if (null === $associatedProduct) {
                        continue;
                    }

                    if (!is_string($associationTypeCode)) {
                        throw ProductSynchronizationException::invalidAssociationTypeCode($associationTypeCode);
                    }

                    if (!array_key_exists($code, $productAssociations)) {
                        $productAssociations[$code] = [];
                    }

                    if (array_key_exists($associationTypeCode, $productAssociations[$code])) {
                        $association = $productAssociations[$code][$associationTypeCode];
                        $association->addAssociatedProduct($associatedProduct);

                        continue;
                    }

                    $association = $this->findFirstAssociationInProduct($product, $associatedProduct, $associationTypeCode);
                    if (null === $association) {
                        $association = $this->createAssociation($product, $associationTypeCode);
                    }

                    $productAssociations[$code][$associationTypeCode] = $association;
                    $association->addAssociatedProduct($associatedProduct);
                } catch(\Throwable $e) {
                    $this->logger->error('An error occurred while assigning product associations', [
                        'message' => $e->getMessage(),
                        'product' => $product->getCode(),
                        'associationTypeCode' => is_string($associationTypeCode) ? $associationTypeCode : 'not a string',
                        'associatedProduct' => $associatedProduct?->getCode(),
                    ]);
                }
            }

            $this->entityManager->flush();
        }
    }

    private function createAssociation(ProductInterface $product, string $associationTypeCode): ProductAssociationInterface
    {
        $associationType = $this->productAssociationTypeStorage->getAssociationByCode($associationTypeCode);
        if (null !== $associationType) {
            throw ProductSynchronizationException::missingAssociation($associationTypeCode);
        }

        /** @var ProductAssociationInterface $productAssociation */
        $productAssociation = $this->productAssociationFactory->createNew();
        $productAssociation->setType($associationType);
        $productAssociation->setOwner($product);

        $this->entityManager->persist($productAssociation);

        return $productAssociation;
    }

    public function findFirstAssociationInProduct(ProductInterface $product, mixed $associatedProduct, string $associationTypeCode): ?ProductAssociationInterface
    {
        return $product->getAssociations()->findFirst(
            function (int|string $key, ProductAssociationInterface $productAssociation) use (
                $associatedProduct,
                $associationTypeCode
            ): bool {
                return $associationTypeCode === $productAssociation->getType()?->getCode() && $productAssociation->hasAssociatedProduct(
                    $associatedProduct,
                );
            },
        );
    }

    public function getMappedProducts(): array
    {
        /** @var array<ProductInterface> $products */
        $products = $this->productRepository->findBy(['code' => $this->productsRelationsStorage->getProductsCodes()]);

        $mappedProducts = array_map(
            function (ProductInterface $product) {
                return [$product->getCode() => $product];
            },
            $products,
        );

        return array_merge(...$mappedProducts);
    }
}
