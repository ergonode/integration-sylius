<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Encryptor;

use BitBag\SyliusErgonodePlugin\Encryptor\Encryptor;
use BitBag\SyliusErgonodePlugin\Exception\EncryptionException;
use PHPUnit\Framework\TestCase;

final class EncryptorTest extends TestCase
{
    private string $keyFilePath;

    protected function setUp(): void
    {
        $this->keyFilePath = sys_get_temp_dir() . '/encryptor-test.key';

        if (file_exists($this->keyFilePath)) {
            unlink($this->keyFilePath);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->keyFilePath)) {
            unlink($this->keyFilePath);
        }
    }

    public function test_encrypt_and_decrypt(): void
    {
        $encryptor = new Encryptor($this->keyFilePath);
        $original = 'my secret';

        $encrypted = $encryptor->encrypt($original);

        $this->assertNotEquals($original, $encrypted);
        $this->assertStringEndsWith(Encryptor::ENCRYPTION_SUFFIX, $encrypted);

        $decrypted = $encryptor->decrypt($encrypted);
        $this->assertSame($original, $decrypted);
    }

    public function test_encrypt_and_decrypt_across_instances(): void
    {
        $encryptor1 = new Encryptor($this->keyFilePath);
        $encrypted = $encryptor1->encrypt('token123');

        $encryptor2 = new Encryptor($this->keyFilePath);
        $decrypted = $encryptor2->decrypt($encrypted);

        $this->assertSame('token123', $decrypted);
    }

    public function test_decrypt_plain_value(): void
    {
        $encryptor = new Encryptor($this->keyFilePath);

        $plain = 'already-plain';
        $result = $encryptor->decrypt($plain);

        $this->assertSame($plain, $result);
    }

    public function test_decrypt_null_value_throws(): void
    {
        $this->expectException(EncryptionException::class);

        $encryptor = new Encryptor($this->keyFilePath);
        $encryptor->decrypt(null);
    }

    public function test_decrypt_with_invalid_ciphertext_throws(): void
    {
        $this->expectException(EncryptionException::class);

        $encryptor = new Encryptor($this->keyFilePath);
        $encryptor->decrypt('invalid-ciphertext' . Encryptor::ENCRYPTION_SUFFIX);
    }

    public function test_encryption_key_is_persistent_between_instances(): void
    {
        $encryptor1 = new Encryptor($this->keyFilePath);
        $encrypted = $encryptor1->encrypt('persisted');

        $this->assertFileExists($this->keyFilePath);

        $encryptor2 = new Encryptor($this->keyFilePath);
        $decrypted = $encryptor2->decrypt($encrypted);

        $this->assertSame('persisted', $decrypted);
    }

    public function test_decrypt_empty_string_with_suffix_throws(): void
    {
        $this->expectException(EncryptionException::class);

        $encryptor = new Encryptor($this->keyFilePath);
        $encryptor->decrypt(Encryptor::ENCRYPTION_SUFFIX);
    }
}
