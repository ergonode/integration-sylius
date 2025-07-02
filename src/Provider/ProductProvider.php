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
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;

final class ProductProvider implements ProductProviderInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductFactoryInterface $productFactory,
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
    ) {
    }

    public function provide(IntegrationInterface $integration, string $ergonodeProductCode): ProductInterface
    {
        $mapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $ergonodeProductCode,
            RelationType::PRODUCT_EXTERNAL_ID,
        );

        if (null !== $mapping) {
            $product = $this->productRepository->findOneBy(['code' => $mapping->getSyliusValue()]);

            if ($product instanceof ProductInterface) {
                return $product;
            }
        }
        $product = $this->productRepository->findOneBy(['code' => $ergonodeProductCode]);
        if ($product instanceof ProductInterface) {
            return $product;
        }

        /** @var ProductInterface $product */
        $product = $this->productFactory->createWithVariant();
        $product->setCode($ergonodeProductCode);
        $product->getVariants()->first()->setCode($ergonodeProductCode);

        return $product;
    }
}
