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
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttribute\UnitValueResolver;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;

final class UnitValueResolverTest extends TestCase
{
    public function test_resolves_unit_values(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);

        $mapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'en_US',
        ]);

        $mappingResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $mappingResolver
            ->expects(self::once())
            ->method('resolveForErgonodeValue')
            ->with($integration, 'en', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($mapping);

        $resolver = new UnitValueResolver($mappingResolver);

        $attribute = [
            'code' => 'length',
            'unit' => ['symbol]' => 'cm'],
        ];

        $attributeValue = [[
            'language' => 'en',
            'value' => '100',
        ]];

        $result = $resolver->resolve($integration, $attribute, $attributeValue, $mapping);

        $this->assertSame(['en_US' => '100 cm'], $result);
    }

    public function test_skips_if_language_unmapped(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);

        $mappingResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $mappingResolver->method('resolveForErgonodeValue')->willReturn(null);

        $resolver = new UnitValueResolver($mappingResolver);

        $attribute = [
            'code' => 'length',
            'unit' => ['symbol]' => 'cm'],
        ];

        $attributeValue = [[
            'language' => 'xx',
            'value' => '200',
        ]];

        $result = $resolver->resolve($integration, $attribute, $attributeValue, $this->createMock(ResourceRelationMappingInterface::class));
        $this->assertSame([], $result);
    }

    public function test_supports_unit_type_only(): void
    {
        $resolver = new UnitValueResolver($this->createMock(ResourceRelationMappingResolverInterface::class));
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->assertTrue($resolver->supports(AttributeValueType::UNIT->value, $mapping));
        $this->assertFalse($resolver->supports(AttributeValueType::GALLERY->value, $mapping));
        $this->assertFalse($resolver->supports('undefined', $mapping));
    }
}
