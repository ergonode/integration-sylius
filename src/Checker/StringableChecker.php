<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Checker;

final readonly class StringableChecker implements StringableCheckerInterface
{
    public function check(mixed $value): bool
    {
        if (null === $value || is_scalar($value)) {
            return true;
        }

        if ($value instanceof \Stringable) {
            return true;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return true;
        }

        return false;
    }
}
