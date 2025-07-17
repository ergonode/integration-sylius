<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Preloader;

use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

final class TaxonPreloader extends ResourcePreloader implements TaxonPreloaderInterface
{
    public function getTaxonByCode(string $code): ?TaxonInterface
    {
        /** @var ?TaxonInterface $taxon */
        $taxon = $this->getResourceByCode($code);
        $this->validateResource($taxon);

        return $taxon;
    }

    protected function mapResults(array $resources): array
    {
        return array_map(function (TaxonInterface $taxon) {
            $this->validateResource($taxon);
            $code = $taxon->getCode();
            Assert::notNull($code);

            return [mb_strtolower($code) => $taxon];
        }, $resources);
    }

    protected function validateResource(?ResourceInterface $resource): void
    {
        if (null === $resource) {
            return;
        }

        Assert::isInstanceOf(
            $resource,
            TaxonInterface::class,
            sprintf(
                'Preloaded resource of class `%s` should be an instance of `%s`, instead `%s given.',
                self::class,
                TaxonInterface::class,
                get_class($resource),
            ),
        );
    }
}
