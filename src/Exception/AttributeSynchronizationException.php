<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Exception;

use BitBag\SyliusErgonodePlugin\Enum\AttributeType;

final class AttributeSynchronizationException extends SynchronizationException
{
    public static function undefinedTargetType(AttributeType $attributeType): self
    {
        return new self(sprintf('Target type for Ergonode attribute type `%s` is not defined', $attributeType->value));
    }

    public static function notSupportedType(string $typename): self
    {
        return new self(sprintf('Attribute type "%s" is not supported.', $typename));
    }

    public static function attributeDoesNotSupportList(string $attributeCode): self
    {
        return new self(sprintf('Ergonode attribute `%s` does not contain options list property in response.', $attributeCode));
    }
}
