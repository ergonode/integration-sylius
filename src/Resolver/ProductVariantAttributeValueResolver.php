<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

use BitBag\SyliusErgonodePlugin\Checker\Variant\ProductVariantTranslatablePropertyCheckerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\AttributeValue\ValueResolverInterface;

final readonly class ProductVariantAttributeValueResolver implements ProductVariantAttributeValueResolverInterface
{
    public function __construct(
        private iterable $valueResolvers,
        private ProductVariantTranslatablePropertyCheckerInterface $productVariantTranslatablePropertyChecker,
    ) {
    }

    public function resolve(
        IntegrationInterface $integration,
        ResourceRelationMappingInterface $mainLanguageMapping,
        string $typename,
        array $productVariantAttribute,
        array $productVariantAttributeValue,
        ?ResourceRelationMappingInterface $propertyMapping,
    ): array {
        $values = $this->getValues($integration, $productVariantAttribute, $productVariantAttributeValue, $typename);

        if (null === $propertyMapping) {
            return $values;
        }

        $property = $propertyMapping->getSyliusValue();
        if (null === $property) {
            throw SynchronizationException::missingSyliusValue($propertyMapping);
        }

        $propertyEnum = ProductVariantPropertyMapping::tryFrom($property);
        if (null === $propertyEnum || $this->productVariantTranslatablePropertyChecker->check($propertyEnum)) {
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
        array $productVariantAttribute,
        array $productVariantAttributeValue,
        string $typename,
    ): array {
        /** @var ValueResolverInterface $valueResolver */
        foreach ($this->valueResolvers as $valueResolver) {
            if (!$valueResolver->supports(RelationType::VARIANT_PROPERTY, $typename)) {
                continue;
            }

            return $valueResolver->resolve(
                $integration,
                $productVariantAttribute,
                $productVariantAttributeValue,
            );
        }

        throw ProductSynchronizationException::unsupportedProductAttributeValue($typename, $productVariantAttributeValue);
    }
}
