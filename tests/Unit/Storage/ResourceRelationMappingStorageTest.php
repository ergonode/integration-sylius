<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Storage;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Repository\ResourceRelationMappingRepositoryInterface;
use BitBag\SyliusErgonodePlugin\Storage\ResourceRelationMappingStorage;
use PHPUnit\Framework\TestCase;

final class ResourceRelationMappingStorageTest extends TestCase
{
    public function test_resolves_by_sylius_value_and_caches(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 1,
        ]);
        $relationType = RelationType::LANGUAGE_EXTERNAL_ID;
        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);

        $mapping1 = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'en_US',
            'getErgonodeValue' => 'en_SY',
        ]);
        $mapping2 = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'pl_PL',
            'getErgonodeValue' => 'pl_SY',
        ]);

        $repository->expects(self::once())
            ->method('findAllByIntegrationAndType')
            ->with($integration, $relationType)
            ->willReturn([$mapping1, $mapping2])
        ;

        $resolver = new ResourceRelationMappingStorage($repository, $relationType);

        $result1 = $resolver->resolveForSyliusValue($integration, 'en_US');
        $result2 = $resolver->resolveForSyliusValue($integration, 'pl_PL');
        $result3 = $resolver->resolveForSyliusValue($integration, 'en_SY');
        $result4 = $resolver->resolveForSyliusValue($integration, 'de_DE');

        $this->assertSame($mapping1, $result1);
        $this->assertSame($mapping2, $result2);
        $this->assertNull($result3);
        $this->assertNull($result4);
    }

    public function test_resolves_by_ergonode_value_and_caches(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 42,
        ]);

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);
        $relationType = RelationType::CATEGORY_PROPERTY;

        $mapping1 = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'description',
            'getErgonodeValue' => 'short_description',
        ]);
        $mapping2 = $this->createConfiguredMock(ResourceRelationMappingInterface::class, [
            'getSyliusValue' => 'media',
            'getErgonodeValue' => 'images',
        ]);

        $repository->expects(self::once())
            ->method('findAllByIntegrationAndType')
            ->with($integration, $relationType)
            ->willReturn([$mapping1, $mapping2])
        ;

        $resolver = new ResourceRelationMappingStorage($repository, $relationType);

        $result1 = $resolver->resolveForErgonodeValue($integration, 'short_description');
        $result2 = $resolver->resolveForErgonodeValue($integration, 'images');
        $result3 = $resolver->resolveForErgonodeValue($integration, 'media');
        $result4 = $resolver->resolveForErgonodeValue($integration, 'description');

        $this->assertSame($mapping1, $result1);
        $this->assertSame($mapping2, $result2);
        $this->assertNull($result3);
        $this->assertNull($result4);
    }

    public function test_returns_null_if_mapping_not_found(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 5,
        ]);
        $relationType = RelationType::CATEGORY_EXTERNAL_ID;

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findAllByIntegrationAndType')
            ->with($integration, $relationType)
            ->willReturn([])
        ;

        $resolver = new ResourceRelationMappingStorage($repository, $relationType);

        $result = $resolver->resolveForSyliusValue($integration, 'missing-code');

        $this->assertNull($result);
    }
}
