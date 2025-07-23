<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Exception;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;

class SynchronizationException extends \Exception
{
    public static function missingCategoryTreeCode(IntegrationInterface $integration): self
    {
        return new self(sprintf(
            'Failed to synchronize the category tree, missing ergonode category tree code for integration with id `%s`',
            $integration->getId(),
        ));
    }

    public static function noMainLanguageMappingFound(IntegrationInterface $integration): self
    {
        return new self(
            sprintf(
                'No main language mapping found for integration with id `%s`.',
                $integration->getId(),
            ),
        );
    }

    public static function invalidArrayStructure(array $data, string $missingKey): self
    {
        return new self(sprintf(
            'Invalid array structure, expected key `%s`. Encoded value: `%s`.',
            $missingKey,
            json_encode($data, \JSON_THROW_ON_ERROR),
        ));
    }

    public static function missingErgonodeValue(ResourceRelationMappingInterface $mapping): self
    {
        return new self(
            sprintf(
                'No Ergonode value found found for mapping with id `%s`.',
                $mapping->getId(),
            ),
        );
    }

    public static function missingSyliusValue(ResourceRelationMappingInterface $mapping): self
    {
        return new self(
            sprintf(
                'No Sylius value found found for mapping with id `%s`.',
                $mapping->getId(),
            ),
        );
    }

    public static function invalidRelationType(?RelationType $relationType): self
    {
        return new self(
            sprintf(
                'Could not resolve property based relation type `%s`.',
                $relationType?->value,
            ),
        );
    }

    public static function invalidAssociationTypeCode(mixed $code): self
    {
        return new self(
            sprintf(
                'Invalid association type code, expected string, got `%s`.',
                $code,
            ),
        );
    }
}
