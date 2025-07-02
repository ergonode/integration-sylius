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
use BitBag\SyliusErgonodePlugin\Enum\AttributeValueType;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;

final readonly class SimpleValueResolver extends ValueResolver
{
    public function resolve(
        IntegrationInterface $integration,
        array $attribute,
        array $attributeValue,
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

    public function supports(
        RelationType $relationType,
        string $typename,
    ): bool {
        return in_array(AttributeValueType::tryFrom($typename), [
            AttributeValueType::TEXT,
            AttributeValueType::TEXTAREA,
            AttributeValueType::DATE,
            AttributeValueType::NUMBER,
            AttributeValueType::PRICE,
            AttributeValueType::FILE,
            AttributeValueType::GALLERY,
            AttributeValueType::IMAGE,
        ], true);
    }
}
