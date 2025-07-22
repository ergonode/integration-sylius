<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Provider;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Provider\TaxonProvider;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class TaxonProviderTest extends TestCase
{
    public function testProvidesExistingTaxonFromMapping(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn('mapped-code');

        $resolvedTaxon = $this->createMock(TaxonInterface::class);

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')
            ->with($integration, 'cat-1', RelationType::CATEGORY_EXTERNAL_ID)
            ->willReturn($mapping);

        $repository = $this->createMock(TaxonRepositoryInterface::class);
        $repository->method('findOneBy')->with(['code' => 'mapped-code'])->willReturn($resolvedTaxon);

        $factory = $this->createMock(TaxonFactoryInterface::class);

        $provider = new TaxonProvider($repository, $resolver, $factory);
        $result = $provider->provide($integration, 'cat-1');

        $this->assertSame($resolvedTaxon, $result);
    }

    public function testCreatesNewTaxonWhenMappingIsNull(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')->willReturn(null);

        $newTaxon = $this->createMock(TaxonInterface::class);
        $newTaxon->expects(self::once())->method('setCode')->with('cat-new');

        $repository = $this->createMock(TaxonRepositoryInterface::class);
        $factory = $this->createMock(TaxonFactoryInterface::class);
        $factory->method('createNew')->willReturn($newTaxon);

        $provider = new TaxonProvider($repository, $resolver, $factory);
        $result = $provider->provide($integration, 'cat-new');

        $this->assertSame($newTaxon, $result);
    }

    public function test_returns_taxon_by_ergonode_code_when_mapped_code_not_found(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn('mapped-code');

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')->willReturn($mapping);

        $repository = $this->createMock(TaxonRepositoryInterface::class);
        $repository->method('findOneBy')->willReturnCallback(function ($criteria) {
            if ($criteria['code'] === 'mapped-code') {
                return null;
            }
            if ($criteria['code'] === 'cat-2') {
                $taxon = $this->createMock(TaxonInterface::class);
                return $taxon;
            }
            return null;
        });

        $factory = $this->createMock(TaxonFactoryInterface::class);

        $provider = new TaxonProvider($repository, $resolver, $factory);
        $result = $provider->provide($integration, 'cat-2');

        $this->assertInstanceOf(TaxonInterface::class, $result);
    }

    public function test_creates_new_taxon_if_neither_mapping_nor_code_found(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $mapping->method('getSyliusValue')->willReturn('missing-code');

        $resolver = $this->createMock(ResourceRelationMappingResolverInterface::class);
        $resolver->method('resolveForErgonodeValue')->willReturn($mapping);

        $repository = $this->createMock(TaxonRepositoryInterface::class);
        $repository->method('findOneBy')->willReturn(null);

        $newTaxon = $this->createMock(TaxonInterface::class);
        $newTaxon->expects(self::once())->method('setCode')->with('cat-404');

        $factory = $this->createMock(TaxonFactoryInterface::class);
        $factory->method('createNew')->willReturn($newTaxon);

        $provider = new TaxonProvider($repository, $resolver, $factory);
        $result = $provider->provide($integration, 'cat-404');

        $this->assertSame($newTaxon, $result);
    }
}
