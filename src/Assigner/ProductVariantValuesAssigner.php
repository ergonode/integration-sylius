<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner;

use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final class ProductVariantValuesAssigner
{
    public function __construct(
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly MainLanguageContextInterface $mainLanguageContext
    ) {
    }

    public function assign(
        array $variantNode,
        IntegrationInterface $integration,
        ProductVariantInterface $productVariant,
    ): void {
        $attributeListNodes = $variantNode['attributeList'];

        foreach ($attributeListNodes['edges'] as $attributeListNode) {
            if (!array_key_exists('node', $attributeListNode)) {
                throw ProductSynchronizationException::invalidArrayStructure($attributeListNode, 'node');
            }

            $attributeNode = $attributeListNode['node'];
            [$typename, $attribute, $value] = array_values($attributeNode);
            $ergonodeAttributeCode = $attribute['code'];

            $propertyMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $ergonodeAttributeCode,
                RelationType::VARIANT_PROPERTY,
            );

            if (null === $propertyMapping) {
                $this->logMissingMapping($integration, $ergonodeAttributeCode);

                continue;
            }

            $attributeValue = $this->productVariantPropertyValueResolver->resolve(
                $integration,
                $this->mainLanguageContext->getMainLanguageMapping($integration),
                $propertyMapping,
                $typename,
                $attribute,
                $value,
            );

            $this->productVariantPropertyValueAssigner->assign(
                $integration,
                $productVariant,
                $propertyMapping,
                $attributeValue,
            );
        }
    }

    private function logMissingMapping(IntegrationInterface $integration, mixed $ergonodeAttributeCode): void
    {
        $this->logger->info(
            sprintf(
                self::MISSING_PROPERTY_LOG_FORMAT,
                $integration->getId(),
                $ergonodeAttributeCode,
                RelationType::CATEGORY_PROPERTY->value,
            ),
        );
    }
}
