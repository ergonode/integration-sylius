<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Exception;

use Sylius\Resource\Model\ResourceInterface;

final class ProductSynchronizationException extends SynchronizationException
{
    public static function unsupportedProductAttributeValue(string $typename, array $value): self
    {
        return new self(sprintf(
            'Unsupported product attribute with type %s and value: `%s`.',
            $typename,
            json_encode($value, \JSON_THROW_ON_ERROR),
        ));
    }

    public static function couldNotFindVariant(?string $code): self
    {
        return new self(sprintf(
            'Could not resolve variant for product with code `%s`.',
            $code,
        ));
    }

    public static function missingProductAttribute(string $attributeCode): self
    {
        return new self(sprintf(
            'Could not find attribute with code `%s`.',
            $attributeCode,
        ));
    }

    public static function invalidResourceUsed(ResourceInterface $resource): self
    {
        return new self(sprintf(
            'Failed to process further, instance of `%s` given.',
            get_class($resource),
        ));
    }
}
