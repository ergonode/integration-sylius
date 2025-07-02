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

final readonly class ListValueResolver extends ValueResolver
{
    public function resolve(
        IntegrationInterface $integration,
        array $attribute,
        array $attributeValue,
    ): array {
        $values = [];
        foreach ($attributeValue as $value) {
            if (!array_key_exists('value', $value) || null === $value['value']) {
                continue;
            }

            if (!array_key_exists('translatedValue', $value)) {
                continue;
            }

            $syliusLocaleCode = $this->getSyliusLocaleCode($integration, $value['language']);

            if (null === $syliusLocaleCode) {
                continue;
            }

            $rowValue = $value['value'];

            if (isset($rowValue['code'])) {
                $code = $rowValue['code'];
                $values[$syliusLocaleCode] = $this->getValue($code, $value['translatedValue']);
                continue;
            }

            if (is_array($rowValue)) {
                foreach ($rowValue as $entry) {
                    if (!isset($entry['code'])) {
                        continue;
                    }

                    $code = $entry['code'];
                    $values[$syliusLocaleCode][] = $this->getValue($code, $value['translatedValue']);
                }
            }
        }

        return $values;
    }

    public function getValue(string $code, array $translatedValues): string
    {
        if (array_key_exists('name', $translatedValues)) {
            return $translatedValues['name'] ?? $translatedValue['code'] ?? $code;
        }

        $translation = array_filter($translatedValues, function ($translatedValue) use ($code) {
            return $code === $translatedValue['code'];
        });

        return array_shift($translation)['name'] ?? $code;
    }

    public function supports(
        RelationType $relationType,
        string $typename,
    ): bool {
        return in_array(AttributeValueType::tryFrom($typename), [
            AttributeValueType::SELECT,
            AttributeValueType::MULTISELECT,
        ], true);
    }
}
