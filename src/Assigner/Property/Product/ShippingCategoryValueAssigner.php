<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ShippingCategoryRepositoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;

final class ShippingCategoryValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly ShippingCategoryRepositoryInterface $shippingCategoryRepository,
        private readonly ProductVariantResolverInterface $productVariantResolver,
    ) {
    }

    public function assign(ResourceInterface $resource, array $values, ResourceRelationMappingInterface $mapping): void
    {
        $value = array_shift($values);
        /** @var ?ShippingCategoryInterface $shippingCategory */
        $shippingCategory = $this->shippingCategoryRepository->findOneBy(['code' => $value]);
        $variant = $this->productVariantResolver->getVariant($resource);
        $variant->setShippingCategory($shippingCategory);
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        $isVariant = $resource instanceof ProductVariantInterface;
        $productWithSingleVariant = $resource instanceof ProductInterface && 1 === $resource->getVariants()->count();

        if (!$isVariant && !$productWithSingleVariant) {
            return false;
        }

        return ProductPropertyMapping::SHIPPING_CATEGORY === ProductPropertyMapping::tryFrom($property) ||
            ProductVariantPropertyMapping::SHIPPING_CATEGORY === ProductVariantPropertyMapping::tryFrom($property);
    }
}
