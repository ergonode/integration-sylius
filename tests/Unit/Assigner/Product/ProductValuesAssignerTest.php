<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductAttributeValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductPropertyValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductValuesAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributeValueType;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ProductAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use BitBag\SyliusErgonodePlugin\Storage\ProductsRelationsStorageInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class ProductValuesAssignerTest extends TestCase
{
    private ProductValuesAssignerInterface $productValuesAssigner;

    private ResourceRelationMappingResolverInterface $resolver;

    private ProductPropertyValueAssignerInterface $propertyAssigner;

    private MainLanguageContextInterface $mainLangCtx;

    private ProductAttributeValueResolverInterface $valueResolver;

    private ProductAttributeValueAssignerInterface $attributeAssigner;

    private ProductsRelationsStorageInterface $relationStorage;

    private ProductApiClientInterface $apiClient;

    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $this->propertyAssigner = $this->createMock(ProductPropertyValueAssignerInterface::class);
        $this->mainLangCtx = $this->createMock(MainLanguageContextInterface::class);
        $this->valueResolver = $this->createMock(ProductAttributeValueResolverInterface::class);
        $this->attributeAssigner = $this->createMock(ProductAttributeValueAssignerInterface::class);
        $this->relationStorage = $this->createMock(ProductsRelationsStorageInterface::class);
        $this->apiClient = $this->createMock(ProductApiClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->productValuesAssigner = new ProductValuesAssigner(
            $this->resolver,
            $this->propertyAssigner,
            $this->mainLangCtx,
            $this->valueResolver,
            $this->attributeAssigner,
            $this->relationStorage,
            $this->apiClient,
            $this->logger,
        );
    }

    public function testAssignDelegatesToPropertyAssignerWhenPropertyIsKnown(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $propertyMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $propertyMapping->method('getSyliusValue')->willReturn('name');
        $this->mainLangCtx->method('getMainLanguageMapping')->willReturn($propertyMapping);

        $this->resolver->method('resolveForErgonodeValue')->willReturn($propertyMapping);
        $this->valueResolver->method('resolve')->willReturn(['en_US' => 'name']);

        $this->propertyAssigner->expects($this->once())
            ->method('assign')
            ->with($product, ProductPropertyMapping::NAME, $propertyMapping, ['en_US' => 'name']);

        $this->productValuesAssigner->assign([
            'sku' => 'cowboy_hat',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => 'TextValue',
                        'attribute' => ['code' => 'name'],
                        'value' => ['en_US' => 'Cowboy hat'],
                    ],
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ],
        ], $integration, $product);
    }

    public function testAssignDelegatesToAttributeAssignerWhenNoPropertyMapping(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $mainLangMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $this->mainLangCtx->method('getMainLanguageMapping')->willReturn($mainLangMapping);
        $this->resolver->method('resolveForErgonodeValue')->willReturn(null);
        $this->valueResolver->method('resolve')->willReturn(['en_US' => 'Sunglasses']);

        $this->attributeAssigner->expects($this->once())
            ->method('assign')
            ->with($product, ['en_US' => 'Sunglasses'], 'name');

        $this->productValuesAssigner->assign([
            'sku' => 'sunglasses',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => 'TextValue',
                        'attribute' => ['code' => 'name'],
                        'value' => ['Sunglasses'],
                    ],
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ],
        ], $integration, $product);
    }

    public function testAssignStoresProductRelationValues(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $mainLangMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $this->mainLangCtx->method('getMainLanguageMapping')->willReturn($mainLangMapping);
        $this->resolver->method('resolveForErgonodeValue')->willReturn(null);
        $this->valueResolver->method('resolve')->willReturn(['en_US' => 'product-slug']);

        $this->relationStorage->expects($this->once())
            ->method('storeAssociation')
            ->with($integration, $product, 'related', ['en_US' => 'product-slug']);

        $this->productValuesAssigner->assign([
            'sku' => 't-shirt-white',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => AttributeValueType::PRODUCT_RELATION->value,
                        'attribute' => ['code' => 'related'],
                        'value' => ['product-slug'],
                    ],
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ],
        ], $integration, $product);
    }

    public function testAssignThrowsOnMissingNodeKey(): void
    {
        $this->expectException(SynchronizationException::class);

        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $this->productValuesAssigner->assign([
            'sku' => 'white_cap',
            'attributeList' => [
                'edges' => [['invalid_key' => []]],
                'pageInfo' => ['hasNextPage' => false],
            ],
        ], $integration, $product);
    }
}
