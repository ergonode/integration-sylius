<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\PriceValueAssigner;
use BitBag\SyliusErgonodePlugin\Converter\MoneyConverter;
use BitBag\SyliusErgonodePlugin\Converter\MoneyConverterInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

class PriceValueAssignerTest extends TestCase
{
    private ProductVariantResolverInterface $productVariantResolver;
    private ChannelRepositoryInterface $channelRepository;
    private FactoryInterface $channelPricingFactory;
    private MoneyConverterInterface $moneyConverter;
    private LoggerInterface $logger;
    private PriceValueAssigner $assigner;

    private ProductResourceInterface $resource;

    protected function setUp(): void
    {
        $this->productVariantResolver = $this->createMock(ProductVariantResolverInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->channelPricingFactory = $this->createMock(FactoryInterface::class);
        $this->moneyConverter = $this->createMock(MoneyConverterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resource = $this->createMock(ProductResourceInterface::class);

        $this->assigner = new PriceValueAssigner(
            $this->productVariantResolver,
            $this->channelRepository,
            $this->channelPricingFactory,
            $this->moneyConverter,
            $this->logger
        );
    }

    public function testAssignWithNullMapping(): void
    {
        $values = ['1000'];

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('To assign price, you need to add mapping with channel code specified');

        $this->assigner->assign($this->resource, $values, null);
    }

    public function testAssignWithNoChannelCode(): void
    {
        $values = ['1000'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $mapping->method('getChannelCode')->willReturn(null);

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('To assign price, you need to add mapping with channel code specified');

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testAssignWithNonExistentChannel(): void
    {
        $values = ['1000'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);

        $mapping->method('getChannelCode')->willReturn('USD');

        $this->productVariantResolver->method('getVariant')->willReturn($variant);
        $this->channelRepository->method('findOneByCode')->with('USD')->willReturn(null);
        $variant->expects($this->never())->method('hasChannelPricingForChannel');

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testAssignProductPriceWithExistingChannelPricing(): void
    {
        $values = [1000];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $channel = $this->createMock(ChannelInterface::class);
        $channelPricing = $this->createMock(ChannelPricingInterface::class);

        $mapping->method('getChannelCode')->willReturn('USD');
        $mapping->method('getRelationType')->willReturn(RelationType::PRODUCT_PROPERTY);
        $mapping->method('getSyliusValue')->willReturn(ProductPropertyMapping::PRICE->value);

        $this->productVariantResolver->method('getVariant')->willReturn($variant);
        $this->channelRepository->method('findOneByCode')->with('USD')->willReturn($channel);
        $variant->method('hasChannelPricingForChannel')->with($channel)->willReturn(true);
        $variant->method('getChannelPricingForChannel')->with($channel)->willReturn($channelPricing);

        $this->moneyConverter->method('convert')->with($values[0])->willReturn(1000);

        $channelPricing->expects($this->once())->method('setPrice')->with(1000);

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testAssignProductMinimumPriceWithNewChannelPricing(): void
    {
        $values = [500];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $channel = $this->createMock(ChannelInterface::class);
        $channelPricing = $this->createMock(ChannelPricingInterface::class);

        $mapping->method('getChannelCode')->willReturn('USD');
        $mapping->method('getRelationType')->willReturn(RelationType::PRODUCT_PROPERTY);
        $mapping->method('getSyliusValue')->willReturn(ProductPropertyMapping::MINIMUM_PRICE->value);

        $this->productVariantResolver->method('getVariant')->willReturn($variant);
        $this->channelRepository->method('findOneByCode')->with('USD')->willReturn($channel);
        $variant->method('hasChannelPricingForChannel')->with($channel)->willReturn(false);
        $this->channelPricingFactory->method('createNew')->willReturn($channelPricing);

        $this->moneyConverter->method('convert')->with($values[0])->willReturn(500);

        $channelPricing->expects($this->once())->method('setChannelCode')->with('USD');
        $variant->expects($this->once())->method('addChannelPricing')->with($channelPricing);
        $channelPricing->expects($this->once())->method('setMinimumPrice')->with(500);

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testAssignVariantOriginalPrice(): void
    {
        $values = [1200];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $channel = $this->createMock(ChannelInterface::class);
        $channelPricing = $this->createMock(ChannelPricingInterface::class);

        $mapping->method('getChannelCode')->willReturn('USD');
        $mapping->method('getRelationType')->willReturn(RelationType::VARIANT_PROPERTY);
        $mapping->method('getSyliusValue')->willReturn(ProductVariantPropertyMapping::ORIGINAL_PRICE->value);

        $this->productVariantResolver->method('getVariant')->willReturn($variant);
        $this->channelRepository->method('findOneByCode')->with('USD')->willReturn($channel);
        $variant->method('hasChannelPricingForChannel')->with($channel)->willReturn(true);
        $variant->method('getChannelPricingForChannel')->with($channel)->willReturn($channelPricing);

        $this->moneyConverter->method('convert')->with($values[0])->willReturn(1200);

        $channelPricing->expects($this->once())->method('setOriginalPrice')->with(1200);

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testAssignWithInvalidRelationType(): void
    {
        $values = [1000];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $channel = $this->createMock(ChannelInterface::class);

        $mapping->method('getChannelCode')->willReturn('USD');
        $mapping->method('getRelationType')->willReturn(null);
        $mapping->method('getSyliusValue')->willReturn('some_value');

        $this->productVariantResolver->method('getVariant')->willReturn($variant);
        $this->channelRepository->method('findOneByCode')->with('USD')->willReturn($channel);
        $variant->method('hasChannelPricingForChannel')->with($channel)->willReturn(true);

        $this->expectException(SynchronizationException::class);

        $this->assigner->assign($this->resource, $values, $mapping);
    }

    public function testSupportsWithVariant(): void
    {
        $variant = $this->createMock(VariantResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductVariantPropertyMapping::PRICE->value
        ));

        $this->assertTrue($this->assigner->supports(
            $variant,
            ProductPropertyMapping::ORIGINAL_PRICE->value
        ));
    }

    public function testSupportsWithProductHavingSingleVariant(): void
    {
        $variants = new ArrayCollection([1]);

        $this->resource->method('getVariants')->willReturn($variants);

        $this->assertTrue($this->assigner->supports(
            $this->resource,
            ProductPropertyMapping::PRICE->value
        ));
    }

    public function testDoesNotSupportUnsupportedResource(): void
    {
        $this->assertFalse($this->assigner->supports(
            $this->resource,
            ProductPropertyMapping::PRICE->value
        ));
    }

    public function testDoesNotSupportProductWithMultipleVariants(): void
    {
        $variants = new ArrayCollection([1, 2]);

        $this->resource->method('getVariants')->willReturn($variants);

        $this->assertFalse($this->assigner->supports(
            $this->resource,
            ProductPropertyMapping::PRICE->value
        ));
    }
}
