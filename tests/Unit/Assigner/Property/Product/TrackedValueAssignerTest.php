<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\TrackedValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Model\ResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

final class TrackedValueAssignerTest extends TestCase
{
    private ProductVariantResolverInterface $productVariantResolver;

    private TrackedValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);

        $this->assigner = new TrackedValueAssigner(
            $this->productVariantResolver,
        );
    }

    public function testAssignTrackedToProductVariant(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);
        $values = [true];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->productVariantResolver->expects($this->once())
            ->method('getVariant')
            ->with($variant)
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('setTracked')
            ->with(true);

        $this->assigner->assign($variant, $values, $mapping);
    }

    public function testAssignTrackedToProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $variant = $this->createMock(VariantResourceInterface::class);
        $values = [false];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->productVariantResolver->expects($this->once())
            ->method('getVariant')
            ->with($product)
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('setTracked')
            ->with(false);

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsProductVariant(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductVariantPropertyMapping::TRACKED->value,
        ));
    }

    public function testSupportsProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $variants = $this->createMock(Collection::class);

        $variants->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn($variants);

        $this->assertTrue($this->assigner->supports(
            $product,
            ProductPropertyMapping::TRACKED->value,
        ));
    }

    public function testDoesNotSupportProductWithMultipleVariants(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $variants = $this->createMock(Collection::class);

        $variants->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn($variants);

        $this->assertFalse($this->assigner->supports(
            $product,
            ProductPropertyMapping::TRACKED->value,
        ));
    }

    public function testDoesNotSupportOtherResources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $resource,
            ProductPropertyMapping::TRACKED->value,
        ));
    }

    public function testDoesNotSupportOtherProperties(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $variant,
            'invalid_property',
        ));
    }
}
