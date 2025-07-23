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
use BitBag\SyliusErgonodePlugin\Preloader\TaxCategoryPreloaderInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

final class TaxCategoryValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly TaxCategoryPreloaderInterface $taxCategoryPreloader,
        private readonly ProductVariantResolverInterface $productVariantResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function assign(ResourceInterface $resource, array $values, ?ResourceRelationMappingInterface $mapping): void
    {
        $value = array_shift($values);
        $category = $this->taxCategoryPreloader->getTaxCategoryByCode($value[0]);

        if ($category instanceof TaxCategoryInterface || null === $value) {
            $variant = $this->productVariantResolver->getVariant($resource);
            $variant->setTaxCategory($category);

            return;
        }

        $this->logger->warning(sprintf('Tax category with code "%s" not found.', $value));
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        $isVariant = $resource instanceof ProductVariantInterface;
        $productWithSingleVariant = $resource instanceof ProductInterface && 1 == $resource->getVariants()->count();

        if (!$isVariant && !$productWithSingleVariant) {
            return false;
        }

        return ProductPropertyMapping::TAX_CATEGORY === ProductPropertyMapping::tryFrom($property) ||
            ProductVariantPropertyMapping::TAX_CATEGORY === ProductVariantPropertyMapping::tryFrom($property)
        ;
    }
}
