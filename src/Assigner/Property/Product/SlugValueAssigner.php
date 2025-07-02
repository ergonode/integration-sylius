<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\TranslatableTextValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class SlugValueAssigner extends TranslatableTextValueAssigner
{
    public function assign(
        ResourceInterface $resource,
        array $values,
        ResourceRelationMappingInterface $mapping,
    ): void {
        $this->assignValue($resource, $values, function (ProductInterface $resource, string $value, string $locale) {
            $translation = $resource->getTranslation($locale);
            if ($locale !== $translation->getLocale()) {
                return;
            }

            $translation->setSlug($value);
        });
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        if (!$resource instanceof ProductInterface) {
            return true;
        }

        return ProductPropertyMapping::SLUG === ProductPropertyMapping::tryFrom($property);
    }
}
