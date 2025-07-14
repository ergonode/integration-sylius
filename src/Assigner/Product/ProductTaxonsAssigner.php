<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use BitBag\SyliusErgonodePlugin\Storage\TaxonStorageInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ProductTaxonsAssigner implements ProductTaxonsAssignerInterface
{
    public function __construct(
        private readonly TaxonStorageInterface $taxonStorage,
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly FactoryInterface $productTaxonFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function assign(array $productNode, IntegrationInterface $integration, ProductInterface $product): void
    {
        $categoryList = $productNode['categoryList'] ?? [];
        foreach ($categoryList['edges'] as $categoryEdge) {
            $category = $categoryEdge['node'];
            $ergonodeValue = $category['code'];

            $mapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $ergonodeValue,
                RelationType::CATEGORY_EXTERNAL_ID,
            );

            $code = $mapping?->getSyliusValue() ?? $ergonodeValue;

            $taxon = $this->taxonStorage->getTaxonByCode($code);
            if (!$taxon instanceof TaxonInterface) {
                $this->logger->info(sprintf('Taxon with code "%s" not found.', $code));

                return;
            }

            if ($product->hasTaxon($taxon)) {
                continue;
            }

            /** @var ProductTaxonInterface $productTaxon */
            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setTaxon($taxon);
            $product->addProductTaxon($productTaxon);
        }
    }
}
