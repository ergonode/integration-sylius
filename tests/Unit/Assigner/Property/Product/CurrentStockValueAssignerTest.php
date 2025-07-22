<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\CurrentStockValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\FloatValueResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

final class CurrentStockValueAssignerTest extends TestCase
{
    private FloatValueResolverInterface $floatValueResolver;
    private CurrentStockValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->floatValueResolver = $this->createMock(FloatValueResolverInterface::class);

        $this->assigner = new CurrentStockValueAssigner(
            $this->floatValueResolver
        );
    }

    public function testAssignOnHandToProductVariant(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);
        $values = [25.5];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->floatValueResolver->expects($this->once())
            ->method('resolve')
            ->with($values[0])
            ->willReturn(25.5);

        $variant->expects($this->once())
            ->method('setOnHand')
            ->with(25);

        $this->assigner->assign($variant, $values, $mapping);
    }

    public function testAssignOnHandToProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $values = [30.75];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $variants = new ArrayCollection([$variant]);

        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn($variants);

        $this->floatValueResolver->expects($this->once())
            ->method('resolve')
            ->with($values[0])
            ->willReturn(30.75);

        $variant->expects($this->once())
            ->method('setOnHand')
            ->with(30);

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testThrowsExceptionWhenVariantNotFound(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $values = [40.5];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn(new ArrayCollection());

        $product->expects($this->once())
            ->method('getCode')
            ->willReturn('test-product');

        $this->floatValueResolver->expects($this->once())
            ->method('resolve')
            ->with($values[0])
            ->willReturn(40.5);

        $this->expectException(ProductSynchronizationException::class);
        $this->expectExceptionMessage('Could not resolve variant for product with code `test-product`.');

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsProductVariant(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductVariantPropertyMapping::CURRENT_STOCK->value
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
            ProductPropertyMapping::CURRENT_STOCK->value
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
            ProductPropertyMapping::CURRENT_STOCK->value
        ));
    }

    public function testDoesNotSupportOtherResources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $resource,
            ProductPropertyMapping::CURRENT_STOCK->value
        ));
    }

    public function testDoesNotSupportOtherProperties(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $variant,
            'invalid_property'
        ));
    }
}
