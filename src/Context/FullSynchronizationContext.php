<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Context;

final class FullSynchronizationContext implements FullSynchronizationContextInterface
{
    private bool $fullSynchronization;

    public function isFullSynchronization(): bool
    {
        return $this->fullSynchronization;
    }

    public function setFullSynchronization(bool $fullSynchronization): void
    {
        $this->fullSynchronization = $fullSynchronization;
    }
}
