<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Encryptor;

use BitBag\SyliusErgonodePlugin\Exception\EncryptionException;

interface EncryptorInterface
{
    public const ENCRYPTION_SUFFIX = '#ENCRYPTED';

    public const ENCRYPTION_SUFFIX_LENGTH = 10;

    /** @throws EncryptionException */
    public function encrypt(string $data): string;

    /** @throws EncryptionException */
    public function decrypt(?string $data): string;
}
