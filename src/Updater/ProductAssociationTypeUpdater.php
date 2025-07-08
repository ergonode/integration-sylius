<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Updater;

use BitBag\SyliusErgonodePlugin\Assigner\ProductAssociationType\ProductAssociationTypeNameAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Repository\ProductAssociationTypeRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ProductAssociationTypeUpdater implements ProductAssociationTypeUpdaterInterface
{
    public function __construct(
        private readonly ProductAssociationTypeRepositoryInterface $productAssociationTypeRepository,
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly FactoryInterface $productAssociationFactory,
        private readonly ProductAssociationTypeNameAssignerInterface $productAssociationTypeNameAssigner,
    ) {
    }

    public function update(IntegrationInterface $integration, array $associationTypeNode): ProductAssociationTypeInterface
    {
        $productAssociation = $this->getProductAssociation($integration, $associationTypeNode);
        $this->productAssociationTypeNameAssigner->assign($integration, $productAssociation, $associationTypeNode['name']);

        return $productAssociation;
    }

    private function getProductAssociation(IntegrationInterface $integration, array $associationTypeNode): ProductAssociationTypeInterface
    {
        $code = $associationTypeNode['code'];
        $mapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $code,
            RelationType::ATTRIBUTE_EXTERNAL_ID,
        );

        if (null !== $mapping) {
            $productAssociationType = $this->productAssociationTypeRepository->findOneBy(
                ['code' => $mapping->getSyliusValue()],
            );

            if ($productAssociationType instanceof ProductAssociationTypeInterface) {
                return $productAssociationType;
            }
        }
        $productAssociationType = $this->productAssociationTypeRepository->findOneBy(['code' => $code]);
        if ($productAssociationType instanceof ProductAssociationTypeInterface) {
            return $productAssociationType;
        }

        /** @var ProductAssociationTypeInterface $productAssociationType */
        $productAssociationType = $this->productAssociationFactory->createNew();
        $productAssociationType->setCode($associationTypeNode['code']);

        return $productAssociationType;
    }
}
