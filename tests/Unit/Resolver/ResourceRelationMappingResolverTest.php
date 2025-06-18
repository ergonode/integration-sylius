<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Resolver;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Repository\ResourceRelationMappingRepositoryInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolver;
use PHPUnit\Framework\TestCase;

final class ResourceRelationMappingResolverTest extends TestCase
{
    public function test_resolves_by_sylius_value_and_caches(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 1,
        ]);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);
        $repository->expects(self::once())
        ->method('findOneByIntegrationAndTypeAndSyliusValue')
            ->with($integration, RelationType::LANGUAGE_EXTERNAL_ID, 'en_US')
            ->willReturn($mapping);

        $resolver = new ResourceRelationMappingResolver($repository);

        $result1 = $resolver->resolveForSyliusValue($integration, 'en_US', RelationType::LANGUAGE_EXTERNAL_ID);
        $result2 = $resolver->resolveForSyliusValue($integration, 'en_US', RelationType::LANGUAGE_EXTERNAL_ID);

        $this->assertSame($mapping, $result1);
        $this->assertSame($result1, $result2);
    }

    public function test_resolves_by_ergonode_value_and_caches(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 42,
        ]);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);
        $repository->expects(self::once())
        ->method('findOneByIntegrationAndTypeAndErgonodeValue')
            ->with($integration, RelationType::CATEGORY_PROPERTY, 'description')
            ->willReturn($mapping);

        $resolver = new ResourceRelationMappingResolver($repository);

        $result1 = $resolver->resolveForErgonodeValue($integration, 'description', RelationType::CATEGORY_PROPERTY);
        $result2 = $resolver->resolveForErgonodeValue($integration, 'description', RelationType::CATEGORY_PROPERTY);

        $this->assertSame($mapping, $result1);
        $this->assertSame($result2, $result1);
    }

    public function test_returns_null_if_mapping_not_found(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 5,
        ]);

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);
        $repository->method('findOneByIntegrationAndTypeAndSyliusValue')->willReturn(null);

        $resolver = new ResourceRelationMappingResolver($repository);

        $result = $resolver->resolveForSyliusValue($integration, 'missing-code', RelationType::CATEGORY_EXTERNAL_ID);

        $this->assertNull($result);
    }
}
