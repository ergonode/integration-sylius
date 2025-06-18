<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Context;

use BitBag\SyliusErgonodePlugin\Context\MainLanguageContext;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Repository\ResourceRelationMappingRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class MainLanguageContextTest extends TestCase
{
    public function test_returns_cached_main_language_mapping(): void
    {
        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 1,
            'getMainLanguageCode' => 'en_US',
        ]);

        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findOneByIntegrationAndTypeAndSyliusValue')
            ->with($integration, RelationType::LANGUAGE_EXTERNAL_ID, 'en_US')
            ->willReturn($mapping);

        $context = new MainLanguageContext($repository);

        $result1 = $context->getMainLanguageMapping($integration);
        $result2 = $context->getMainLanguageMapping($integration);

        $this->assertSame($mapping, $result1);
        $this->assertSame($mapping, $result2);
    }

    public function test_throws_if_main_language_code_is_null(): void
    {
        $this->expectException(SynchronizationException::class);
        $this->expectExceptionMessage('Failed to synchronize the category tree, missing ergonode category tree code for integration with id `42`');

        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 42,
            'getMainLanguageCode' => null,
        ]);

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);

        $context = new MainLanguageContext($repository);
        $context->getMainLanguageMapping($integration);
    }

    public function test_throws_if_mapping_not_found(): void
    {
        $this->expectException(SynchronizationException::class);
        $this->expectExceptionMessage('No main language mapping found');

        $integration = $this->createConfiguredMock(IntegrationInterface::class, [
            'getId' => 99,
            'getMainLanguageCode' => 'pl_PL',
        ]);

        $repository = $this->createMock(ResourceRelationMappingRepositoryInterface::class);
        $repository->method('findOneByIntegrationAndTypeAndSyliusValue')->willReturn(null);

        $context = new MainLanguageContext($repository);
        $context->getMainLanguageMapping($integration);
    }
}
