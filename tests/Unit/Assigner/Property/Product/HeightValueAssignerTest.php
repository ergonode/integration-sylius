<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\HeightValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
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

final class HeightValueAssignerTest extends TestCase
{
    private FloatValueResolverInterface $floatValueResolver;

    private ProductVariantResolverInterface $productVariantResolver;

    private HeightValueAssigner $heightValueAssigner;

    protected function setUp(): void
    {
        $this->floatValueResolver = $this->createMock(FloatValueResolverInterface::class);
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);
        $this->heightValueAssigner = new HeightValueAssigner(
            $this->productVariantResolver,
            $this->floatValueResolver,
        );
    }

    public function testItAssignsHeightToProductVariant(): void
    {
        $resource = $this->createMock(VariantResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $heightValue = 30.5;
        $values = ['en_US' => 30.5];

        $this->floatValueResolver
            ->expects(self::once())
            ->method('resolve')
            ->with(30.5)
            ->willReturn($heightValue);

        $this->productVariantResolver
            ->expects(self::once())
            ->method('getVariant')
            ->with($resource)
            ->willReturn($resource);

        $resource
            ->expects(self::once())
            ->method('setHeight')
            ->with($heightValue);

        $this->heightValueAssigner->assign($resource, $values, $mapping);
    }

    public function testItAssignsHeightToProductWithSingleVariant(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $heightValue = 25.3;
        $values = ['en_US' => '25.3'];

        $this->floatValueResolver
            ->expects(self::once())
            ->method('resolve')
            ->with('25.3')
            ->willReturn($heightValue);

        $this->productVariantResolver
            ->expects(self::once())
            ->method('getVariant')
            ->with($product)
            ->willReturn($variant);

        $variant
            ->expects(self::once())
            ->method('setHeight')
            ->with($heightValue);

        $this->heightValueAssigner->assign($product, $values, $mapping);
    }

    public function testItDoesNothingWhenValueIsNull(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $values = ['value'];

        $this->floatValueResolver
            ->expects(self::once())
            ->method('resolve')
            ->with('value')
            ->willReturn(null);

        $this->productVariantResolver
            ->expects(self::never())
            ->method('getVariant');

        $this->heightValueAssigner->assign($resource, $values, $mapping);
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testItSupportsCorrectResourceAndProperty(
        string $property,
        bool $expected,
    ): void {
        $resource = $this->createMock(ProductInterface::class);
        $resource
            ->method('getVariants')
            ->willReturn(new ArrayCollection([
                $this->createMock(ProductVariantInterface::class),
            ]));
        $result = $this->heightValueAssigner->supports($resource, $property);

        self::assertSame($expected, $result);
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testItSupportsCorrectVariantAndProperty(
        string $property,
        bool $expected,
    ): void {
        $resource = $this->createMock(ProductVariantInterface::class);
        $result = $this->heightValueAssigner->supports($resource, $property);

        self::assertSame($expected, $result);
    }

    public static function supportDataProvider(): array
    {
        return [
            'resource with height property' => [
                ProductVariantPropertyMapping::HEIGHT->value,
                true,
            ],
            'resource with non-height property' => [
                ProductVariantPropertyMapping::WIDTH->value,
                false,
            ],
        ];
    }
}
