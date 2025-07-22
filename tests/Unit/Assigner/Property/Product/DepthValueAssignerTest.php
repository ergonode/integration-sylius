<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\DepthValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Resolver\FloatValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

final class DepthValueAssignerTest extends TestCase
{
    private FloatValueResolverInterface $floatValueResolver;
    private ProductVariantResolverInterface $productVariantResolver;
    private DepthValueAssigner $depthValueAssigner;

    protected function setUp(): void
    {
        $this->floatValueResolver = $this->createMock(FloatValueResolverInterface::class);
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);
        $this->depthValueAssigner = new DepthValueAssigner(
            $this->productVariantResolver,
            $this->floatValueResolver
        );
    }

    public function testItAssignsDepthToProductVariant(): void
    {
        $resource = $this->createMock(VariantResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $depthValue = 10.2;
        $values = ['en_US' => '10.2'];

        $this->floatValueResolver
            ->expects(self::once())
            ->method('resolve')
            ->with('10.2')
            ->willReturn($depthValue);

        $this->productVariantResolver
            ->expects(self::once())
            ->method('getVariant')
            ->with($resource)
            ->willReturn($resource);

        $resource
            ->expects(self::once())
            ->method('setDepth')
            ->with($depthValue);

        $this->depthValueAssigner->assign($resource, $values, $mapping);
    }

    public function test_it_assigns_depth_to_product_with_single_variant(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $depthValue = 18.7;
        $values = ['en_US' => 18.7];

        $this->floatValueResolver
            ->expects(self::once())
            ->method('resolve')
            ->with('18.7')
            ->willReturn($depthValue);

        $this->productVariantResolver
            ->expects(self::once())
            ->method('getVariant')
            ->with($product)
            ->willReturn($variant);

        $variant
            ->expects(self::once())
            ->method('setDepth')
            ->with($depthValue);

        $this->depthValueAssigner->assign($product, $values, $mapping);
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testItSupportsCorrectVariantAndProperty(
        string $property,
        bool $expected
    ): void {
        $resource = $this->createMock(VariantResourceInterface::class);
        $result = $this->depthValueAssigner->supports($resource, $property);

        self::assertSame($expected, $result);
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testItSupportsCorrectProductAndProperty(
        string $property,
        bool $expected
    ): void {
        $resource = $this->createMock(ProductResourceInterface::class);
        $resource
            ->method('getVariants')
            ->willReturn(new ArrayCollection([
                $this->createMock(ProductVariantInterface::class),
            ])
        );

        $result = $this->depthValueAssigner->supports($resource, $property);

        self::assertSame($expected, $result);
    }

    public static function supportDataProvider(): array
    {
        return [
            'resource with depth property' => [
                ProductVariantPropertyMapping::DEPTH->value,
                true,
            ],
            'resource with non-depth property' => [
                ProductVariantPropertyMapping::WIDTH->value,
                false,
            ],
        ];
    }
}
