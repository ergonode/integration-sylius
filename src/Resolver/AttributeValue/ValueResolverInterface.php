<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver\AttributeValue;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;

interface ValueResolverInterface
{
    public function resolve(
        IntegrationInterface $integration,
        array $attribute,
        array $attributeValue,
    ): array;

    public function supports(RelationType $relationType, string $typename): bool;
}
