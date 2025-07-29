<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

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
    public function testAssignSetsPosition(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->once())->method('setPosition')->with(5);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::POSITION, '5');
    }

    public function testAssignSetsTranslatableFlag(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->once())->method('setTranslatable')->with(true);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::TRANSLATABLE, true);
    }

    public function testAssignSetsMultipleFlagInConfiguration(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['multiple' => true]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MULTIPLE, true);
    }

    public function testAssignSetsMaxEntries(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['max' => 10]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MAX_ENTRIES, '10');
    }

    public function testAssignSetsMaxLength(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['max' => 15]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MAX_LENGTH, '15');
    }

    public function testAssignSetsMinEntries(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['min' => 1]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MIN_ENTRIES, '1');
    }

    public function testAssignSetsMinLength(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getConfiguration')->willReturn([]);
        $attribute->expects($this->once())->method('setConfiguration')->with(['min' => 3]);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::MIN_LENGTH, '3');
    }

    public function testAssignDelegatesTypeAssignment(): void
    {
        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $typeAssigner->expects($this->once())
            ->method('assignByMetadatumValue')
            ->with($attribute, 'text');

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::TYPE, 'text');
    }

    public function testGetIntThrowsExceptionOnInvalidValue(): void
    {
        $this->expectException(AttributeSynchronizationException::class);
        $this->expectExceptionMessage('Position value must be an integer or be possible to transform to integer. Given value: `invalid`.');

        $typeAssigner = $this->createMock(AttributeTypeAssignerInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $assigner = new AttributePropertyValueAssigner($typeAssigner);
        $assigner->assign($attribute, AttributePropertyMapping::POSITION, 'invalid');
    }
}
