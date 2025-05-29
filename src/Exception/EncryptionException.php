<?php

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
}
