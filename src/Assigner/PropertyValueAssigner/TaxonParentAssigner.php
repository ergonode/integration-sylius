<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner;

use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final readonly class TaxonParentAssigner implements TaxonParentAssignerInterface
{
    public function __construct(
        private TaxonRepositoryInterface $taxonRepository,
    ) {
    }

    public function assign(TaxonInterface $taxon, string $parentCategoryCode): void
    {
        $parentCategory = $this->taxonRepository->findOneBy(['code' => $parentCategoryCode]);
        if (!$parentCategory instanceof TaxonInterface) {
            return;
        }

        $taxon->setParent($parentCategory);
    }
}
