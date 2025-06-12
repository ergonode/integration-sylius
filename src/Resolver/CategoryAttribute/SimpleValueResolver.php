<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver\CategoryAttribute;

use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributeType;

final class SimpleValueResolver implements ValueResolverInterface
{
    public function resolve(array $attributeValue, ResourceRelationMappingInterface $languageExternalId): array
    {
        return array_merge(...array_map(function ($localizedValue) {
            return [$localizedValue['language'] => $localizedValue['value']];
        }, $attributeValue));
    }

    public function supports(array $attributeValue, ResourceRelationMappingInterface $languageExternalId): bool
    {
        return in_array($attributeValue['typename'], [
            AttributeType::TEXT,
            AttributeType::TEXTAREA,
            AttributeType::DATE,
            AttributeType::NUMBER,
            AttributeType::PRICE
        ], true);
    }
}
