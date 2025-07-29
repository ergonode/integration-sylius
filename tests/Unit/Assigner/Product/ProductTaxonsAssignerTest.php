<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductTaxonsAssigner;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Preloader\TaxonPreloaderInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ProductTaxonsAssignerTest extends TestCase
{
    public function testAssignSkipsIfTaxonIsMissing(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $preloader = $this->createMock(TaxonPreloaderInterface::class);
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $factory = $this->createMock(FactoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $preloader->method('getTaxonByCode')->with('ergonode-cat')->willReturn(null);

        $logger->expects($this->once())->method('info')->with('Taxon with code "ergonode-cat" not found.');

        $assigner = new ProductTaxonsAssigner($preloader, $resolver, $factory, $logger);

        $assigner->assign([
            'categoryList' => [
                'edges' => [
                    ['node' => ['code' => 'ergonode-cat']],
                ],
            ],
        ], $integration, $product);
    }

    public function testAssignSkipsIfProductAlreadyHasTaxon(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);
        $taxon = $this->createMock(TaxonInterface::class);

        $preloader = $this->createMock(TaxonPreloaderInterface::class);
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $factory = $this->createMock(FactoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $preloader->method('getTaxonByCode')->willReturn($taxon);
        $product->method('hasTaxon')->with($taxon)->willReturn(true);

        $factory->expects($this->never())->method('createNew');
        $product->expects($this->never())->method('addProductTaxon');

        $assigner = new ProductTaxonsAssigner($preloader, $resolver, $factory, $logger);
        $assigner->assign([
            'categoryList' => [
                'edges' => [
                    ['node' => ['code' => 'cat-code']],
                ],
            ],
        ], $integration, $product);
    }

    public function testAssignAddsNewProductTaxonIfMissing(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);
        $taxon = $this->createMock(TaxonInterface::class);
        $productTaxon = $this->createMock(ProductTaxonInterface::class);

        $preloader = $this->createMock(TaxonPreloaderInterface::class);
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $factory = $this->createMock(FactoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $preloader->method('getTaxonByCode')->willReturn($taxon);
        $product->method('hasTaxon')->with($taxon)->willReturn(false);

        $factory->expects($this->once())->method('createNew')->willReturn($productTaxon);
        $productTaxon->expects($this->once())->method('setTaxon')->with($taxon);
        $product->expects($this->once())->method('addProductTaxon')->with($productTaxon);

        $assigner = new ProductTaxonsAssigner($preloader, $resolver, $factory, $logger);
        $assigner->assign([
            'categoryList' => [
                'edges' => [
                    ['node' => ['code' => 'cat-code']],
                ],
            ],
        ], $integration, $product);
    }
}
