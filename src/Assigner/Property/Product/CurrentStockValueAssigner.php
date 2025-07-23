<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\FloatValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class CurrentStockValueAssigner extends FloatValueAssigner
{
    public function assign(ResourceInterface $resource, array $values, ?ResourceRelationMappingInterface $mapping): void
    {
        $this->assignValue($resource, $values, function (ProductVariantInterface|ProductInterface $resource, float|int $value) {
            $variant = !$resource instanceof ProductInterface
                ? $resource
                : $resource->getVariants()->first()
            ;

            if (!$variant instanceof ProductVariantInterface) {
                throw ProductSynchronizationException::couldNotFindVariant($resource->getCode());
            }

            $variant->setOnHand((int) floor($value));
        });
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        $isVariant = $resource instanceof ProductVariantInterface;
        $productWithSingleVariant = $resource instanceof ProductInterface && 1 == $resource->getVariants()->count();

        if (!$isVariant && !$productWithSingleVariant) {
            return false;
        }

        return ProductPropertyMapping::CURRENT_STOCK === ProductPropertyMapping::tryFrom($property) ||
            ProductVariantPropertyMapping::CURRENT_STOCK === ProductVariantPropertyMapping::tryFrom($property)
        ;
    }
}
