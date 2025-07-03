<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Webmozart\Assert\Assert;

final class MainTaxonValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
    ) {
    }

    public function assign(ResourceInterface $resource, array $values, ResourceRelationMappingInterface $mapping): void
    {
        Assert::isInstanceOf($resource, ProductInterface::class);
        /** @var ?TaxonInterface $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $values[0]]);
        $resource->setMainTaxon($taxon);
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        return
            $resource instanceof ProductInterface &&
            ProductPropertyMapping::MAIN_TAXON === ProductPropertyMapping::tryFrom($property)
        ;
    }
}
