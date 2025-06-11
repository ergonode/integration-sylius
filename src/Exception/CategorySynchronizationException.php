<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Exception;

final class CategorySynchronizationException extends \Exception
{
    public static function invalidCategoryTreeFound(?string $expectedCode, string $actualCode): self
    {
        return new self(
            sprintf(
                'Invalid category tree found. Expected: `%s`, actual: `%s`.',
                $expectedCode,
                $actualCode,
            ),
        );
    }

    public static function unsupportedCategoryAttributeValue(string $typename, array $value): self
    {
        return new self(sprintf(
            'Unsupported category attribute with type %s and value: `%s`.',
            $typename,
            json_encode($value, \JSON_THROW_ON_ERROR),
        ));
    }
}
