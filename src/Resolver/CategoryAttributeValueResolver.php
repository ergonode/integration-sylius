<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Exception\CategorySynchronizationException;

final class CategoryAttributeValueResolver implements CategoryAttributeValueResolverInterface
{
    public function __construct(
        private readonly iterable $resolvers,
    ) {
    }

    public function resolve(
        ResourceRelationMappingInterface $mainLanguageMapping,
        string $typename,
        array $categoryAttributeValue,
    ): array {
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->supports($mainLanguageMapping, $typename, $categoryAttributeValue)) {
                continue;
            }

            return $resolver->resolve($mainLanguageMapping, $categoryAttributeValue);
        }

        throw CategorySynchronizationException::unsupportedCategoryAttributeValue($typename, $categoryAttributeValue);
    }
}
