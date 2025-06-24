<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Attribute;

use BitBag\SyliusErgonodePlugin\Enum\AttributeType;
use BitBag\SyliusErgonodePlugin\Resolver\Attribute\AttributeTypeResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Attribute\AttributeType\AttributeTypeInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

final readonly class AttributeTypeAssigner implements AttributeTypeAssignerInterface
{
    public function __construct(
        private AttributeTypeResolverInterface $attributeTypeResolver,
        private ServiceRegistryInterface $attributeTypeRegistry,
        private LoggerInterface $logger,
    ) {
    }

    public function assignByEnum(
        AttributeInterface $attribute,
        AttributeType $type,
    ): void {
        $resolvedType = $this->attributeTypeResolver->resolveType($type);
        $attribute->setType($resolvedType);

        /** @var AttributeTypeInterface $attributeType */
        $attributeType = $this->attributeTypeRegistry->get($resolvedType);

        $attribute->setStorageType($attributeType->getStorageType());
    }

    public function assignByMetadatumValue(
        AttributeInterface $attribute,
        string $type,
    ): void {
        if (!$this->attributeTypeRegistry->has($type)) {
            $this->logger->error(
                sprintf(
                    'Attribute type `%s` is not supported. Leaving type `%s` for attribute with code `%s`',
                    $type,
                    $attribute->getType(),
                    $attribute->getCode(),
                ),
            );
        }

        /** @var AttributeTypeInterface $attributeType */
        $attributeType = $this->attributeTypeRegistry->get($type);

        $attribute->setType($type);
        $attribute->setStorageType($attributeType->getStorageType());
    }
}
