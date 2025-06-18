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

final readonly class ListValueResolver extends ValueResolver
{
    public function resolve(
        IntegrationInterface $integration,
        array $attribute,
        array $attributeValue,
        ResourceRelationMappingInterface $languageExternalId,
    ): array {
        $values = [];
        foreach ($attributeValue as $value) {
            if (!array_key_exists('value', $value) && null !== $value['value']) {
                continue;
            }

            if (!array_key_exists('translatedValue', $value)) {
                continue;
            }
            $syliusLocaleCode = $this->getSyliusLocaleCode($integration, $value['language']);

            if (null === $syliusLocaleCode) {
                continue;
            }
            if (array_key_exists('name', $value['translatedValue'])) {
                $values[$syliusLocaleCode] = [
                    'code' => $value['value']['code'],
                    'name' => $value['translatedValue']['name'],
                ];

                continue;
            }
            $code = $value['value']['code'];
            $translation = array_filter($value['translatedValue'], function ($translatedValue) use ($code) {
                return $code === $translatedValue['code'];
            });

            $translatedValue = array_shift($translation);
            if (null === $translatedValue || !array_key_exists('name', $translatedValue)) {
                continue;
            }

            $values[$syliusLocaleCode] = [
                'code' => $code,
                'name' => $translatedValue['name'],
            ];
        }

        return $values;
    }

    public function supports(string $typename, ResourceRelationMappingInterface $languageExternalId): bool
    {
        return in_array(AttributeType::tryFrom($typename), [
            AttributeType::SELECT,
            AttributeType::MULTISELECT,
        ], true);
    }
}
