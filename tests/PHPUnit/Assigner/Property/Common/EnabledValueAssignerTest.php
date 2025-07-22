<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\PHPUnit\Assigner\Property\Common;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Common\EnabledValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\ToggleableInterface;

class EnabledValueAssignerTest extends TestCase
{
    private EnabledValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->assigner = new EnabledValueAssigner();
    }

    public function testAssignEnabledValue(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->expects($this->once())
            ->method('setEnabled')
            ->with(true);

        $values = [true];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->assigner->assign($resource, $values, $mapping);
    }

    public function testAssignDisabledValue(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->expects($this->once())
            ->method('setEnabled')
            ->with(false);

        $values = [false];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->assigner->assign($resource, $values, $mapping);
    }

    public function testAssignBooleanCastValue(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->expects($this->once())
            ->method('setEnabled')
            ->with(true);

        $values = ['1'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->assigner->assign($resource, $values, $mapping);
    }

    public function testSupportsToggleableResourceAndEnabledProperty(): void
    {
        $resource = $this->createMock(ToggleableInterface::class);

        $this->assertTrue($this->assigner->supports($resource, 'enabled'));
    }

    public function testDoesNotSupportNonToggleableResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertFalse($this->assigner->supports($resource, 'enabled'));
    }

    public function testDoesNotSupportToggleableResourceWithOtherProperty(): void
    {
        $resource = $this->createMock(ToggleableInterface::class);

        $this->assertFalse($this->assigner->supports($resource, 'other_property'));
    }
}
