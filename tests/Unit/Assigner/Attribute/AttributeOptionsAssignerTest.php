<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Attribute;

use BitBag\SyliusErgonodePlugin\API\Client\AttributeApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Attribute\AttributeOptionsAssigner;
use BitBag\SyliusErgonodePlugin\Context\SynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\AttributeOption\AttributeOptionNameResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Attribute\Model\AttributeInterface;

final class AttributeOptionsAssignerTest extends TestCase
{
    public function test_assign_in_partial_sync_calls_resolve_and_setConfiguration(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $client = $this->createMock(AttributeApiClientInterface::class);
        $resolver = $this->createMock(AttributeOptionNameResolverInterface::class);
        $context = $this->createMock(SynchronizationContextInterface::class);

        $context->expects($this->once())
            ->method('isFullSynchronization')
            ->willReturn(false);

        $attribute->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['choices' => []]);

        $initialOptions = [
            'edges' => [
                ['node' => ['code' => 'RED', 'name' => [['language' => 'en', 'value' => 'Red']]]],
            ],
            'pageInfo' => ['endCursor' => 'cursor1', 'hasNextPage' => true],
        ];

        $response = [
            'data' => [
                'attribute' => [
                    'optionList' => [
                        'edges' => [
                            ['node' => ['code' => 'GREEN', 'name' => [['language' => 'en', 'value' => 'Green']]]],
                        ],
                        'pageInfo' => ['endCursor' => 'cursor2', 'hasNextPage' => false],
                    ]
                ]
            ]
        ];

        $client->expects($this->once())
            ->method('getSelectAttributeOptions')
            ->with($integration, 'color', 'cursor1')
            ->willReturn($response);

        $resolver->expects($this->exactly(2))
            ->method('resolve')
            ->willReturnOnConsecutiveCalls(['en_US' => 'Red'], ['en_US' => 'Green']);

        $attribute->expects($this->once())
            ->method('setConfiguration')
            ->with([
                'choices' => [
                    'ergonode-attribute-option-RED' => ['en_US' => 'Red'],
                    'ergonode-attribute-option-GREEN' => ['en_US' => 'Green'],
                ],
                'ergonode-attribute-option-end-cursor' => 'cursor2',
            ]);

        $assigner = new AttributeOptionsAssigner($client, $resolver, $context);
        $assigner->assign($integration, $attribute, 'color', $initialOptions);
    }

    public function test_assign_in_full_sync_calls_setConfiguration_once(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $client = $this->createMock(AttributeApiClientInterface::class);
        $resolver = $this->createMock(AttributeOptionNameResolverInterface::class);
        $context = $this->createMock(SynchronizationContextInterface::class);

        $context->expects($this->once())
            ->method('isFullSynchronization')
            ->willReturn(true);

        $attribute->expects($this->once())
            ->method('getConfiguration')
            ->willReturn([]);

        $response = [
            'data' => [
                'attribute' => [
                    'optionList' => [
                        'edges' => [
                            ['node' => ['code' => 'CLEAR', 'name' => [['language' => 'en', 'value' => 'Cleared']]]],
                        ],
                        'pageInfo' => ['endCursor' => 'final', 'hasNextPage' => false],
                    ]
                ]
            ]
        ];

        $client->expects($this->once())
            ->method('getSelectAttributeOptions')
            ->with($integration, 'size', null)
            ->willReturn($response);

        $resolver->expects($this->once())
            ->method('resolve')
            ->with([['language' => 'en', 'value' => 'Cleared']], $integration)
            ->willReturn(['en_US' => 'Cleared']);

        $attribute->expects($this->once())
            ->method('setConfiguration')
            ->with([
                'choices' => [
                    'ergonode-attribute-option-CLEAR' => ['en_US' => 'Cleared'],
                ],
                'ergonode-attribute-option-end-cursor' => 'final',
            ]);

        $assigner = new AttributeOptionsAssigner($client, $resolver, $context);
        $assigner->assign($integration, $attribute, 'size', []);
    }

    public function test_assign_throws_when_attribute_array_empty(): void
    {
        $this->expectException(AttributeSynchronizationException::class);
        $this->expectExceptionMessage('Ergonode attribute `broken` does not contain options list property in response.');

        $integration = $this->createMock(IntegrationInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $client = $this->createMock(AttributeApiClientInterface::class);
        $resolver = $this->createMock(AttributeOptionNameResolverInterface::class);
        $context = $this->createMock(SynchronizationContextInterface::class);

        $context->method('isFullSynchronization')->willReturn(true);
        $attribute->method('getConfiguration')->willReturn([]);

        $client->expects($this->once())
            ->method('getSelectAttributeOptions')
            ->willReturn([
                'data' => ['attribute' => []]
            ]);

        $assigner = new AttributeOptionsAssigner($client, $resolver, $context);
        $assigner->assign($integration, $attribute, 'broken', []);
    }

    public function test_assign_throws_when_option_list_missing(): void
    {
        $this->expectException(SynchronizationException::class);
        $this->expectExceptionMessage('Invalid array structure, expected key `attribute.optionList`.');

        $integration = $this->createMock(IntegrationInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $client = $this->createMock(AttributeApiClientInterface::class);
        $resolver = $this->createMock(AttributeOptionNameResolverInterface::class);
        $context = $this->createMock(SynchronizationContextInterface::class);

        $context->method('isFullSynchronization')->willReturn(true);
        $attribute->method('getConfiguration')->willReturn([]);

        $client->expects($this->once())
            ->method('getSelectAttributeOptions')
            ->willReturn([
                'data' => ['attribute' => ['notOptionList' => []]]
            ]);

        $assigner = new AttributeOptionsAssigner($client, $resolver, $context);
        $assigner->assign($integration, $attribute, 'unknown', []);
    }
}
