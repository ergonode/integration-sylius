<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Resolver;

use BitBag\SyliusErgonodePlugin\DependencyInjection\Registry\ResourceRelationMappingStorageRegistryInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolver;
use BitBag\SyliusErgonodePlugin\Storage\ResourceRelationMappingStorageInterface;
use PHPUnit\Framework\TestCase;

final class ResourceRelationMappingResolverTest extends TestCase
{
    public function test_returns_null_if_storage_not_found(): void
    {
        $registry = $this->createMock(ResourceRelationMappingStorageRegistryInterface::class);
        $integration = $this->createMock(IntegrationInterface::class);

        $relationType = RelationType::ATTRIBUTE_PROPERTY;

        $registry
            ->expects($this->exactly(2))
            ->method('hasStorage')
            ->with($relationType)
            ->willReturn(false);

        $resolver = new ResourceRelationMappingResolver($registry);

        $this->assertNull($resolver->resolveForSyliusValue($integration, 'color', $relationType));
        $this->assertNull($resolver->resolveForErgonodeValue($integration, 'color', $relationType));
    }

    public function test_resolves_sylius_value(): void
    {
        $registry = $this->createMock(ResourceRelationMappingStorageRegistryInterface::class);
        $storage = $this->createMock(ResourceRelationMappingStorageInterface::class);
        $integration = $this->createMock(IntegrationInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $relationType = RelationType::CATEGORY_PROPERTY;

        $registry
            ->expects($this->once())
            ->method('hasStorage')
            ->with($relationType)
            ->willReturn(true);

        $registry
            ->expects($this->once())
            ->method('getStorage')
            ->with($relationType)
            ->willReturn($storage);

        $storage
            ->expects($this->once())
            ->method('resolveForSyliusValue')
            ->with($integration, 'color')
            ->willReturn($mapping);

        $resolver = new ResourceRelationMappingResolver($registry);

        $result = $resolver->resolveForSyliusValue($integration, 'color', $relationType);
        $this->assertSame($mapping, $result);
    }

    public function test_resolves_ergonode_value(): void
    {
        $registry = $this->createMock(ResourceRelationMappingStorageRegistryInterface::class);
        $storage = $this->createMock(ResourceRelationMappingStorageInterface::class);
        $integration = $this->createMock(IntegrationInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $relationType = RelationType::CATEGORY_EXTERNAL_ID;

        $registry
            ->expects($this->once())
            ->method('hasStorage')
            ->with($relationType)
            ->willReturn(true);

        $registry
            ->expects($this->once())
            ->method('getStorage')
            ->with($relationType)
            ->willReturn($storage);

        $storage
            ->expects($this->once())
            ->method('resolveForErgonodeValue')
            ->with($integration, 'category-123')
            ->willReturn($mapping);

        $resolver = new ResourceRelationMappingResolver($registry);

        $result = $resolver->resolveForErgonodeValue($integration, 'category-123', $relationType);
        $this->assertSame($mapping, $result);
    }
}
