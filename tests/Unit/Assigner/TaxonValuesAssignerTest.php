<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner;

use BitBag\SyliusErgonodePlugin\API\Client\CategoryApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Taxon\TaxonNameAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Taxon\TaxonPropertyValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\TaxonValuesAssigner;
use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\CategoryNode;
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final class TaxonValuesAssignerTest extends TestCase
{
    public function testAssignInvokesAllResolversAndAssigners(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->method('getId')->willReturn(123);

        $categoryNode = $this->createMock(CategoryNode::class);
        $taxon = $this->createMock(TaxonInterface::class);

        $attributeNode = [
            'typename' => 'TextAttributeValue',
            'attribute' => ['code' => 'description'],
            'value' => ['en_US' => 'sample value'],
        ];

        $categoryNode->method('getAttributeListNodes')->willReturn([
            ['node' => $attributeNode],
        ]);
        $categoryNode->method('getCode')->willReturn('cat-001');
        $categoryNode->method('getPageInfo')->willReturn(['hasNextPage' => false, 'endCursor' => 'XYZ']);

        $propertyMapping = $this->createMock(ResourceRelationMappingInterface::class);
        $languageMapping = $this->createMock(ResourceRelationMappingInterface::class);

        $resourceRelationResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resourceRelationResolver
            ->expects(self::once())
            ->method('resolveForErgonodeValue')
            ->with($integration, 'description', RelationType::CATEGORY_PROPERTY)
            ->willReturn($propertyMapping);

        $languageContext = $this->createMock(MainLanguageContextInterface::class);
        $languageContext->method('getMainLanguageMapping')->willReturn($languageMapping);

        $attributeValueResolver = $this->createMock(CategoryAttributeValueResolverInterface::class);
        $attributeValueResolver
            ->method('resolve')
            ->with($integration, $languageMapping, $propertyMapping, 'TextAttributeValue', ['code' => 'description'], ['en_US' => 'sample value'])
            ->willReturn(['en_US' => 'sample value']);

        $propertyValueAssigner = $this->createMock(TaxonPropertyValueAssignerInterface::class);
        $propertyValueAssigner
            ->expects(self::once())
            ->method('assign')
            ->with($integration, $taxon, $propertyMapping, ['en_US' => 'sample value']);

        $nameAssigner = $this->createMock(TaxonNameAssignerInterface::class);
        $nameAssigner->expects(self::once())->method('assign')->with($integration, $taxon, $categoryNode);

        $logger = $this->createMock(LoggerInterface::class);
        $apiClient = $this->createMock(CategoryApiClientInterface::class);
        $apiClient->expects(self::never())->method('getCategoryAttributes');

        $assigner = new TaxonValuesAssigner(
            $resourceRelationResolver,
            $propertyValueAssigner,
            $nameAssigner,
            $languageContext,
            $attributeValueResolver,
            $apiClient,
            $logger,
        );

        $assigner->assign($categoryNode, $integration, $taxon);
    }

    public function testAssignLogsWarningIfMappingMissing(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 456,
        ]);
        $categoryNode = $this->createMock(CategoryNode::class);
        $taxon = $this->createMock(TaxonInterface::class);

        $categoryNode->method('getAttributeListNodes')->willReturn([
            ['node' => [
                'typename' => 'TextAttributeValue',
                'attribute' => ['code' => 'missing_attribute'],
                'value' => 'irrelevant',
            ]],
        ]);

        $categoryNode->method('getPageInfo')->willReturn(['hasNextPage' => false, 'endCursor' => 'XYZ']);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')->willReturn(null);

        $nameAssigner = $this->createMock(TaxonNameAssignerInterface::class);
        $nameAssigner->expects(self::once())->method('assign');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')
            ->with(sprintf(
                'Missing property relation mapping for integration id `%s`, relation type %s and value %s',
                456,
                'missing_attribute',
                RelationType::CATEGORY_PROPERTY->value,
            ));

        $apiClient = $this->createMock(CategoryApiClientInterface::class);
        $apiClient->expects(self::never())->method('getCategoryAttributes');
        $assigner = new TaxonValuesAssigner(
            $resolver,
            $this->createMock(TaxonPropertyValueAssignerInterface::class),
            $nameAssigner,
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(CategoryAttributeValueResolverInterface::class),
            $apiClient,
            $logger,
        );

        $assigner->assign($categoryNode, $integration, $taxon);
    }

    public function test_assign_throws_exception_on_missing_node_key(): void
    {
        $this->expectException(SynchronizationException::class);
        $this->expectExceptionMessage('Invalid array structure, expected key `node`. Encoded value: `{"invalid_key":"value"}`.');

        $integration = $this->createMock(IntegrationInterface::class);
        $categoryNode = $this->createMock(CategoryNode::class);
        $taxon = $this->createMock(TaxonInterface::class);

        $categoryNode->method('getAttributeListNodes')->willReturn([
            ['invalid_key' => 'value'],
        ]);

        $assigner = new TaxonValuesAssigner(
            $this->createMock(ResourceRelationMappingResolverInterface::class),
            $this->createMock(TaxonPropertyValueAssignerInterface::class),
            $this->createMock(TaxonNameAssignerInterface::class),
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(CategoryAttributeValueResolverInterface::class),
            $this->createMock(CategoryApiClientInterface::class),
            $this->createMock(LoggerInterface::class),
        );

        $assigner->assign($categoryNode, $integration, $taxon);
    }

    public function test_assign_recursively_fetches_next_page(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->method('getId')->willReturn(789);

        $taxon = $this->createMock(TaxonInterface::class);
        $categoryNode = $this->createMock(CategoryNode::class);

        $firstPageAttributeNode = [
            'typename' => 'TextAttributeValue',
            'attribute' => ['code' => 'description'],
            'value' => ['en_US' => 'page1 value'],
        ];

        $secondPageAttributeNode = [
            'typename' => 'TextAttributeValue',
            'attribute' => ['code' => 'seo_title'],
            'value' => ['en_US' => 'page2 value'],
        ];

        $categoryNode->method('getAttributeListNodes')->willReturn([
            ['node' => $firstPageAttributeNode],
        ]);

        $categoryNode->method('getPageInfo')->willReturn([
            'hasNextPage' => true,
            'endCursor' => 'XYZ',
        ]);

        $categoryNode->method('getCode')->willReturn('cat-001');

        $propertyMapping1 = $this->createMock(ResourceRelationMappingInterface::class);
        $propertyMapping2 = $this->createMock(ResourceRelationMappingInterface::class);
        $languageMapping = $this->createMock(ResourceRelationMappingInterface::class);

        $resourceRelationResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resourceRelationResolver->method('resolveForErgonodeValue')->willReturnMap([
            [$integration, 'description', RelationType::CATEGORY_PROPERTY, $propertyMapping1],
            [$integration, 'seo_title', RelationType::CATEGORY_PROPERTY, $propertyMapping2],
        ]);

        $languageContext = $this->createMock(MainLanguageContextInterface::class);
        $languageContext->method('getMainLanguageMapping')->willReturn($languageMapping);

        $attributeValueResolver = $this->createMock(CategoryAttributeValueResolverInterface::class);
        $attributeValueResolver->method('resolve')->willReturn(['en_US' => 'value']);

        $propertyValueAssigner = $this->createMock(TaxonPropertyValueAssignerInterface::class);
        $propertyValueAssigner->expects(self::exactly(2))
            ->method('assign')
        ;

        $nameAssigner = $this->createMock(TaxonNameAssignerInterface::class);
        $nameAssigner->expects(self::once())->method('assign');

        $logger = $this->createMock(LoggerInterface::class);

        $apiClient = $this->createMock(CategoryApiClientInterface::class);
        $apiClient->expects(self::once())
            ->method('getCategoryAttributes')
            ->with($integration, 'cat-001', 'XYZ')
            ->willReturn([
                'data' => [
                    'category' => [
                        'attributeList' => [
                            'edges' => [
                                ['node' => $secondPageAttributeNode],
                            ],
                            'pageInfo' => [
                                'hasNextPage' => false,
                                'endCursor' => 'ZYX',
                            ],
                        ],
                    ],
                ],
            ]);

        $assigner = new TaxonValuesAssigner(
            $resourceRelationResolver,
            $propertyValueAssigner,
            $nameAssigner,
            $languageContext,
            $attributeValueResolver,
            $apiClient,
            $logger,
        );

        $assigner->assign($categoryNode, $integration, $taxon);
    }
}
