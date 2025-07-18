<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Product\StorageBasedProductAttributeValueAssigner;
use BitBag\SyliusErgonodePlugin\Resolver\FloatValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;

final class StorageBasedProductAttributeValueAssignerTest extends TestCase
{
    public function test_assign_handles_text_type(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $value = $this->createMock(ProductAttributeValueInterface::class);
        $value->method('getAttribute')->willReturn($attribute);
        $value->method('getLocaleCode')->willReturn('en_US');

        $attribute->method('getStorageType')->willReturn(ProductAttributeValueInterface::STORAGE_TEXT);

        $floatResolver = $this->createMock(FloatValueResolverInterface::class);
        $textResolver = $this->createMock(TextValueResolverInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $textResolver->expects($this->once())
            ->method('resolve')
            ->with('en_US', 'test value')
            ->willReturn('test value');

        $value->expects($this->once())->method('setValue')->with('test value');

        $assigner = new StorageBasedProductAttributeValueAssigner($floatResolver, $textResolver, $logger);
        $assigner->assign($value, 'test value');
    }

    public function test_assign_logs_error_on_invalid_attribute(): void
    {
        $value = $this->createMock(ProductAttributeValueInterface::class);
        $value->method('getAttribute')->willReturn(null);

        $floatResolver = $this->createMock(FloatValueResolverInterface::class);
        $textResolver = $this->createMock(TextValueResolverInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())->method('error');

        $assigner = new StorageBasedProductAttributeValueAssigner($floatResolver, $textResolver, $logger);
        $assigner->assign($value, 'bad');
    }
}
