<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\ProductVariant;

use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\ProductVariant\ProductVariantOptionAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\ProductVariant\ProductVariantPropertyValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\ProductVariant\ProductVariantValuesAssigner;
use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;

final class ProductVariantValuesAssignerTest extends TestCase
{
    public function testAssignDelegatesToOptionAssignerIfOptionPresent(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $optionAssigner = $this->createMock(ProductVariantOptionAssignerInterface::class);

        $optionAssigner->expects($this->once())->method('assign');

        $assigner = new ProductVariantValuesAssigner(
            $this->createMock(ResourceRelationMappingResolverInterface::class),
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ProductVariantAttributeValueResolverInterface::class),
            $this->createMock(ProductVariantPropertyValueAssignerInterface::class),
            $optionAssigner,
            $this->createMock(ProductApiClientInterface::class),
            $this->createMock(LoggerInterface::class)
        );

        $assigner->assign([
            'sku' => '1234',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => 'TextValue',
                        'attribute' => ['code' => 'color'],
                        'value' => ['value'],
                    ],
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ],
        ], $integration, $variant, ['color' => $this->createMock(ProductOptionInterface::class)]);
    }

    public function testAssignThrowsOnMissingNodeKey(): void
    {
        $this->expectException(SynchronizationException::class);

        $assigner = new ProductVariantValuesAssigner(
            $this->createMock(ResourceRelationMappingResolverInterface::class),
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ProductVariantAttributeValueResolverInterface::class),
            $this->createMock(ProductVariantPropertyValueAssignerInterface::class),
            $this->createMock(ProductVariantOptionAssignerInterface::class),
            $this->createMock(ProductApiClientInterface::class),
            $this->createMock(LoggerInterface::class)
        );

        $assigner->assign([
            'sku' => '1234',
            'attributeList' => [
                'edges' => [[/* missing 'node' */]],
                'pageInfo' => ['hasNextPage' => false],
            ],
        ], $this->createMock(IntegrationInterface::class), $this->createMock(ProductVariantInterface::class), []);
    }

    public function testAssignLogsMissingMapping(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->method('getId')->willReturn(99);
        $variant = $this->createMock(ProductVariantInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(1))->method('info');

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $valueAssigner = $this->createMock(ProductVariantPropertyValueAssignerInterface::class);
        $valueAssigner->expects($this->never())->method('assign');

        $assigner = new ProductVariantValuesAssigner(
            $resolver,
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ProductVariantAttributeValueResolverInterface::class),
            $valueAssigner,
            $this->createMock(ProductVariantOptionAssignerInterface::class),
            $this->createMock(ProductApiClientInterface::class),
            $logger
        );

        $assigner->assign([
            'sku' => 'red-socks',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => 'TextValue',
                        'attribute' => ['code' => 'missing'],
                        'value' => [
                            'language' => 'en_US',
                            'value' =>'text'
                        ],
                    ],
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ]
        ], $integration, $variant, []);
    }

    public function testAssignCallsPropertyValueAssignerWhenMappingAndTypeKnown(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $mainLangMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')->willReturn($mapping);
        $mapping->method('getSyliusValue')->willReturn(ProductVariantPropertyMapping::WIDTH->value);
        $mainLanguageContext = $this->createMock(MainLanguageContextInterface::class);
        $mainLanguageContext->method('getMainLanguageMapping')->willReturn($mainLangMapping);

        $valueResolver = $this->createMock(ProductVariantAttributeValueResolverInterface::class);
        $valueResolver->method('resolve')->willReturn(['en_US' => 15]);

        $propertyAssigner = $this->createMock(ProductVariantPropertyValueAssignerInterface::class);
        $propertyAssigner->expects($this->once())->method('assign');

        $assigner = new ProductVariantValuesAssigner(
            $resolver,
            $mainLanguageContext,
            $valueResolver,
            $propertyAssigner,
            $this->createMock(ProductVariantOptionAssignerInterface::class),
            $this->createMock(ProductApiClientInterface::class),
            $this->createMock(LoggerInterface::class),
        );

        $assigner->assign([
            'sku' => 'green-boots',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => 'NumericValue',
                        'attribute' => ['code' => 'product_width'],
                        'value' => [
                            'language' => 'en_SY',
                            'value' => 15
                        ],
                    ]
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ]
        ], $integration, $variant, []);
    }

    public function testAssignLogsUnresolvedProperty(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getId')->willReturn(123);
        $mapping->method('getSyliusValue')->willReturn('unknown_property_value');
        $mainLangMapping = $this->createMock(ResourceRelationMappingInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')->willReturn($mapping);

        $mainLanguageContext = $this->createMock(MainLanguageContextInterface::class);
        $mainLanguageContext->method('getMainLanguageMapping')->willReturn($mainLangMapping);

        $valueResolver = $this->createMock(ProductVariantAttributeValueResolverInterface::class);
        $valueResolver->method('resolve')->willReturn(['en_US' => 'some_value']);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
               ->method('info')
               ->with($this->stringContains('Failed to resolve property with property relation mapping'));

        $assigner = new ProductVariantValuesAssigner(
            $resolver,
            $mainLanguageContext,
            $valueResolver,
            $this->createMock(ProductVariantPropertyValueAssignerInterface::class),
            $this->createMock(ProductVariantOptionAssignerInterface::class),
            $this->createMock(ProductApiClientInterface::class),
            $logger
        );

        $assigner->assign([
            'sku' => 'blue-shirt',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => 'TextValue',
                        'attribute' => ['code' => 'unknown_attribute'],
                        'value' => [
                            'language' => 'en_US',
                            'value' => 'some_value'
                        ],
                    ]
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ]
        ], $integration, $variant, []);
    }

    public function testAssignLoadsNextPageWhenAvailable(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $sku = 'pagination-test-sku';
        $endCursor = 'next-page-cursor';

        $apiClient = $this->createMock(ProductApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('getProductAttributes')
                  ->with($integration, $sku, $endCursor)
                  ->willReturn([
                      'sku' => $sku,
                      'attributeList' => [
                          'edges' => [],
                          'pageInfo' => ['hasNextPage' => false],
                      ]
                  ]);

        $assigner = new ProductVariantValuesAssigner(
            $this->createMock(ResourceRelationMappingResolverInterface::class),
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ProductVariantAttributeValueResolverInterface::class),
            $this->createMock(ProductVariantPropertyValueAssignerInterface::class),
            $this->createMock(ProductVariantOptionAssignerInterface::class),
            $apiClient,
            $this->createMock(LoggerInterface::class)
        );

        $assigner->assign([
            'sku' => $sku,
            'attributeList' => [
                'edges' => [],
                'pageInfo' => [
                    'hasNextPage' => true,
                    'endCursor' => $endCursor
                ],
            ]
        ], $integration, $variant, []);
    }

    public function testAssignLogsErrorWhenOptionAssignmentFails(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $variant->method('getCode')->willReturn('failing-variant');
        $option = $this->createMock(ProductOptionInterface::class);
        $errorMessage = 'Option assignment error';

        $optionAssigner = $this->createMock(ProductVariantOptionAssignerInterface::class);
        $optionAssigner->method('assign')->willThrowException(new \Exception($errorMessage));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
               ->method('error')
               ->with($this->stringContains('Failed to assign option'));

        $assigner = new ProductVariantValuesAssigner(
            $this->createMock(ResourceRelationMappingResolverInterface::class),
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ProductVariantAttributeValueResolverInterface::class),
            $this->createMock(ProductVariantPropertyValueAssignerInterface::class),
            $optionAssigner,
            $this->createMock(ProductApiClientInterface::class),
            $logger
        );

        $assigner->assign([
            'sku' => 'variant-with-failing-option',
            'attributeList' => [
                'edges' => [[
                    'node' => [
                        '__typename' => 'TextValue',
                        'attribute' => ['code' => 'color'],
                        'value' => ['value' => 'red'],
                    ]
                ]],
                'pageInfo' => ['hasNextPage' => false],
            ]
        ], $integration, $variant, ['color' => $option]);
    }
}
