<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Attribute;

use BitBag\SyliusErgonodePlugin\Assigner\Attribute\AttributePropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Attribute\AttributeTypeAssignerInterface;
use BitBag\SyliusErgonodePlugin\Enum\AttributePropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Attribute\Model\AttributeInterface;

final class AttributePropertyValueAssignerTest extends TestCase
{
    public function test_assign_sets_position(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->once())->method('setPosition')->with(5);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::POSITION, '5');
    }

    public function test_assign_sets_translatable_flag(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->once())->method('setTranslatable')->with(true);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::TRANSLATABLE, true);
    }

    public function test_assign_sets_multiple_flag_in_configuration(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['multiple' => true]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MULTIPLE, true);
    }

    public function test_assign_sets_max_entries(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['max' => 10]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MAX_ENTRIES, '10');
    }

    public function test_assign_sets_max_length(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['max' => 15]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MAX_LENGTH, '15');
    }

    public function test_assign_sets_min_entries(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['min' => 1]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MIN_ENTRIES, '1');
    }

    public function test_assign_sets_min_length(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['min' => 3]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MIN_LENGTH, '3');
    }

    public function test_assign_delegates_type_assignment(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $typeAssigner->expects($this->once())
            ->method('assignByMetadatumValue')
            ->with($attribute, 'text');

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::TYPE, 'text');
    }

    public function test_get_int_throws_exception_on_invalid_value(): void
    {
        $this->expectException(AttributeSynchronizationException::class);
        $this->expectExceptionMessage('Position value must be an integer or be possible to transform to integer. Given value: `invalid`.');

        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::POSITION, 'invalid');
    }
}
