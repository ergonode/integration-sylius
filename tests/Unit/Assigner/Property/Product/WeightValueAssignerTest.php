<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\WeightValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Resolver\FloatValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class WeightValueAssignerTest extends TestCase
{
    private ProductVariantResolverInterface $productVariantResolver;
    private FloatValueResolverInterface $floatValueResolver;
    private WeightValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);
        $this->floatValueResolver = $this->createMock(FloatValueResolverInterface::class);

        $this->assigner = new WeightValueAssigner(
            $this->productVariantResolver,
            $this->floatValueResolver
        );
    }

    public function testAssignWeightToProductVariant(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);
        $values = [10.5];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->productVariantResolver->expects($this->once())
            ->method('getVariant')
            ->with($variant)
            ->willReturn($variant);

        $this->floatValueResolver->expects($this->once())
            ->method('resolve')
            ->with($values[0])
            ->willReturn(10.5);

        $variant->expects($this->once())
            ->method('setWeight')
            ->with(10.5);

        $this->assigner->assign($variant, $values, $mapping);
    }

    public function testAssignWeightToProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $values = [15.75];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->productVariantResolver->expects($this->once())
            ->method('getVariant')
            ->with($product)
            ->willReturn($variant);

        $this->floatValueResolver->expects($this->once())
            ->method('resolve')
            ->with($values[0])
            ->willReturn(15.75);

        $variant->expects($this->once())
            ->method('setWeight')
            ->with(15.75);

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsProductVariant(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductVariantPropertyMapping::WEIGHT->value
        ));
    }

    public function testSupportsProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $variants = $this->createMock(\Doctrine\Common\Collections\Collection::class);

        $variants->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn($variants);

        $this->assertTrue($this->assigner->supports(
            $product,
            ProductPropertyMapping::WEIGHT->value
        ));
    }

    public function testDoesNotSupportProductWithMultipleVariants(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $variants = $this->createMock(\Doctrine\Common\Collections\Collection::class);

        $variants->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn($variants);

        $this->assertFalse($this->assigner->supports(
            $product,
            ProductPropertyMapping::WEIGHT->value
        ));
    }

    public function testDoesNotSupportOtherResources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $resource,
            ProductPropertyMapping::WEIGHT->value
        ));
    }

    public function testDoesNotSupportOtherProperties(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);

        $this->assertFalse($this->assigner->supports(
            $variant,
            'invalid_property'
        ));
    }
}
