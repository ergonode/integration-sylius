<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\ShippingRequiredValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class ShippingRequiredValueAssignerTest extends TestCase
{
    private ShippingRequiredValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->assigner = new ShippingRequiredValueAssigner();
    }

    public function testAssignShippingRequiredToVariant(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);
        $values = [true];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $variant->expects($this->once())
            ->method('setShippingRequired')
            ->with(true);

        $this->assigner->assign($variant, $values, $mapping);
    }

    public function testAssignShippingRequiredToProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $values = [true];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn(new ArrayCollection([$variant]));

        $variant->expects($this->once())
            ->method('setShippingRequired')
            ->with(true);

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testThrowExceptionWhenNoVariantFound(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $values = [true];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $product->method('getCode')->willReturn('test-product');
        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn(new ArrayCollection([]));

        $this->expectException(ProductSynchronizationException::class);

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsProductVariant(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductPropertyMapping::SHIPPING_REQUIRED->value,
        ));

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductVariantPropertyMapping::SHIPPING_CATEGORY->value,
        ));
    }

    public function testSupportsProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('getVariants')->willReturn(new ArrayCollection([1]));

        $this->assertTrue($this->assigner->supports(
            $product,
            ProductPropertyMapping::SHIPPING_REQUIRED->value,
        ));
    }

    public function testDoesNotSupportProductWithMultipleVariants(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('getVariants')->willReturn(new ArrayCollection([1, 2]));

        $this->assertFalse($this->assigner->supports(
            $product,
            ProductPropertyMapping::SHIPPING_REQUIRED->value,
        ));
    }

    public function testDoesNotSupportUnsupportedResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $resource,
            ProductPropertyMapping::SHIPPING_REQUIRED->value,
        ));
    }
}
