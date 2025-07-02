<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver;

use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Model\ResourceInterface;

final class ProductVariantResolver implements ProductVariantResolverInterface
{
    public function getVariant(ResourceInterface $resource): ProductVariantInterface
    {
        if (!$resource instanceof ProductVariantInterface && !$resource instanceof ProductInterface) {
            throw ProductSynchronizationException::invalidResourceUsed($resource);
        }

        if ($resource instanceof ProductVariantInterface) {
            return $resource;
        }

        $variant = $resource->getVariants()->first();

        if (!$variant instanceof ProductVariantInterface) {
            throw ProductSynchronizationException::couldNotFindVariant($resource->getCode());
        }

        return $variant;
    }
}
