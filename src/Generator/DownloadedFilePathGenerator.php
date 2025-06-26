<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Generator;

final readonly class DownloadedFilePathGenerator implements DownloadedFilePathGeneratorInterface
{
    public function generate(string $filename): string
    {
        $hash = bin2hex(random_bytes(4));
        $filename = str_replace(' ', '_', $filename);

        return sprintf(
            '%s/%s/%s',
            substr($hash, 0, 2),
            substr($hash, 2, 2),
            $filename,
        );
    }
}
