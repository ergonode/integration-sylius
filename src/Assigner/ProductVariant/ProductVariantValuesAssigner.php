<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\ProductVariant;

use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\ProductSynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class ProductVariantValuesAssigner implements ProductVariantValuesAssignerInterface
{
    private const MISSING_PROPERTY_LOG_FORMAT = 'Missing property relation mapping for integration id `%s`, relation type %s and value %s';

    private const UNRESOLVED_PROPERTY = 'Failed to resolve property with property relation mapping  id `%s and attribute code %s`';

    public function __construct(
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly MainLanguageContextInterface $mainLanguageContext,
        private readonly ProductVariantAttributeValueResolverInterface $productVariantAttributeValueResolver,
        private readonly ProductVariantPropertyValueAssignerInterface $productVariantPropertyValueAssigner,
        private readonly ProductApiClientInterface $apiClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function assign(
        array $variantNode,
        IntegrationInterface $integration,
        ProductVariantInterface $productVariant,
    ): void {
        $sku = $variantNode['sku'];
        $attributeListNodes = $variantNode['attributeList'];
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
                RelationType::VARIANT_PROPERTY,
            );
            if (null === $propertyMapping || null === $syliusValue = $propertyMapping->getSyliusValue()) {
                $this->logMissingMapping($integration, $ergonodeAttributeCode);

                continue;
            }

            $attributeValue = $this->productVariantAttributeValueResolver->resolve(
                $integration,
                $this->mainLanguageContext->getMainLanguageMapping($integration),
                $typename,
                $attribute,
                $value,
                $propertyMapping,
            );

            $property = ProductVariantPropertyMapping::tryFrom($syliusValue);
            if (null === $property) {
                $this->logUnresolvedProperty($propertyMapping, $ergonodeAttributeCode);

                continue;
            }

            $this->productVariantPropertyValueAssigner->assign(
                $productVariant,
                $property,
                $propertyMapping,
                $attributeValue,
            );
        }

        if ($pageInfo['hasNextPage']) {
            $productNode = $this->apiClient->getProductAttributes($integration, $sku, $pageInfo['endCursor']);
            $this->assign($productNode, $integration, $productVariant);
        }
    }

    private function logMissingMapping(IntegrationInterface $integration, mixed $ergonodeAttributeCode): void
    {
        $this->logger->info(
            sprintf(
                self::MISSING_PROPERTY_LOG_FORMAT,
                $integration->getId(),
                $ergonodeAttributeCode,
                RelationType::VARIANT_PROPERTY->value,
            ),
        );
    }

    private function logUnresolvedProperty(ResourceRelationMappingInterface $resourceRelationMapping, mixed $ergonodeAttributeCode): void
    {
        $this->logger->info(
            sprintf(
                self::UNRESOLVED_PROPERTY,
                $resourceRelationMapping->getId(),
                $ergonodeAttributeCode,
            ),
        );
    }
}
