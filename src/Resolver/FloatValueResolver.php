<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

final class FloatValueResolver implements FloatValueResolverInterface
{
    public function resolve(mixed $value): ?float
    {
        if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            return null;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $pos = strrpos($value, ' ');
            $value = false === $pos ? $value : substr($value, 0, $pos);

            return is_numeric($value) ? (float) $value : null;
        }

        return null;
    }
}
