<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Exception;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final class SynchronizationException extends \Exception
{
    public static function missingCategoryTreeCode(IntegrationInterface $integration): self
    {
        return new self(sprintf(
            'Failed to synchronize the category tree, missing ergonode category tree code for integration with id `%s`',
            $integration->getId(),
        ));
    }

    public static function noMainLanguageMappingFound(IntegrationInterface $integration): self
    {
        return new self(
            sprintf(
                'No main language mapping found for integration with id `%s`.',
                $integration->getId(),
            ),
        );
    }
}
