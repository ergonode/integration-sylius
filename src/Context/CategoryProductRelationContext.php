<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Context;

use Sylius\Component\Core\Model\TaxonInterface;

final class CategoryProductRelationContext implements CategoryProductRelationContextInterface
{
    private array $context = [];

    public function add(TaxonInterface $taxon, string $productIdentifier): void
    {
        $this->context[$taxon->getCode()][] = $productIdentifier;
    }

    public function getForTaxon(TaxonInterface $taxon): array
    {
        if (null === $taxon->getCode() || !array_key_exists($taxon->getCode(), $this->context)) {
            return [];
        }

        return $this->context[$taxon->getCode()];
    }

    public function getAll(): array
    {
        return $this->context;
    }
}
