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
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Webmozart\Assert\Assert;

final class ProductVariantProvider implements ProductVariantProviderInterface
{
    public function __construct(
        private ProductVariantRepositoryInterface $productVariantRepository,
        private ProductVariantFactoryInterface $productFactory,
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
    ) {
    }

    public function provide(IntegrationInterface $integration, ProductInterface $product, string $productVariantCode): ProductVariantInterface
    {
        $mapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $productVariantCode,
            RelationType::VARIANT_EXTERNAL_ID,
        );

        if (null !== $mapping) {
            $productVariant = $this->productVariantRepository->findOneBy(['code' => $mapping->getSyliusValue()]);

            if ($productVariant instanceof ProductVariantInterface) {
                return $productVariant;
            }
        }
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $productVariantCode]);
        if ($productVariant instanceof ProductVariantInterface) {
            return $productVariant;
        }

        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->productFactory->createForProduct($product);
        $productVariant->setCode($productVariantCode);

        return $productVariant;
    }
}
