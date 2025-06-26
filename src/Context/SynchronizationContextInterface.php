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

interface SynchronizationContextInterface
{
    public const CATEGORY_STREAM = 'category_stream';
    public const CATEGORY_DELETED_STREAM = 'category_deleted_stream';
    public const ATTRIBUTE_STREAM = 'attribute_stream';
    public const ATTRIBUTE_DELETED_STREAM = 'attribute_deleted_stream';

    public function initialize(IntegrationInterface $integration, bool $isFullSynchronization): void;

    public function getInitialStreamCursor(string $stream): ?string;

    public function isFullSynchronization(): bool;
}
