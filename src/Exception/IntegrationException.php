<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Exception;

final class IntegrationException extends \Exception
{
    public static function notFoundEnabledWithId(int $id): self
    {
        return new self(sprintf('Integration with id `%s` does not exist or is disabled.', $id));
    }

    public static function notFoundEnabledWithCode(string $code): self
    {
        return new self(sprintf('Integration with code `%s` does not exist or is disabled.', $code));
    }
}
