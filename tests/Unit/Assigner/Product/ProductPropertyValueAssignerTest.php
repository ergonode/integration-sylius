<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductPropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Context\SynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;

final class ProductPropertyValueAssignerTest extends TestCase
{
    public function test_assign_skips_if_partial_and_import_once_is_false(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('getId')->willReturn(42);

        $context = $this->createMock(SynchronizationContextInterface::class);
        $context->method('isFullSynchronization')->willReturn(false);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('isImportOnce')->willReturn(false);

        $assigner = new ProductPropertyValueAssigner([], $context);
        $assigner->assign($product, ProductPropertyMapping::CHANNELS, $mapping, []);
        $this->assertTrue(true);
    }

    public function test_assign_calls_supports_and_assign_on_first_matching_assigner(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('getId')->willReturn(null);

        $context = $this->createMock(SynchronizationContextInterface::class);
        $context->method('isFullSynchronization')->willReturn(false);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $matchingAssigner = $this->createMock(PropertyValueAssigner::class);
        $matchingAssigner->expects($this->once())
            ->method('supports')
            ->with($product, 'shipping_category')
            ->willReturn(true);
        $matchingAssigner->expects($this->once())
            ->method('assign')
            ->with($product, ['value'], $mapping);

        $nonMatchingAssigner = $this->createMock(PropertyValueAssigner::class);
        $nonMatchingAssigner->expects($this->once())
            ->method('supports')
            ->willReturn(false);
        $nonMatchingAssigner->expects($this->never())->method('assign');

        $assigner = new ProductPropertyValueAssigner(
            [$nonMatchingAssigner, $matchingAssigner],
            $context
        );

        $assigner->assign($product, ProductPropertyMapping::SHIPPING_CATEGORY, $mapping, ['value']);
    }

    public function test_assign_always_runs_on_new_product_even_if_not_full_sync(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('getId')->willReturn(null);

        $context = $this->createMock(SynchronizationContextInterface::class);
        $context->expects($this->never())->method('isFullSynchronization');

        $assignerMock = $this->createMock(PropertyValueAssigner::class);
        $assignerMock->method('supports')->willReturn(true);
        $assignerMock->expects($this->once())->method('assign');

        $assigner = new ProductPropertyValueAssigner([$assignerMock], $context);
        $assigner->assign($product, ProductPropertyMapping::ORIGINAL_PRICE, null, [12]);
    }
}
