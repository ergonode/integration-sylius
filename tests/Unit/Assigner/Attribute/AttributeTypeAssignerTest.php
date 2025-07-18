<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Attribute;

use BitBag\SyliusErgonodePlugin\Assigner\Attribute\AttributeTypeAssigner;
use BitBag\SyliusErgonodePlugin\Enum\AttributeType;
use BitBag\SyliusErgonodePlugin\Resolver\Attribute\AttributeTypeResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Attribute\AttributeType\AttributeTypeInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class AttributeTypeAssignerTest extends TestCase
{
    public function test_assign_by_enum_sets_type_and_storage_type(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $resolver = $this->createMock(AttributeTypeResolverInterface::class);
        $registry = $this->createMock(ServiceRegistryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $attributeType = $this->createMock(AttributeTypeInterface::class);

        $resolver->expects($this->once())
            ->method('resolveType')
            ->with(AttributeType::TEXT)
            ->willReturn('text');

        $registry->expects($this->once())
            ->method('get')
            ->with('text')
            ->willReturn($attributeType);

        $attributeType->expects($this->once())
            ->method('getStorageType')
            ->willReturn('string');

        $attribute->expects($this->once())->method('setType')->with('text');
        $attribute->expects($this->once())->method('setStorageType')->with('string');

        $assigner = new AttributeTypeAssigner($resolver, $registry, $logger);
        $assigner->assignByEnum($attribute, AttributeType::TEXT);
    }

    public function test_assign_by_metadatum_value_sets_type_and_storage_type_when_type_exists(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $resolver = $this->createMock(AttributeTypeResolverInterface::class);
        $registry = $this->createMock(ServiceRegistryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $attributeType = $this->createMock(AttributeTypeInterface::class);

        $registry->expects($this->once())
            ->method('has')
            ->with('text')
            ->willReturn(true);

        $registry->expects($this->once())
            ->method('get')
            ->with('text')
            ->willReturn($attributeType);

        $attributeType->expects($this->once())
            ->method('getStorageType')
            ->willReturn('string');

        $attribute->expects($this->once())->method('setType')->with('text');
        $attribute->expects($this->once())->method('setStorageType')->with('string');

        $assigner = new AttributeTypeAssigner($resolver, $registry, $logger);
        $assigner->assignByMetadatumValue($attribute, 'text');
    }

    public function test_assign_by_metadatum_value_logs_when_type_is_not_registered(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $resolver = $this->createMock(AttributeTypeResolverInterface::class);
        $registry = $this->createMock(ServiceRegistryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $attribute->method('getType')->willReturn('existing');
        $attribute->method('getCode')->willReturn('material');

        $registry->expects($this->once())
            ->method('has')
            ->with('non_existing')
            ->willReturn(false);

        $logger->expects($this->once())
            ->method('error')
            ->with('Attribute type `non_existing` is not supported. Leaving type `existing` for attribute with code `material`');

        $registry->method('get')
            ->with('non_existing')
            ->willReturn($this->createMock(AttributeTypeInterface::class));

        $attribute->expects($this->once())->method('setType')->with('non_existing');
        $attribute->expects($this->once())->method('setStorageType');

        $assigner = new AttributeTypeAssigner($resolver, $registry, $logger);
        $assigner->assignByMetadatumValue($attribute, 'non_existing');
    }
}
