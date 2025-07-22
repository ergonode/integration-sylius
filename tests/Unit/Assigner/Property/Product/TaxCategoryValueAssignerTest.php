<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\TaxCategoryValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Preloader\TaxCategoryPreloaderInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

final class TaxCategoryValueAssignerTest extends TestCase
{
    private TaxCategoryPreloaderInterface $taxCategoryPreloader;
    private ProductVariantResolverInterface $productVariantResolver;
    private LoggerInterface $logger;
    private TaxCategoryValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->taxCategoryPreloader = $this->createMock(TaxCategoryPreloaderInterface::class);
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->assigner = new TaxCategoryValueAssigner(
            $this->taxCategoryPreloader,
            $this->productVariantResolver,
            $this->logger
        );
    }

    public function testAssignTaxCategoryToVariant(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);
        $taxCategory = $this->createMock(TaxCategoryInterface::class);
        $values = [['standard']];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->taxCategoryPreloader->expects($this->once())
            ->method('getTaxCategoryByCode')
            ->with('standard')
            ->willReturn($taxCategory);

        $this->productVariantResolver->expects($this->once())
            ->method('getVariant')
            ->with($variant)
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('setTaxCategory')
            ->with($taxCategory);

        $this->logger->expects($this->never())
            ->method('warning');

        $this->assigner->assign($variant, $values, $mapping);
    }

    public function testAssignNonExistentTaxCategoryLogsWarning(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);
        $values = [['unknown']];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->taxCategoryPreloader->expects($this->once())
            ->method('getTaxCategoryByCode')
            ->with('unknown')
            ->willReturn(null);

        $this->productVariantResolver->expects($this->never())
            ->method('getVariant');

        $variant->expects($this->never())
            ->method('setTaxCategory');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Tax category with code "unknown" not found.');

        $this->assigner->assign($variant, $values, $mapping);
    }

    public function testSupportsProductVariant(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductVariantPropertyMapping::TAX_CATEGORY->value
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
            ProductPropertyMapping::TAX_CATEGORY->value
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
            ProductPropertyMapping::TAX_CATEGORY->value
        ));
    }

    public function testDoesNotSupportOtherResources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $resource,
            ProductPropertyMapping::TAX_CATEGORY->value
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
