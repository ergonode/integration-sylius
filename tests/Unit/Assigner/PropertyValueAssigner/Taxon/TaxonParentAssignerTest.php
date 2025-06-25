<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\PropertyValueAssigner\Taxon;

use BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Taxon\TaxonParentAssigner;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class TaxonParentAssignerTest extends TestCase
{
    public function test_assign_sets_parent_when_found(): void
    {
        $taxonRepository = $this->createMock(TaxonRepositoryInterface::class);
        $childTaxon = $this->createMock(TaxonInterface::class);
        $parentTaxon = $this->createMock(TaxonInterface::class);

        $taxonRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'parent_code'])
            ->willReturn($parentTaxon);

        $childTaxon
            ->expects(self::once())
            ->method('setParent')
            ->with($parentTaxon);

        $assigner = new TaxonParentAssigner($taxonRepository);
        $assigner->assign($childTaxon, 'parent_code');
    }

    public function test_assign_does_nothing_if_parent_not_found(): void
    {
        $taxonRepository = $this->createMock(TaxonRepositoryInterface::class);
        $childTaxon = $this->createMock(TaxonInterface::class);

        $taxonRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'nonexistent_parent'])
            ->willReturn(null);

        $childTaxon
            ->expects(self::never())
            ->method('setParent');

        $assigner = new TaxonParentAssigner($taxonRepository);
        $assigner->assign($childTaxon, 'nonexistent_parent');
    }

    public function test_assign_does_nothing_if_result_is_not_a_taxon(): void
    {
        $taxonRepository = $this->createMock(TaxonRepositoryInterface::class);
        $childTaxon = $this->createMock(TaxonInterface::class);

        $taxonRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'invalid_type'])
            ->willReturn(new \stdClass());

        $childTaxon
            ->expects(self::never())
            ->method('setParent');

        $assigner = new TaxonParentAssigner($taxonRepository);
        $assigner->assign($childTaxon, 'invalid_type');
    }
}
