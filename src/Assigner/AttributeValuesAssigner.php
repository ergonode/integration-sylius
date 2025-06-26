<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner;

use BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Attribute\AttributeNameAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Attribute\AttributeOptionsAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Attribute\AttributePropertyValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Attribute\AttributeTypeAssignerInterface;
use BitBag\SyliusErgonodePlugin\Context\SynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributePropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\AttributeType;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Attribute\AttributeType\DateAttributeType;
use Sylius\Component\Attribute\AttributeType\DatetimeAttributeType;
use Sylius\Component\Attribute\AttributeType\SelectAttributeType;
use Sylius\Component\Attribute\Model\AttributeInterface;

final readonly class AttributeValuesAssigner implements AttributeValuesAssignerInterface
{
    private const MISSING_PROPERTY_LOG_FORMAT = 'Missing property relation mapping for integration id `%s`, relation type %s and value %s';

    public function __construct(
        private AttributeNameAssignerInterface $attributeNameAssigner,
        private AttributeTypeAssignerInterface $attributeTypeAssigner,
        private AttributeOptionsAssignerInterface $attributeOptionsAssigner,
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private AttributePropertyValueAssignerInterface $attributePropertyValueAssigner,
        private SynchronizationContextInterface $synchronizationContext,
        private LoggerInterface $logger,
    ) {
    }

    public function assign(IntegrationInterface $integration, AttributeInterface $attribute, array $attributeNode): void
    {
        $typename = $attributeNode['typename'];
        $ergonodeAttributeCode = $attributeNode['code'];
        $this->attributeNameAssigner->assign($integration, $attribute, $attributeNode['name']);

        $typeEnum = AttributeType::tryFrom($typename);
        if (null === $typeEnum) {
            throw AttributeSynchronizationException::notSupportedType($typename);
        }

        $this->attributeTypeAssigner->assignByEnum($attribute, $typeEnum);

        foreach ($attributeNode['metadata'] as $metadatum) {
            $propertyMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $metadatum['key'],
                RelationType::ATTRIBUTE_PROPERTY,
            );

            $mappingFound = null !== $propertyMapping;
            $isFullSynchronization = $this->synchronizationContext->isFullSynchronization();

            if ($mappingFound && !$isFullSynchronization && $propertyMapping->isImportOnce()) {
                continue;
            }

            $possibleProperty = AttributePropertyMapping::tryFrom(!$mappingFound
                ? $metadatum['key']
                : $propertyMapping->getSyliusValue())
            ;

            if (null === $possibleProperty) {
                $this->logMissingMapping($integration, $metadatum['key']);

                continue;
            }

            $this->attributePropertyValueAssigner->assign($attribute, $possibleProperty, $metadatum['value']);
        }
        if (SelectAttributeType::TYPE === $attribute->getType() && array_key_exists('optionList', $attributeNode)) {
            $this->attributeOptionsAssigner->assign($integration, $attribute, $ergonodeAttributeCode, $attributeNode['optionList'] ?? []);
        }

        if (DateAttributeType::TYPE === $attribute->getType() || DatetimeAttributeType::TYPE === $attribute->getType()) {
            $configuration = $attribute->getConfiguration();
            $configuration['format'] = $attributeNode['format'];
            $attribute->setConfiguration($configuration);
        }
    }

    private function logMissingMapping(IntegrationInterface $integration, mixed $ergonodeAttributeCode): void
    {
        $this->logger->warning(
            sprintf(
                self::MISSING_PROPERTY_LOG_FORMAT,
                $integration->getId(),
                RelationType::ATTRIBUTE_PROPERTY->value,
                $ergonodeAttributeCode,
            ),
        );
    }
}
