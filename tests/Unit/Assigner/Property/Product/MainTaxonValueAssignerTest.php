<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\MainTaxonValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Preloader\TaxonPreloaderInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;

class MainTaxonValueAssignerTest extends TestCase
{
    private TaxonPreloaderInterface $taxonPreloader;
    private MainTaxonValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->taxonPreloader = $this->createMock(TaxonPreloaderInterface::class);

        $this->assigner = new MainTaxonValueAssigner($this->taxonPreloader);
    }

    public function testAssignMainTaxon(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $taxon = $this->createMock(TaxonInterface::class);
        $values = [['main-category']];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->taxonPreloader->expects($this->once())
            ->method('getTaxonByCode')
            ->with('main-category')
            ->willReturn($taxon);

        $product->expects($this->once())
            ->method('setMainTaxon')
            ->with($taxon);

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testAssignNullMainTaxon(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $values = [['non-existent-category']];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->taxonPreloader->expects($this->once())
            ->method('getTaxonByCode')
            ->with('non-existent-category')
            ->willReturn(null);

        $product->expects($this->once())
            ->method('setMainTaxon')
            ->with(null);

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsProductAndMainTaxonProperty(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $product,
            ProductPropertyMapping::MAIN_TAXON->value
        ));
    }

    public function testDoesNotSupportNonProduct(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $resource,
            ProductPropertyMapping::MAIN_TAXON->value
        ));
    }

    public function testDoesNotSupportProductWithOtherProperty(): void
    {
        $product = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $product,
            'other_property'
        ));
    }
}
