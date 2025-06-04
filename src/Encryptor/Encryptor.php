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
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\HaliteAlert;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

final class Encryptor implements EncryptorInterface
{
    private ?EncryptionKey $key = null;

    public function __construct(
        private readonly string $encryptionKeyPath,
    ) {
    }

    public function encrypt(string $data): string
    {
        try {
            return Crypto::encrypt(new HiddenString($data), $this->getKey()) . self::ENCRYPTION_SUFFIX;
        } catch (HaliteAlert|\SodiumException|\TypeError $exception) {
            throw EncryptionException::cannotEncrypt($exception);
        }
    }

    public function decrypt(?string $data): string
    {
        if (null === $data) {
            throw EncryptionException::dataIsNull();
        }

        if (!str_ends_with($data, self::ENCRYPTION_SUFFIX)) {
            return $data;
        }

        try {
            $data = substr($data, 0, -self::ENCRYPTION_SUFFIX_LENGTH);

            return Crypto::decrypt($data, $this->getKey())->getString();
        } catch (HaliteAlert|\SodiumException|\TypeError $exception) {
            throw EncryptionException::cannotDecrypt($exception);
        }
    }

    private function getKey(): EncryptionKey
    {
        if (null === $this->key) {
            try {
                if (!file_exists($this->encryptionKeyPath)) {
                    $this->key = KeyFactory::generateEncryptionKey();
                    KeyFactory::save($this->key, $this->encryptionKeyPath);
                } else {
                    $this->key = KeyFactory::loadEncryptionKey($this->encryptionKeyPath);
                }
            } catch (CannotPerformOperation|InvalidKey $exception) {
                throw EncryptionException::invalidKey($exception);
            }
        }

        return $this->key;
    }
}
