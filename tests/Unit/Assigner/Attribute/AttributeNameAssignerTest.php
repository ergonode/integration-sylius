<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Attribute;

use BitBag\SyliusErgonodePlugin\Assigner\Attribute\AttributeNameAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;

final class AttributeNameAssignerTest extends TestCase
{
    public function test_assigns_translations_to_locales(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $translationAssigner = $this->createMock(TranslationAssignerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $assigner = new AttributeNameAssigner($resolver, $translationAssigner, $logger);

        $attributeName = [
            'language' => 'en',
            'value' => 'Material',
        ];

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn('en_US');

        $resolver->method('resolveForErgonodeValue')
            ->with($integration, 'en', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($mapping);

        $translation = $this->createMock(AttributeTranslationInterface::class);
        $translation->method('getLocale')->willReturn('en_US');

        $attribute->method('getTranslation')->with('en_US')->willReturn($translation);

        $translation->expects($this->once())->method('setName')->with('Material');

        $assigner->assign($integration, $attribute, [$attributeName]);
    }

    public function test_skips_if_locale_not_found(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $translationAssigner = $this->createMock(TranslationAssignerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $assigner = new AttributeNameAssigner($resolver, $translationAssigner, $logger);

        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $logger->expects($this->once())->method('info')->with('Locale with code "fr" not found.');

        $assigner->assign($integration, $attribute, [
            ['language' => 'fr', 'value' => 'Matière']
        ]);
    }

    public function test_skips_if_value_is_null(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $translationAssigner = $this->createMock(TranslationAssignerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $assigner = new AttributeNameAssigner($resolver, $translationAssigner, $logger);

        $assigner->assign($integration, $attribute, [
            ['language' => 'en', 'value' => null]
        ]);

        $attribute->expects($this->never())->method('getTranslation');
    }
}
