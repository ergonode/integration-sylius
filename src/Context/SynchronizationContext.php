<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Context;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final class SynchronizationContext implements SynchronizationContextInterface
{
    private bool $fullSynchronization;

    private array $initialCursors;

    public function initialize(IntegrationInterface $integration, bool $isFullSynchronization): void
    {
        $this->fullSynchronization = $isFullSynchronization || $integration->isFirstSynchronization();
        $this->initialCursors = [
            self::CATEGORY_STREAM => $integration->getCategoryStreamCursorEnd(),
            self::CATEGORY_DELETED_STREAM => $integration->getCategoryDeletedStreamCursorEnd(),
            self::ATTRIBUTE_STREAM => $integration->getAttributeStreamCursorEnd(),
            self::ATTRIBUTE_DELETED_STREAM => $integration->getAttributeStreamCursorEnd(),
        ];
    }

    public function isFullSynchronization(): bool
    {
        return $this->fullSynchronization;
    }

    public function getInitialStreamCursor(string $stream): ?string
    {
        if (!array_key_exists($stream, $this->initialCursors)) {
            return null;
        }

        return $this->initialCursors[$stream];
    }
}
