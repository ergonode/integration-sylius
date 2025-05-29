<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Exception;

final class EncryptionException extends \Exception
{
    public static function cannotEncrypt(\Throwable $previousException): self
    {
        return new self(
            message: 'Cannot encrypt data.',
            previous: $previousException,
        );
    }

    public static function cannotDecrypt(\Throwable $previousException): self
    {
        return new self(
            message: 'Cannot decrypt data.',
            previous: $previousException,
        );
    }

    public static function invalidKey(\Throwable $previousException): self
    {
        return new self(
            message: 'Invalid encryption key.',
            previous: $previousException,
        );
    }

    public static function dataIsNull(): self
    {
        return new self('Empty data provided as an argument');
    }
}
