<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Context\SynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

readonly class ProductPropertyValueAssigner implements ProductPropertyValueAssignerInterface
{
    public function __construct(
        private iterable $propertyValueAssigners,
        private SynchronizationContextInterface $synchronizationContext,
    ) {
    }

    public function assign(
        ProductInterface $product,
        ProductPropertyMapping $propertyMapping,
        ?ResourceRelationMappingInterface $resourceRelationMapping,
        array $productValue,
    ): void {
        Assert::isInstanceOf($product, ResourceInterface::class);
        $existingProduct = null !== $product->getId();
        $skipForExistingProduct = null !== $resourceRelationMapping && $resourceRelationMapping->isImportOnce();
        $shouldSkipInPartialSync = $existingProduct && !$skipForExistingProduct;

        if ($shouldSkipInPartialSync && !$this->synchronizationContext->isFullSynchronization()) {
            return;
        }

        /** @var PropertyValueAssigner $propertyValueAssigner */
        foreach ($this->propertyValueAssigners as $propertyValueAssigner) {
            if (!$propertyValueAssigner->supports($product, $propertyMapping->value)) {
                continue;
            }
            $propertyValueAssigner->assign($product, $productValue, $resourceRelationMapping);

            return;
        }
    }
}
