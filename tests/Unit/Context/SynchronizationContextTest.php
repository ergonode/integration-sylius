<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Context;

use BitBag\SyliusErgonodePlugin\Context\SynchronizationContext;
use BitBag\SyliusErgonodePlugin\Context\SynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use PHPUnit\Framework\TestCase;

final class SynchronizationContextTest extends TestCase
{
    private SynchronizationContext $context;

    protected function setUp(): void
    {
        $this->context = new SynchronizationContext();
    }

    public function testInitializeWithFullSynchronization(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->expects($this->atMost(1))
            ->method('isFirstSynchronization')
            ->willReturn(false);

        $integration->expects($this->once())
            ->method('getCategoryStreamCursorEnd')
            ->willReturn('category-cursor');

        $integration->expects($this->once())
            ->method('getCategoryDeletedStreamCursorEnd')
            ->willReturn('category-deleted-cursor');

        $integration->expects($this->once())
            ->method('getAttributeStreamCursorEnd')
            ->willReturn('attribute-cursor');

        $integration->expects($this->once())
            ->method('getAttributeDeletedStreamCursorEnd')
            ->willReturn('attribute-deleted-cursor');

        $integration->expects($this->once())
            ->method('getProductStreamCursorEnd')
            ->willReturn('product-cursor');

        $integration->expects($this->once())
            ->method('getProductDeletedStreamCursorEnd')
            ->willReturn('product-deleted-cursor');

        $this->context->initialize($integration, true);

        $this->assertTrue($this->context->isFullSynchronization());

        $this->assertEquals('category-cursor', $this->context->getInitialStreamCursor(SynchronizationContextInterface::CATEGORY_STREAM));
        $this->assertEquals('category-deleted-cursor', $this->context->getInitialStreamCursor(SynchronizationContextInterface::CATEGORY_DELETED_STREAM));
        $this->assertEquals('attribute-cursor', $this->context->getInitialStreamCursor(SynchronizationContextInterface::ATTRIBUTE_STREAM));
        $this->assertEquals('attribute-deleted-cursor', $this->context->getInitialStreamCursor(SynchronizationContextInterface::ATTRIBUTE_DELETED_STREAM));
        $this->assertEquals('product-cursor', $this->context->getInitialStreamCursor(SynchronizationContextInterface::PRODUCT_STREAM));
        $this->assertEquals('product-deleted-cursor', $this->context->getInitialStreamCursor(SynchronizationContextInterface::PRODUCT_DELETED_STREAM));
    }

    public function testInitializeWithFirstSynchronization(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->expects($this->once())
            ->method('isFirstSynchronization')
            ->willReturn(true);

        $this->setupCursorReturns($integration);

        $this->context->initialize($integration, false);

        $this->assertTrue($this->context->isFullSynchronization());
    }

    public function testInitializeWithIncrementalSynchronization(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->expects($this->once())
            ->method('isFirstSynchronization')
            ->willReturn(false);

        $this->setupCursorReturns($integration);

        $this->context->initialize($integration, false);

        $this->assertFalse($this->context->isFullSynchronization());
    }

    public function testGetInitialStreamCursorReturnsNullForInvalidStream(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->expects($this->once())
            ->method('isFirstSynchronization')
            ->willReturn(false);

        $this->setupCursorReturns($integration);

        $this->context->initialize($integration, false);

        $this->assertNull($this->context->getInitialStreamCursor('invalid_stream'));
    }

    private function setupCursorReturns(IntegrationInterface $integration): void
    {
        $integration->expects($this->once())
            ->method('getCategoryStreamCursorEnd')
            ->willReturn('category-cursor');

        $integration->expects($this->once())
            ->method('getCategoryDeletedStreamCursorEnd')
            ->willReturn('category-deleted-cursor');

        $integration->expects($this->once())
            ->method('getAttributeStreamCursorEnd')
            ->willReturn('attribute-cursor');

        $integration->expects($this->once())
            ->method('getAttributeDeletedStreamCursorEnd')
            ->willReturn('attribute-deleted-cursor');

        $integration->expects($this->once())
            ->method('getProductStreamCursorEnd')
            ->willReturn('product-cursor');

        $integration->expects($this->once())
            ->method('getProductDeletedStreamCursorEnd')
            ->willReturn('product-deleted-cursor');
    }
}
