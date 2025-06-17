<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver\CategoryAttribute;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributeType;

final readonly class SimpleValueResolver extends ValueResolver
{
    public function resolve(
        IntegrationInterface $integration,
        array $attribute,
        array $attributeValue,
        ResourceRelationMappingInterface $languageExternalId,
    ): array {
        $values = [];
        foreach ($attributeValue as $value) {
            $syliusCode = $this->getSyliusLocaleCode($integration, $value['language']);
            if (null === $syliusCode) {
                continue;
            }

            $values[$syliusCode] = $value['value'];
        }

        return $values;
    }

    public function supports(string $typename, ResourceRelationMappingInterface $languageExternalId): bool
    {
        return in_array(AttributeType::tryFrom($typename), [
            AttributeType::TEXT,
            AttributeType::TEXTAREA,
            AttributeType::DATE,
            AttributeType::NUMBER,
            AttributeType::PRICE,
            AttributeType::FILE,
            AttributeType::GALLERY,
            AttributeType::IMAGE,
        ], true);
    }
}
