<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\ProductVariant;

use BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Context\SynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

readonly class ProductVariantPropertyValueAssigner implements ProductVariantPropertyValueAssignerInterface
{
    public function __construct(
        private iterable $propertyValueAssigners,
        private SynchronizationContextInterface $synchronizationContext,
    ) {
    }

    public function assign(
        ProductVariantInterface $productVariant,
        ProductVariantPropertyMapping $propertyMapping,
        ResourceRelationMappingInterface $resourceRelationMapping,
        array $productValue,
    ): void {
        Assert::isInstanceOf($productVariant, ResourceInterface::class);
        $existingProduct = null !== $productVariant->getId();
        $skipForExistingProduct = $resourceRelationMapping->isImportOnce();
        $shouldSkipInPartialSync = $existingProduct && !$skipForExistingProduct;

        if ($shouldSkipInPartialSync && !$this->synchronizationContext->isFullSynchronization()) {
            return;
        }
        /** @var PropertyValueAssigner $propertyValueAssigner */
        foreach ($this->propertyValueAssigners as $propertyValueAssigner) {
            if (!$propertyValueAssigner->supports($productVariant, $propertyMapping->value)) {
                continue;
            }
            $propertyValueAssigner->assign($productVariant, $productValue, $resourceRelationMapping);

            return;
        }
    }
}
