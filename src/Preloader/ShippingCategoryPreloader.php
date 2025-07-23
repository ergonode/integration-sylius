<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Preloader;

use Sylius\Component\Shipping\Model\ShippingCategoryInterface;
use Sylius\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

final class ShippingCategoryPreloader extends ResourcePreloader implements ShippingCategoryPreloaderInterface
{
    public function getShippingCategoryByCode(string $code): ?ShippingCategoryInterface
    {
        /** @var ShippingCategoryInterface $shippingCategory */
        $shippingCategory = $this->getResourceByCode($code);
        $this->validateResource($shippingCategory);

        return $shippingCategory;
    }

    protected function mapResults(array $resources): array
    {
        return array_map(function (ShippingCategoryInterface $shippingCategory) {
            $this->validateResource($shippingCategory);
            $code = $shippingCategory->getCode();
            Assert::notNull($code);

            return [mb_strtolower($code) => $shippingCategory];
        }, $resources);
    }

    protected function validateResource(?ResourceInterface $resource): void
    {
        if (null === $resource) {
            return;
        }

        Assert::isInstanceOf(
            $resource,
            ShippingCategoryInterface::class,
            sprintf(
                'Preloaded resource of class `%s` should be an instance of `%s`, instead `%s given.',
                self::class,
                ShippingCategoryInterface::class,
                get_class($resource),
            ),
        );
    }
}
