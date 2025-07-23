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
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\CategorySynchronizationException;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\AttributeValue\ValueResolverInterface;

final readonly class CategoryAttributeValueResolver implements CategoryAttributeValueResolverInterface
{
    public function __construct(
        private iterable $valueResolvers,
        private CategoryTranslatablePropertyCheckerInterface $categoryTranslatablePropertyChecker,
    ) {
    }

    public function resolve(
        IntegrationInterface $integration,
        ResourceRelationMappingInterface $mainLanguageMapping,
        ResourceRelationMappingInterface $propertyMapping,
        string $typename,
        array $categoryAttribute,
        array $categoryAttributeValue,
    ): array {
        $values = $this->getValues($integration, $categoryAttribute, $categoryAttributeValue, $typename, $propertyMapping);

        $categoryProperty = $propertyMapping->getSyliusValue();
        if (null === $categoryProperty) {
            throw SynchronizationException::missingSyliusValue($propertyMapping);
        }

        $propertyEnum = CategoryPropertyMapping::tryFrom($categoryProperty);
        if ($this->categoryTranslatablePropertyChecker->check($propertyEnum)) {
            return $values;
        }

        if (null === $mainLanguageMapping->getErgonodeValue()) {
            throw SynchronizationException::missingErgonodeValue($mainLanguageMapping);
        }

        return array_key_exists($mainLanguageMapping->getErgonodeValue(), $values)
            ? [$mainLanguageMapping->getErgonodeValue() => $values[$mainLanguageMapping->getErgonodeValue()]]
            : []
        ;
    }

    private function getValues(
        IntegrationInterface $integration,
        array $categoryAttribute,
        array $categoryAttributeValue,
        string $typename,
        ResourceRelationMappingInterface $propertyMapping,
    ): array {
        /** @var ValueResolverInterface $valueResolver */
        foreach ($this->valueResolvers as $valueResolver) {
            if (!$valueResolver->supports(RelationType::CATEGORY_PROPERTY, $typename)) {
                continue;
            }

            return $valueResolver->resolve(
                $integration,
                $categoryAttribute,
                $categoryAttributeValue,
            );
        }

        throw CategorySynchronizationException::unsupportedCategoryAttributeValue($typename, $categoryAttributeValue);
    }
}
