<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Provider;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class TaxonProvider implements TaxonProviderInterface
{
    /**
     * @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository
     * @param TaxonFactoryInterface<TaxonInterface> $taxonFactory
     */
    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly TaxonFactoryInterface $taxonFactory,
    ) {
    }

    public function provide(IntegrationInterface $integration, string $ergonodeCategoryCode): TaxonInterface
    {
        if (null !== $taxon = $this->taxonRepository->findOneBy(['code' => $ergonodeCategoryCode])) {
            return $taxon;
        }

        $mapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $ergonodeCategoryCode,
            RelationType::CATEGORY_EXTERNAL_ID,
        );

        if (null === $mapping) {
            return $this->taxonFactory->createNew();
        }

        if (null !== $taxon = $this->taxonRepository->findOneBy(['code' => $mapping->getSyliusValue()])) {
            return $taxon;
        }

        return $this->taxonFactory->createNew();
    }
}
