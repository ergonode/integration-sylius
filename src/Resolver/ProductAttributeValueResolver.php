<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

use BitBag\SyliusErgonodePlugin\Checker\Product\ProductTranslatablePropertyCheckerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributeValueType;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\AttributeValue\ValueResolverInterface;

final readonly class ProductAttributeValueResolver implements ProductAttributeValueResolverInterface
{
    public function __construct(
        private iterable $valueResolvers,
        private ProductTranslatablePropertyCheckerInterface $productTranslatablePropertyChecker,
    ) {
    }

    public function resolve(
        IntegrationInterface $integration,
        ResourceRelationMappingInterface $mainLanguageMapping,
        string $typename,
        array $productAttribute,
        array $productAttributeValue,
        ?ResourceRelationMappingInterface $propertyMapping,
    ): array {
        if (null === $mainLanguageMapping->getErgonodeValue()) {
            throw SynchronizationException::missingErgonodeValue($mainLanguageMapping);
        }

        $values = $this->getValues($integration, $productAttribute, $productAttributeValue, $typename);

        if ($typename === AttributeValueType::PRODUCT_RELATION->value) {
            return array_key_exists($mainLanguageMapping->getErgonodeValue(), $values)
               ? [$mainLanguageMapping->getErgonodeValue() => $values[$mainLanguageMapping->getErgonodeValue()]]
               : []
            ;
        }

        if (null === $propertyMapping) {
            return $values;
        }

        $property = $propertyMapping->getSyliusValue();
        if (null === $property) {
            throw SynchronizationException::missingSyliusValue($propertyMapping);
        }

        $propertyEnum = ProductPropertyMapping::tryFrom($property);

        if ((null === $propertyEnum || $this->productTranslatablePropertyChecker->check($propertyEnum)) &&
            $typename !== AttributeValueType::PRODUCT_RELATION->value
        ) {
            return $values;
        }

        return array_key_exists($mainLanguageMapping->getErgonodeValue(), $values)
            ? [$mainLanguageMapping->getErgonodeValue() => $values[$mainLanguageMapping->getErgonodeValue()]]
            : []
        ;
    }

    private function getValues(
        IntegrationInterface $integration,
        array $productAttribute,
        array $productyAttributeValue,
        string $typename,
    ): array {
        /** @var ValueResolverInterface $valueResolver */
        foreach ($this->valueResolvers as $valueResolver) {
            if (!$valueResolver->supports(RelationType::PRODUCT_PROPERTY, $typename)) {
                continue;
            }

            return $valueResolver->resolve(
                $integration,
                $productAttribute,
                $productyAttributeValue,
            );
        }

        throw ProductSynchronizationException::unsupportedProductAttributeValue($typename, $productyAttributeValue);
    }
}
