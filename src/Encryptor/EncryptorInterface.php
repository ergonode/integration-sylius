<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Encryptor;

use Sylius\Component\Payment\Encryption\Exception\EncryptionException;

interface EncryptorInterface
{
    public const ENCRYPTION_SUFFIX = '#ENCRYPTED';

    public const ENCRYPTION_SUFFIX_LENGTH = 10;

    public function encrypt(string $data): string;

    public function decrypt(string $data): string;
}
