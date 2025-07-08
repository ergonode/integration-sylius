<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributeValueType;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ProductAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use BitBag\SyliusErgonodePlugin\Storage\ProductsRelationsStorageInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class ProductValuesAssigner implements ProductValuesAssignerInterface
{
    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private ProductPropertyValueAssignerInterface $productPropertyValueAssigner,
        private MainLanguageContextInterface $mainLanguageContext,
        private ProductAttributeValueResolverInterface $productAttributeValueResolver,
        private ProductAttributeValueAssignerInterface $productAttributeValueAssigner,
        private ProductsRelationsStorageInterface $productsRelationsStorage,
        private ProductApiClientInterface $apiClient,
    ) {
    }

    public function assign(
        array $productNode,
        IntegrationInterface $integration,
        ProductInterface $product,
    ): void {
        $sku = $productNode['sku'];
        $attributeListNodes = $productNode['attributeList'];
        $pageInfo = $attributeListNodes['pageInfo'];
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
                RelationType::PRODUCT_PROPERTY,
            );

            $attributeValue = $this->productAttributeValueResolver->resolve(
                $integration,
                $this->mainLanguageContext->getMainLanguageMapping($integration),
                $typename,
                $attribute,
                $value,
                $propertyMapping,
            );

            $property = $propertyMapping?->getSyliusValue();
            if (null !== $property) {
                $property = ProductPropertyMapping::tryFrom($property);
            }

            $syliusAttributeCode = null === $property ? $ergonodeAttributeCode : $property->value;

            if ($typename === AttributeValueType::PRODUCT_RELATION->value) {
                $this->productsRelationsStorage->storeAssociation($product, $ergonodeAttributeCode, $attributeValue);
            }

            if (null === $propertyMapping || null === $property) {
                $this->productAttributeValueAssigner->assign($product, $attributeValue, $syliusAttributeCode);

                continue;
            }

            $this->productPropertyValueAssigner->assign(
                $product,
                $property,
                $propertyMapping,
                $attributeValue,
            );
        }

        if ($pageInfo['hasNextPage']) {
            $productNode = $this->apiClient->getProductAttributes($integration, $sku, $pageInfo['endCursor']);
            $this->assign($productNode, $integration, $product);
        }
    }
}
