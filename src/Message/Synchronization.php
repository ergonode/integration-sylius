<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class Synchronization
{
    public function __construct(
        private int $integrationId,
        private bool $fullSynchronization,
    ) {
    }

    public function getIntegrationId(): int
    {
        return $this->integrationId;
    }

    public function isFullSynchronization(): bool
    {
        return $this->fullSynchronization;
    }
}
