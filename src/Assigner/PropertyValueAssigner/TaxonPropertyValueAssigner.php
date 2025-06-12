<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner;

use BitBag\SyliusErgonodePlugin\Context\MainLanguageContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\CategoryAttributeValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final readonly class TaxonPropertyValueAssigner implements TaxonPropertyValueAssignerInterface
{
    private const MISSING_PROPERTY_LOG_FORMAT = 'Missing property relation mapping for integration id `%s`, relation type %s and value %s';

    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private MainLanguageContextInterface $mainLanguageContext,
        private CategoryAttributeValueResolverInterface $categoryAttributeValueResolver,
        private LoggerInterface $logger,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        TaxonInterface $taxon,
        array $attributeNode,
    ): void {
        $attribute = $attributeNode['attribute'];
        $ergonodeAttributeCode = $attribute['code'];
        $ergonodeValue = $attributeNode['value'];
        $typename = $attributeNode['typename'];

        $propertyMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
            $integration,
            $ergonodeAttributeCode,
            RelationType::ATTRIBUTE_PROPERTY,
        );

        if (null === $propertyMapping) {
            $this->logger->warning(
                sprintf(
                    self::MISSING_PROPERTY_LOG_FORMAT,
                    $integration->getId(),
                    $ergonodeAttributeCode,
                    RelationType::ATTRIBUTE_PROPERTY->value,
                ),
            );

            return;
        }

        $ergonodeLanguageMapping = $this->mainLanguageContext->getMainLanguageMapping($integration);

        $attributeValue = $this->categoryAttributeValueResolver->resolve(
            $ergonodeLanguageMapping,
            $typename,
            $ergonodeValue,
        );
    }
}
