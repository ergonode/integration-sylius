<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\ShippingCategoryValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Preloader\ShippingCategoryPreloaderInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

class ShippingCategoryValueAssignerTest extends TestCase
{
    private ShippingCategoryPreloaderInterface $shippingCategoryPreloader;

    private ProductVariantResolverInterface $productVariantResolver;

    private LoggerInterface $logger;

    private ShippingCategoryValueAssigner $assigner;

    private ProductResourceInterface $resource;

    protected function setUp(): void
    {
        $this->shippingCategoryPreloader = $this->createMock(ShippingCategoryPreloaderInterface::class);
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resource = $this->createMock(ProductResourceInterface::class);

        $this->assigner = new ShippingCategoryValueAssigner(
            $this->shippingCategoryPreloader,
            $this->productVariantResolver,
            $this->logger,
        );
    }

    public function testAssignShippingCategoryToVariant(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);
        $shippingCategory = $this->createMock(ShippingCategoryInterface::class);
        $values = [['standard-shipping']];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->shippingCategoryPreloader->expects($this->once())
            ->method('getShippingCategoryByCode')
            ->with('standard-shipping')
            ->willReturn($shippingCategory);

        $this->productVariantResolver->expects($this->once())
            ->method('getVariant')
            ->with($this->resource)
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('setShippingCategory')
            ->with($shippingCategory);

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testLogWarningWhenShippingCategoryNotFound(): void
    {
        $values = ['en_US' => ['non-existent-shipping']];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->shippingCategoryPreloader->expects($this->once())
            ->method('getShippingCategoryByCode')
            ->with('non-existent-shipping')
            ->willReturn(null);

        $this->logger->expects($this->once())
            ->method('warning')
        ;

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testSupportsProductVariant(): void
    {
        $this->assertTrue($this->assigner->supports(
            $this->createMock(VariantResourceInterface::class),
            ProductPropertyMapping::SHIPPING_CATEGORY->value,
        ));
    }

    public function testSupportsProductWithSingleVariant(): void
    {
        $variants = new ArrayCollection([1]);
        $this->resource->method('getVariants')->willReturn($variants);

        $this->assertTrue($this->assigner->supports(
            $this->resource,
            ProductPropertyMapping::SHIPPING_CATEGORY->value,
        ));
    }

    public function testDoesNotSupportProductWithMultipleVariants(): void
    {
        $variants = new ArrayCollection([1, 2]);

        $this->resource->method('getVariants')->willReturn($variants);
        $this->resource->method('getVariants')->willReturn($variants);

        $this->assertFalse($this->assigner->supports(
            $this->resource,
            ProductPropertyMapping::SHIPPING_CATEGORY->value,
        ));
    }

    public function testDoesNotSupportUnsupportedResource(): void
    {
        $this->assertFalse($this->assigner->supports(
            $this->createMock(ResourceInterface::class),
            ProductPropertyMapping::SHIPPING_CATEGORY->value,
        ));
    }
}
