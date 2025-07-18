<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductAttributeValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductPropertyValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductValuesAssigner;
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
use Sylius\Component\Core\Model\ProductInterface;

final class ProductValuesAssignerTest extends TestCase
{
    public function test_assign_delegates_to_property_assigner_when_property_is_known(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $propertyAssigner = $this->createMock(ProductPropertyValueAssignerInterface::class);
        $mainLangCtx = $this->createMock(MainLanguageContextInterface::class);
        $valueResolver = $this->createMock(ProductAttributeValueResolverInterface::class);
        $attributeAssigner = $this->createMock(ProductAttributeValueAssignerInterface::class);
        $relationStorage = $this->createMock(ProductsRelationsStorageInterface::class);
        $apiClient = $this->createMock(ProductApiClientInterface::class);

        $propertyMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $propertyMapping->method('getSyliusValue')->willReturn('name');
        $mainLangCtx->method('getMainLanguageMapping')->willReturn($propertyMapping);

        $resolver->method('resolveForErgonodeValue')->willReturn($propertyMapping);
        $valueResolver->method('resolve')->willReturn(['en_US' => 'name']);

        $propertyAssigner->expects($this->once())
            ->method('assign')
            ->with($product, ProductPropertyMapping::NAME, $propertyMapping, ['en_US' => 'name']);

        $assigner = new ProductValuesAssigner(
            $resolver,
            $propertyAssigner,
            $mainLangCtx,
            $valueResolver,
            $attributeAssigner,
            $relationStorage,
            $apiClient
        );

        $assigner->assign([
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

    public function test_assign_delegates_to_attribute_assigner_when_no_property_mapping(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $propertyAssigner = $this->createMock(ProductPropertyValueAssignerInterface::class);
        $mainLangCtx = $this->createMock(MainLanguageContextInterface::class);
        $valueResolver = $this->createMock(ProductAttributeValueResolverInterface::class);
        $attributeAssigner = $this->createMock(ProductAttributeValueAssignerInterface::class);
        $relationStorage = $this->createMock(ProductsRelationsStorageInterface::class);
        $apiClient = $this->createMock(ProductApiClientInterface::class);

        $mainLangMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mainLangCtx->method('getMainLanguageMapping')->willReturn($mainLangMapping);
        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $valueResolver->method('resolve')->willReturn(['en_US' => 'Sunglasses']);

        $attributeAssigner->expects($this->once())
            ->method('assign')
            ->with($product, ['en_US' => 'Sunglasses'], 'name');

        $assigner = new ProductValuesAssigner($resolver, $propertyAssigner, $mainLangCtx, $valueResolver, $attributeAssigner, $relationStorage, $apiClient);

        $assigner->assign([
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
            ]
        ], $integration, $product);
    }

    public function test_assign_stores_product_relation_values(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $propertyAssigner = $this->createMock(ProductPropertyValueAssignerInterface::class);
        $mainLangCtx = $this->createMock(MainLanguageContextInterface::class);
        $valueResolver = $this->createMock(ProductAttributeValueResolverInterface::class);
        $attributeAssigner = $this->createMock(ProductAttributeValueAssignerInterface::class);
        $relationStorage = $this->createMock(ProductsRelationsStorageInterface::class);
        $apiClient = $this->createMock(ProductApiClientInterface::class);

        $mainLangMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mainLangCtx->method('getMainLanguageMapping')->willReturn($mainLangMapping);
        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $valueResolver->method('resolve')->willReturn(['en_US' => 'product-slug']);

        $relationStorage->expects($this->once())
            ->method('storeAssociation')
            ->with($integration, $product, 'related', ['en_US' => 'product-slug']);

        $assigner = new ProductValuesAssigner($resolver, $propertyAssigner, $mainLangCtx, $valueResolver, $attributeAssigner, $relationStorage, $apiClient);

        $assigner->assign([
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

    public function test_assign_throws_on_missing_node_key(): void
    {
        $this->expectException(SynchronizationException::class);

        $integration = $this->createMock(IntegrationInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $propertyAssigner = $this->createMock(ProductPropertyValueAssignerInterface::class);
        $mainLangCtx = $this->createMock(MainLanguageContextInterface::class);
        $valueResolver = $this->createMock(ProductAttributeValueResolverInterface::class);
        $attributeAssigner = $this->createMock(ProductAttributeValueAssignerInterface::class);
        $relationStorage = $this->createMock(ProductsRelationsStorageInterface::class);
        $apiClient = $this->createMock(ProductApiClientInterface::class);

        $assigner = new ProductValuesAssigner($resolver, $propertyAssigner, $mainLangCtx, $valueResolver, $attributeAssigner, $relationStorage, $apiClient);

        $assigner->assign([
            'sku' => 'white_cap',
            'attributeList' => [
                'edges' => [[ 'invalid_key' => [] ]],
                'pageInfo' => ['hasNextPage' => false],
            ]
        ], $integration, $product);
    }
}
