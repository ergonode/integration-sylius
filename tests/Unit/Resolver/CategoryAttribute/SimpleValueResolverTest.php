<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Resolver\CategoryAttribute;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributeValueType;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\AttributeValue\SimpleValueResolver;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;

final class SimpleValueResolverTest extends TestCase
{
    public function test_resolves_simple_values_per_locale(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);

        $mapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'en_US',
        ]);

        $mappingResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $mappingResolver->method('resolveForErgonodeValue')
            ->with($integration, 'en', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($mapping);

        $resolver = new SimpleValueResolver($mappingResolver);

        $attributeValue = [[
            'language' => 'en',
            'value' => 'Simple text value',
        ]];

        $result = $resolver->resolve(
            $integration,
            ['code' => 'text_attribute'],
            $attributeValue,
            $this->createMock(ResourceRelationMappingInterface::class),
        );

        $this->assertSame(['en_US' => 'Simple text value'], $result);
    }

    public function test_skips_unmapped_language(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);

        $mappingResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $mappingResolver->method('resolveForErgonodeValue')->willReturn(null);

        $resolver = new SimpleValueResolver($mappingResolver);

        $attributeValue = [[
            'language' => 'unknown',
            'value' => 'value that should be skipped',
        ]];

        $result = $resolver->resolve($integration, [], $attributeValue, $this->createMock(ResourceRelationMappingInterface::class));
        $this->assertSame([], $result);
    }

    public function testSupportsExpectedAttributeTypes(): void
    {
        $resolver = new SimpleValueResolver($this->createMock(ResourceRelationMappingResolverInterface::class));
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $relation = RelationType::CATEGORY_PROPERTY;
        foreach ([
                    AttributeValueType::TEXT,
                    AttributeValueType::TEXTAREA,
                    AttributeValueType::DATE,
                    AttributeValueType::NUMBER,
                    AttributeValueType::PRICE,
                    AttributeValueType::FILE,
                    AttributeValueType::GALLERY,
                    AttributeValueType::IMAGE,
                ] as $validType) {
            $this->assertTrue($resolver->supports($relation, $validType->value, $mapping), "Expected {$validType->value} to be supported.");
        }

        $this->assertFalse($resolver->supports($relation, AttributeValueType::UNIT->value, $mapping));
        $this->assertFalse($resolver->supports($relation, 'undefined', $mapping));
    }
}
