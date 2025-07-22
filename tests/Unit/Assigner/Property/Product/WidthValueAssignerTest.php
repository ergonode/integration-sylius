<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\WidthValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Resolver\FloatValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

final class WidthValueAssignerTest extends TestCase
{
    private FloatValueResolverInterface $floatValueResolver;
    private ProductVariantResolverInterface $productVariantResolver;
    private WidthValueAssigner $widthValueAssigner;

    protected function setUp(): void
    {
        $this->floatValueResolver = $this->createMock(FloatValueResolverInterface::class);
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);
        $this->widthValueAssigner = new WidthValueAssigner(
            $this->productVariantResolver,
            $this->floatValueResolver
        );
    }

    public function testItAssignsWidthToProductVariant(): void
    {
        $resource = $this->createMock(VariantResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $widthValue = 15.5;
        $values = ['en_US' => '15.5'];

        $this->floatValueResolver
            ->expects(self::once())
            ->method('resolve')
            ->with('15.5')
            ->willReturn($widthValue);

        $this->productVariantResolver
            ->expects(self::once())
            ->method('getVariant')
            ->with($resource)
            ->willReturn($resource);

        $resource
            ->expects(self::once())
            ->method('setWidth')
            ->with($widthValue);

        $this->widthValueAssigner->assign($resource, $values, $mapping);
    }

    public function testItAssignsToProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $widthValue = 20.7;
        $values = ['en_US' => 20.7];

        $this->floatValueResolver
            ->expects(self::once())
            ->method('resolve')
            ->with(20.7)
            ->willReturn($widthValue);

        $this->productVariantResolver
            ->expects(self::once())
            ->method('getVariant')
            ->with($product)
            ->willReturn($variant);

        $variant
            ->expects(self::once())
            ->method('setWidth')
            ->with($widthValue);

        $this->widthValueAssigner->assign($product, $values, $mapping);
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testItSupportsCorrectResourceAndProperty(
        string $property,
        bool $expected
    ): void {
        $resource = $this->createMock(ProductInterface::class);
        $resource
            ->method('getVariants')
            ->willReturn(new ArrayCollection([
                $this->createMock(ProductVariantInterface::class),
            ]));
        $result = $this->widthValueAssigner->supports($resource, $property);

        self::assertSame($expected, $result);
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testItSupportsCorrectVariantAndProperty(
        string $property,
        bool $expected
    ): void {
        $resource = $this->createMock(VariantResourceInterface::class);
        $result = $this->widthValueAssigner->supports($resource, $property);

        self::assertSame($expected, $result);
    }

    public static function supportDataProvider(): array
    {
        return [
            'resource with width property' => [
                ProductVariantPropertyMapping::WIDTH->value,
                true,
            ],
            'resource with non-width property' => [
                ProductVariantPropertyMapping::HEIGHT->value,
                false,
            ],
        ];
    }
}
