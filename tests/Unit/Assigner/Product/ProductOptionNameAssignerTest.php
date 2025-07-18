<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductOptionNameAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;

final class ProductOptionNameAssignerTest extends TestCase
{
    public function test_assign_sets_translated_name(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $productOption = $this->createMock(ProductOptionInterface::class);
        $translation = $this->createMock(ProductOptionTranslationInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $assigner = $this->createMock(TranslationAssignerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn('en_US');

        $resolver->expects($this->once())
            ->method('resolveForErgonodeValue')
            ->with($integration, 'en', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($mapping);

        $productOption->method('getTranslation')->with('en_US')->willReturn($translation);
        $translation->method('getLocale')->willReturn('en_US');

        $translation->expects($this->once())->method('setName')->with('Size');

        $assigner = new ProductOptionNameAssigner($resolver, $assigner, $logger);
        $assigner->assign($integration, $productOption, [
            ['language' => 'en', 'value' => 'Size'],
        ]);
    }

    public function test_assign_skips_if_locale_mapping_missing(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $productOption = $this->createMock(ProductOptionInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $assigner = $this->createMock(TranslationAssignerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $resolver->method('resolveForErgonodeValue')->willReturn(null);
        $logger->expects($this->once())->method('info')->with('Locale with code "fr" not found.');

        $assigner = new ProductOptionNameAssigner($resolver, $assigner, $logger);
        $assigner->assign($integration, $productOption, [
            ['language' => 'fr', 'value' => 'Taille'],
        ]);
    }

    public function test_assign_skips_if_value_is_null(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $productOption = $this->createMock(ProductOptionInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $translationAssigner = $this->createMock(TranslationAssignerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn('en_US');

        $resolver->expects($this->once())
            ->method('resolveForErgonodeValue')
            ->with($integration, 'en', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($mapping);

        $translationAssigner->expects($this->never())->method('assign');
        $logger->expects($this->never())->method('info');
        $productOption->expects($this->never())->method('getTranslation');

        $assigner = new ProductOptionNameAssigner($resolver, $translationAssigner, $logger);
        $assigner->assign($integration, $productOption, [
            ['language' => 'en', 'value' => null],
        ]);
    }

    public function test_assign_triggers_translation_assigner_if_locale_mismatch(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $productOption = $this->createMock(ProductOptionInterface::class);
        $translation = $this->createMock(ProductOptionTranslationInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $assigner = $this->createMock(TranslationAssignerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn('en_US');

        $resolver->method('resolveForErgonodeValue')->willReturn($mapping);

        $productOption->expects($this->exactly(2))
            ->method('getTranslation')
            ->with('en_US')
            ->willReturn($translation);

        $translation->method('getLocale')->willReturn('fr_FR');

        $assigner->expects($this->once())
            ->method('assign')
            ->with($productOption, 'en_US');

        $translation->expects($this->once())->method('setName')->with('Label');

        $assigner = new ProductOptionNameAssigner($resolver, $assigner, $logger);
        $assigner->assign($integration, $productOption, [
            ['language' => 'en', 'value' => 'Label'],
        ]);
    }
}
