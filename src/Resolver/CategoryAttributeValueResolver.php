<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

use BitBag\SyliusErgonodePlugin\Checker\Category\CategoryTranslatablePropertyCheckerInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;

final class CategoryAttributeValueResolver implements CategoryAttributeValueResolverInterface
{
    public function __construct(
        private readonly CategoryTranslatablePropertyCheckerInterface $categoryTranslatablePropertyChecker,
    ) {
    }
    public function resolve(
        ResourceRelationMappingInterface $mainLanguageMapping,
        ResourceRelationMappingInterface $propertyMapping,
        string $typename,
        array $categoryAttributeValue,
    ): array {
        $values = $this->getFlattenValues($categoryAttributeValue);
        $categoryProperty = $propertyMapping->getSyliusValue();
        $propertyEnum = CategoryPropertyMapping::tryFrom($categoryProperty);

        if ($this->categoryTranslatablePropertyChecker->check($propertyEnum)) {
            return $values;
        }

        return array_key_exists($mainLanguageMapping->getErgonodeValue(), $values)
            ? $values[$mainLanguageMapping->getErgonodeValue()]
            : []
        ;
    }

    private function getFlattenValues(array $attributeValue): array
    {
        return array_merge(...array_map(function ($localizedValue) {
            return [$localizedValue['language'] => $localizedValue['value']];
        }, $attributeValue));
    }
}
