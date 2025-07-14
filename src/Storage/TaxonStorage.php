<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Storage;

use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class TaxonStorage implements TaxonStorageInterface
{
    private array $taxons = [];

    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
    ) {
    }

    public function getTaxonByCode(string $code): ?TaxonInterface
    {
        if ([] === $this->taxons) {
            $this->preload();
        }

        if (!array_key_exists($code, $this->taxons)) {
            return null;
        }

        return $this->taxons[$code];
    }

    private function preload(): void
    {
        /** @var array<TaxonInterface> $taxons */
        $taxons = $this->taxonRepository->findAll();

        $this->taxons = array_merge(
            ...array_map(function (TaxonInterface $productAssociationType) {
                return [$productAssociationType->getCode() => $productAssociationType];
            }, $taxons),
        );
    }
}
