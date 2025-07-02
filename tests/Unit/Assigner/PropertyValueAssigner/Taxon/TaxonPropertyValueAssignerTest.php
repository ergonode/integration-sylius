<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\PropertyValueAssigner\Taxon;

use BitBag\SyliusErgonodePlugin\Assigner\Taxon\TaxonPropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Downloader\ErgonodeFileDownloaderInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\TaxonImageInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class TaxonPropertyValueAssignerTest extends TestCase
{
    public function test_throws_exception_if_sylius_value_is_null(): void
    {
        $this->expectException(SynchronizationException::class);

        $assigner = new TaxonPropertyValueAssigner(
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ErgonodeFileDownloaderInterface::class),
            $this->createMock(FactoryInterface::class),
            $this->createMock(TextValueResolverInterface::class),
        );

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn(null);

        $assigner->assign(
            $this->createMock(IntegrationInterface::class),
            $this->createMock(TaxonInterface::class),
            $mapping,
            []
        );
    }

    public function test_assign_description_applies_translation(): void
    {
        $attributeValue = ['en_US' => 'description'];

        $mapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::DESCRIPTION->value
        ]);

        $translation = $this->createMock(TaxonTranslationInterface::class);
        $translation->method('getLocale')->willReturn('en_US');
        $translation->expects(self::once())->method('setDescription')->with('description');

        $taxon = $this->createMock(TaxonInterface::class);
        $taxon->method('getTranslation')->with('en_US')->willReturn($translation);

        $resolver = $this->createMock(TextValueResolverInterface::class);
        $resolver->method('resolve')->willReturn('description');

        $assigner = new TaxonPropertyValueAssigner(
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ErgonodeFileDownloaderInterface::class),
            $this->createMock(FactoryInterface::class),
            $resolver
        );

        $assigner->assign(
            $this->createMock(IntegrationInterface::class),
            $taxon,
            $mapping,
            $attributeValue
        );
    }

    public function test_assign_slug_applies_translation(): void
    {
        $attributeValue = ['en_US' => 'slug'];

        $resolver = $this->createMock(TextValueResolverInterface::class);
        $resolver->method('resolve')->willReturn('resolved_slug');

        $translation = $this->createMock(TaxonTranslationInterface::class);
        $translation->method('getLocale')->willReturn('en_US');
        $translation->expects(self::once())->method('setSlug')->with('resolved_slug');

        $taxon = $this->createMock(TaxonInterface::class);
        $taxon->method('getTranslation')->with('en_US')->willReturn($translation);

        $assigner = new TaxonPropertyValueAssigner(
            $this->createMock(MainLanguageContextInterface::class),
            $this->createMock(ErgonodeFileDownloaderInterface::class),
            $this->createMock(FactoryInterface::class),
            $resolver
        );

        $mapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::SLUG->value
        ]);

        $assigner->assign(
            $this->createMock(IntegrationInterface::class),
            $taxon,
            $mapping,
            $attributeValue
        );
    }

    public function test_assign_enabled_reads_code_boolean(): void
    {
        $mainLanguage = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getErgonodeValue' => 'en_US'
        ]);

        $context = $this->createMock(MainLanguageContextInterface::class);
        $context->expects(self::once())->method('getMainLanguageMapping')->willReturn($mainLanguage);

        $assigner = new TaxonPropertyValueAssigner(
            $context,
            $this->createMock(ErgonodeFileDownloaderInterface::class),
            $this->createMock(FactoryInterface::class),
            $this->createMock(TextValueResolverInterface::class)
        );

        $mapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::ENABLED->value
        ]);

        $assigner->assign(
            $this->createMock(IntegrationInterface::class),
            $this->createMock(TaxonInterface::class),
            $mapping,
            ['en_US' => ['code' => true]]
        );
    }

    public function test_assign_media_downloads_and_adds_image(): void
    {
        $mainLanguage = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'en_US'
        ]);

        $context = $this->createMock(MainLanguageContextInterface::class);
        $context->method('getMainLanguageMapping')->willReturn($mainLanguage);

        $image = $this->createMock(TaxonImageInterface::class);
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createNew')->willReturn($image);

        $downloader = $this->createMock(ErgonodeFileDownloaderInterface::class);
        $downloader->expects(self::once())->method('download')->with(['url' => 'http://img'], $image);

        $taxon = $this->createMock(TaxonInterface::class);
        $taxon->expects(self::once())->method('addImage')->with($image);

        $assigner = new TaxonPropertyValueAssigner(
            $context,
            $downloader,
            $factory,
            $this->createMock(TextValueResolverInterface::class)
        );

        $mapping = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => CategoryPropertyMapping::MEDIA->value
        ]);

        $assigner->assign(
            $this->createMock(IntegrationInterface::class),
            $taxon,
            $mapping,
            ['en_US' => [['url' => 'http://img']]]
        );
    }
}
