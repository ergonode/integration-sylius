<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Model\Category\Stream;

final class Attribute
{
    public function __construct(
        private readonly string|float|bool|null $code,
        /** @var array<LocalizedText> */
        private readonly array $name,
    ) {
    }

    public function getCode(): string|float|bool|null
    {
        return $this->code;
    }

    public function getName(): array
    {
        return $this->name;
    }
}
