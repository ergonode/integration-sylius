<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\PropertyValueAssigner;

use BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\TaxonNameAssigner;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\CategoryNode;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\LocalizedText;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

final class TaxonNameAssignerTest extends TestCase
{
    public function test_assigns_translations_based_on_mapping(): void
    {
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $integration = $this->createMock(IntegrationInterface::class);
        $taxon = $this->createMock(TaxonInterface::class);
        $categoryNode = $this->createMock(CategoryNode::class);
        $localizedText = $this->createMock(LocalizedText::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $translation = $this->createMock(TaxonTranslationInterface::class);

        $categoryNode->method('getName')->willReturn([$localizedText]);
        $localizedText->method('getLanguage')->willReturn('en_US');
        $localizedText->method('getValue')->willReturn('Laptops');

        $resolver
            ->method('resolveForErgonodeValue')
            ->with($integration, 'en_US', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn($mapping);

        $mapping->method('getSyliusValue')->willReturn('en_US');

        $taxon->method('getTranslation')->with('en_US')->willReturn($translation);
        $translation->expects(self::once())->method('setName')->with('Laptops');

        $assigner = new TaxonNameAssigner($resolver);
        $assigner->assign($integration, $taxon, $categoryNode);
    }

    public function test_skips_translation_if_mapping_is_null(): void
    {
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $integration = $this->createMock(IntegrationInterface::class);
        $taxon = $this->createMock(TaxonInterface::class);
        $categoryNode = $this->createMock(CategoryNode::class);
        $localizedText = $this->createMock(LocalizedText::class);

        $categoryNode->method('getName')->willReturn([$localizedText]);
        $localizedText->method('getLanguage')->willReturn('fr_FR');

        $resolver
            ->method('resolveForErgonodeValue')
            ->with($integration, 'fr_FR', RelationType::LANGUAGE_EXTERNAL_ID)
            ->willReturn(null);

        $taxon->expects(self::never())->method('getTranslation');

        $assigner = new TaxonNameAssigner($resolver);
        $assigner->assign($integration, $taxon, $categoryNode);
    }

    public function test_assigns_multiple_translations(): void
    {
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $integration = $this->createMock(IntegrationInterface::class);
        $taxon = $this->createMock(TaxonInterface::class);
        $categoryNode = $this->createMock(CategoryNode::class);

        $localizedTextEn = $this->createMock(LocalizedText::class);
        $localizedTextPl = $this->createMock(LocalizedText::class);

        $mappingEn = $this->createMock(ResourceRelationMappingInterface::class);
        $mappingPl = $this->createMock(ResourceRelationMappingInterface::class);

        $translationEn = $this->createMock(TaxonTranslationInterface::class);
        $translationPl = $this->createMock(TaxonTranslationInterface::class);

        $categoryNode->method('getName')->willReturn([$localizedTextEn, $localizedTextPl]);

        $localizedTextEn->method('getLanguage')->willReturn('en_US');
        $localizedTextEn->method('getValue')->willReturn('Laptops');

        $localizedTextPl->method('getLanguage')->willReturn('pl_PL');
        $localizedTextPl->method('getValue')->willReturn('Laptopy');

        $resolver->method('resolveForErgonodeValue')->willReturnMap([
            [$integration, 'en_US', RelationType::LANGUAGE_EXTERNAL_ID, $mappingEn],
            [$integration, 'pl_PL', RelationType::LANGUAGE_EXTERNAL_ID, $mappingPl],
        ]);

        $mappingEn->method('getSyliusValue')->willReturn('en_US');
        $mappingPl->method('getSyliusValue')->willReturn('pl_PL');

        $taxon->method('getTranslation')->willReturnMap([
            ['en_US', $translationEn],
            ['pl_PL', $translationPl],
        ]);

        $translationEn->expects(self::once())->method('setName')->with('Laptops');
        $translationPl->expects(self::once())->method('setName')->with('Laptopy');

        $assigner = new TaxonNameAssigner($resolver);
        $assigner->assign($integration, $taxon, $categoryNode);
    }

    public function test_does_nothing_if_no_names(): void
    {
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $integration = $this->createMock(IntegrationInterface::class);
        $taxon = $this->createMock(TaxonInterface::class);
        $categoryNode = $this->createMock(CategoryNode::class);

        $categoryNode->method('getName')->willReturn([]);

        $taxon->expects(self::never())->method('getTranslation');

        $assigner = new TaxonNameAssigner($resolver);
        $assigner->assign($integration, $taxon, $categoryNode);
    }
}
