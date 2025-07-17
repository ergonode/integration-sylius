<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Preloader;

use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Sylius\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

final class TaxCategoryPreloader extends ResourcePreloader implements TaxCategoryPreloaderInterface
{
    public function getTaxCategoryByCode(string $code): ?TaxCategoryInterface
    {
        /** @var TaxCategoryInterface $taxCategory */
        $taxCategory = $this->getResourceByCode($code);
        $this->validateResource($taxCategory);

        return $taxCategory;
    }

    protected function mapResults(array $resources): array
    {
        return array_map(function (TaxCategoryInterface $taxCategory) {
            $this->validateResource($taxCategory);
            $code = $taxCategory->getCode();
            Assert::notNull($code);

            return [mb_strtolower($code) => $taxCategory];
        }, $resources);
    }

    protected function validateResource(?ResourceInterface $resource): void
    {
        if (null === $resource) {
            return;
        }

        Assert::isInstanceOf(
            $resource,
            TaxCategoryInterface::class,
            sprintf(
                'Preloaded resource of class `%s` should be an instance of `%s`, instead `%s given.',
                self::class,
                TaxCategoryInterface::class,
                get_class($resource),
            ),
        );
    }
}
