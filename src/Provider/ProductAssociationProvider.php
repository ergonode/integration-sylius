<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Provider;

use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Preloader\ProductAssociationTypePreloaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class ProductAssociationProvider implements ProductAssociationProviderInterface
{
    public function __construct(
        private readonly ProductAssociationTypePreloaderInterface $productAssociationsPreloader,
        private readonly FactoryInterface $productAssociationFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(
        ProductInterface $product,
        ProductInterface $associatedProduct,
        string $associationTypeCode,
    ): ProductAssociationInterface {
        $association = $this->findFirstAssociationInProduct(
            $product,
            $associationTypeCode,
        );
        if (null === $association) {
            $association = $this->createAssociation($product, $associationTypeCode);
        }

        return $association;
    }

    private function createAssociation(ProductInterface $product, string $associationTypeCode): ProductAssociationInterface
    {
        $associationType = $this->productAssociationsPreloader->getAssociationByCode($associationTypeCode);
        if (null === $associationType) {
            throw ProductSynchronizationException::missingAssociation($associationTypeCode);
        }

        /** @var ProductAssociationInterface $productAssociation */
        $productAssociation = $this->productAssociationFactory->createNew();
        $productAssociation->setType($associationType);
        $productAssociation->setOwner($product);

        $this->entityManager->persist($productAssociation);

        return $productAssociation;
    }

    private function findFirstAssociationInProduct(ProductInterface $product, string $associationTypeCode): ?ProductAssociationInterface
    {
        return $product->getAssociations()->findFirst(
            function (int|string $key, ProductAssociationInterface $productAssociation) use (
                $associationTypeCode
            ): bool {
                return $associationTypeCode === $productAssociation->getType()?->getCode();
            },
        );
    }
}
