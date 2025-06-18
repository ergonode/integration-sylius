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
use BitBag\SyliusErgonodePlugin\Enum\AttributeType;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttribute\ListValueResolver;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;

final class ListValueResolverTest extends TestCase
{
    public function test_resolves_translated_values_with_name_key(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $languageMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'en_US',
        ]);

        $mappingResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $mappingResolver->method('resolveForErgonodeValue')
            ->with($integration, 'en', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($languageMapping);

        $resolver = new ListValueResolver($mappingResolver);
        $attribute = ['code' => 'color'];
        $attributeValue = [[
            'language' => 'en',
            'value' => ['code' => 'red'],
            'translatedValue' => ['name' => 'Red'],
        ]];

        $languageExternalId = $this->createMock(ResourceRelationMappingInterface::class);

        $result = $resolver->resolve($integration, $attribute, $attributeValue, $languageExternalId);

        $this->assertSame([
            'en_US' => ['code' => 'red', 'name' => 'Red'],
        ], $result);
    }

    public function test_resolves_translated_values_with_named_array(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $languageMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'pl_PL',
        ]);

        $mappingResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $mappingResolver->method('resolveForErgonodeValue')
            ->with($integration, 'pl', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($languageMapping);

        $resolver = new ListValueResolver($mappingResolver);

        $attributeValue = [[
            'language' => 'pl',
            'value' => ['code' => 'm'],
            'translatedValue' => [
                ['code' => 's', 'name' => 'Mały'],
                ['code' => 'm', 'name' => 'Średni'],
                ['code' => 'l', 'name' => 'Duży'],
            ],
        ]];

        $result = $resolver->resolve(
            $integration,
            ['code' => 'size'],
            $attributeValue,
            $this->createMock(ResourceRelationMappingInterface::class)
        );

        $this->assertSame([
            'pl_PL' => ['code' => 'm', 'name' => 'Średni'],
        ], $result);
    }

    public function test_skips_if_value_or_translation_missing_or_unmapped(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);

        $mappingResolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $mappingResolver->method('resolveForErgonodeValue')->willReturn(null);

        $resolver = new ListValueResolver($mappingResolver);

        $attributeValue = [
            ['language' => 'xx', 'value' => ['code' => 'a'], 'translatedValue' => ['name' => 'A']],
            ['language' => 'en', 'value' => null, 'translatedValue' => ['name' => 'B']],
            ['language' => 'en', 'value' => ['code' => 'c']],
        ];

        $result = $resolver->resolve($integration, [], $attributeValue, $this->createMock(ResourceRelationMappingInterface::class));
        $this->assertSame([], $result);
    }

    public function test_supports_only_select_and_multiselect(): void
    {
        $resolver = new ListValueResolver($this->createMock(ResourceRelationMappingResolverInterface::class));
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->assertTrue($resolver->supports(AttributeType::SELECT->value, $mapping));
        $this->assertTrue($resolver->supports(AttributeType::MULTISELECT->value, $mapping));
        $this->assertFalse($resolver->supports(AttributeType::TEXTAREA->value, $mapping));
        $this->assertFalse($resolver->supports(AttributeType::UNIT->value, $mapping));
        $this->assertFalse($resolver->supports(AttributeType::DATE->value, $mapping));
        $this->assertFalse($resolver->supports('undefined', $mapping));
    }
}
