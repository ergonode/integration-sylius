<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Resolver;
use BitBag\SyliusErgonodePlugin\Checker\Category\CategoryTranslatablePropertyCheckerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributeType;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\CategorySynchronizationException;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttribute\ValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttributeValueResolver;
use PHPUnit\Framework\TestCase;

final class CategoryAttributeValueResolverTest extends TestCase
{
    public function test_returns_translatable_values(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $propertyMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::DESCRIPTION->value,
        ]);
        $mainLanguageMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getErgonodeValue' => 'en',
        ]);

        $valueResolver = $this->createMock(ValueResolverInterface::class);
        $valueResolver->method('supports')->willReturn(true);
        $valueResolver->method('resolve')->willReturn([
            'en' => 'Description EN',
            'pl' => 'Opis PL',
        ]);

        $translatableChecker = $this->createMock(CategoryTranslatablePropertyCheckerInterface::class);
        $translatableChecker->method('check')->willReturn(true);

        $resolver = new CategoryAttributeValueResolver([$valueResolver], $translatableChecker);

        $result = $resolver->resolve(
            $integration,
            $mainLanguageMapping,
            $propertyMapping,
            'TEXT',
            ['code' => 'desc'],
            [['language' => 'en', 'value' => 'Description EN'], ['language' => 'pl', 'value' => 'Opis PL']],
        );

        $this->assertSame([
            'en' => 'Description EN',
            'pl' => 'Opis PL',
        ], $result);
    }

    public function test_returns_main_language_only_when_not_translatable(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $propertyMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::ENABLED->value,
        ]);
        $mainLanguageMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getErgonodeValue' => 'en',
        ]);

        $valueResolver = $this->createMock(ValueResolverInterface::class);
        $valueResolver->method('supports')->willReturn(true);
        $valueResolver->method('resolve')->willReturn([
            'en' => 'true',
            'pl' => 'false',
        ]);

        $translatableChecker = $this->createMock(CategoryTranslatablePropertyCheckerInterface::class);
        $translatableChecker->method('check')->willReturn(false);

        $resolver = new CategoryAttributeValueResolver([$valueResolver], $translatableChecker);

        $result = $resolver->resolve(
            $integration,
            $mainLanguageMapping,
            $propertyMapping,
            AttributeType::TEXT->value,
            ['code' => 'enabled'],
            [['language' => 'en', 'value' => 'true'], ['language' => 'pl', 'value' => 'false']],
        );

        $this->assertSame(['en' => 'true'], $result);
    }

    public function test_throws_if_no_resolver_supports_type(): void
    {
        $this->expectException(CategorySynchronizationException::class);

        $integration = $this->createMock(IntegrationInterface::class);
        $propertyMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::DESCRIPTION->value,
        ]);
        $mainLanguageMapping = $this->createMock(ResourceRelationMappingInterface::class);

        $unsupportedResolver = $this->createMock(ValueResolverInterface::class);
        $unsupportedResolver->method('supports')->willReturn(false);

        $resolver = new CategoryAttributeValueResolver([$unsupportedResolver], $this->createMock(CategoryTranslatablePropertyCheckerInterface::class));
        $resolver->resolve($integration, $mainLanguageMapping, $propertyMapping, 'UNSUPPORTED', [], []);
    }

    public function test_throws_if_sylius_value_is_missing(): void
    {
        $this->expectException(SynchronizationException::class);

        $integration = $this->createMock(IntegrationInterface::class);
        $propertyMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => null,
        ]);
        $mainLanguageMapping = $this->createMock(ResourceRelationMappingInterface::class);

        $valueResolver = $this->createMock(ValueResolverInterface::class);
        $valueResolver->method('supports')->willReturn(true);
        $valueResolver->method('resolve')->willReturn([]);

        $resolver = new CategoryAttributeValueResolver([$valueResolver], $this->createMock(CategoryTranslatablePropertyCheckerInterface::class));
        $resolver->resolve($integration, $mainLanguageMapping, $propertyMapping, AttributeType::TEXT->value, [], []);
    }

    public function test_throws_if_main_language_has_no_ergonode_value(): void
    {
        $this->expectException(SynchronizationException::class);

        $integration = $this->createMock(IntegrationInterface::class);
        $propertyMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::SLUG->value,
        ]);
        $mainLanguageMapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getErgonodeValue' => null,
        ]);

        $valueResolver = $this->createMock(ValueResolverInterface::class);
        $valueResolver->method('supports')->willReturn(true);
        $valueResolver->method('resolve')->willReturn(['en' => 'slug-val']);

        $translatableChecker = $this->createMock(CategoryTranslatablePropertyCheckerInterface::class);
        $translatableChecker->method('check')->willReturn(false);

        $resolver = new CategoryAttributeValueResolver([$valueResolver], $translatableChecker);
        $resolver->resolve($integration, $mainLanguageMapping, $propertyMapping, AttributeType::TEXT->value, [], []);
    }
}
