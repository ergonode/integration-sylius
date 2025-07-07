<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner;

use BitBag\SyliusErgonodePlugin\API\Client\CategoryApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Taxon\TaxonNameAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Taxon\TaxonPropertyValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\CategorySynchronizationException;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\CategoryNode;
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final readonly class TaxonValuesAssigner implements TaxonValuesAssignerInterface
{
    private const MISSING_PROPERTY_LOG_FORMAT = 'Missing property relation mapping for integration id `%s`, relation type %s and value %s';

    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private TaxonPropertyValueAssignerInterface $taxonPropertyValueAssigner,
        private TaxonNameAssignerInterface $taxonNameAssigner,
        private MainLanguageContextInterface $mainLanguageContext,
        private CategoryAttributeValueResolverInterface $categoryAttributeValueResolver,
        private CategoryApiClientInterface $apiClient,
        private LoggerInterface $logger,
    ) {
    }

    public function assign(
        CategoryNode $categoryNode,
        IntegrationInterface $integration,
        TaxonInterface $taxon,
    ): void {
        $code = $categoryNode->getCode();
        $attributeListNodes = $categoryNode->getAttributeListNodes();
        $this->taxonNameAssigner->assign($integration, $taxon, $categoryNode);
        $pageInfo = $categoryNode->getPageInfo();
        $this->assignValues($attributeListNodes, $integration, $taxon, $pageInfo, $code);
    }

    private function logMissingMapping(IntegrationInterface $integration, mixed $ergonodeAttributeCode): void
    {
        $this->logger->warning(
            sprintf(
                self::MISSING_PROPERTY_LOG_FORMAT,
                $integration->getId(),
                $ergonodeAttributeCode,
                RelationType::CATEGORY_PROPERTY->value,
            ),
        );
    }

    public function assignValues(
        array $attributeListNodes,
        IntegrationInterface $integration,
        TaxonInterface $taxon,
        array $pageInfo,
        string $code
    ): void {
        foreach ($attributeListNodes as $attributeListNode) {
            if (!array_key_exists('node', $attributeListNode)) {
                throw CategorySynchronizationException::invalidArrayStructure($attributeListNode, 'node');
            }

            $attributeNode = $attributeListNode['node'];
            [$typename, $attribute, $value] = array_values($attributeNode);
            $ergonodeAttributeCode = $attribute['code'];

            $propertyMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $ergonodeAttributeCode,
                RelationType::CATEGORY_PROPERTY,
            );

            if (null === $propertyMapping) {
                $this->logMissingMapping($integration, $ergonodeAttributeCode);

                continue;
            }

            $attributeValue = $this->categoryAttributeValueResolver->resolve(
                $integration,
                $this->mainLanguageContext->getMainLanguageMapping($integration),
                $propertyMapping,
                $typename,
                $attribute,
                $value,
            );

            $this->taxonPropertyValueAssigner->assign(
                $integration,
                $taxon,
                $propertyMapping,
                $attributeValue,
            );
        }

        if ($pageInfo['hasNextPage']) {
            $cursorEnd = $pageInfo['endCursor'];
            $response = $this->apiClient->getCategoryAttributes($integration, $code, $cursorEnd);
            dd($response);
        }
    }
}
