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
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Repository\ProductAttributeRepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;

final readonly class AttributeProvider implements AttributeProviderInterface
{
    public function __construct(
        private ProductAttributeRepositoryInterface $productAttributeRepository,
        private FactoryInterface $productAttributeFactory,
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
    ) {
    }

    public function provide(IntegrationInterface $integration, string $ergonodeAttributeCode): ProductAttributeInterface
    {
        $mapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $ergonodeAttributeCode,
            RelationType::ATTRIBUTE_EXTERNAL_ID,
        );

        if (null !== $mapping) {
            $productAttribute = $this->productAttributeRepository->findOneBy(['code' => $mapping->getSyliusValue()]);

            if ($productAttribute instanceof ProductAttributeInterface) {
                return $productAttribute;
            }
        }
        $productAttribute = $this->productAttributeRepository->findOneBy(['code' => $ergonodeAttributeCode]);
        if ($productAttribute instanceof ProductAttributeInterface) {
            return $productAttribute;
        }

        /** @var ProductAttributeInterface $productAttribute */
        $productAttribute = $this->productAttributeFactory->createNew();
        $productAttribute->setCode($ergonodeAttributeCode);

        return $productAttribute;
    }
}
