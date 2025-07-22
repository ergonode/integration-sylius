<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductAttributeValueAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Product\StorageBasedProductAttributeValueAssignerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductAttributeValueAssignerTest extends TestCase
{
    public function testAssignCreatesAndAssignsAttributeValueIfNotExists(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $attributeValue = $this->createMock(ProductAttributeValueInterface::class);

        $attributeRepository = $this->createMock(RepositoryInterface::class);
        $attributeRepository->method('findOneBy')->with(['code' => 'color'])->willReturn($attribute);

        $valueRepository = $this->createMock(RepositoryInterface::class);
        $valueRepository->method('findOneBy')->willReturn(null);

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createNew')->willReturn($attributeValue);

        $resolver = $this->createMock(StorageBasedProductAttributeValueAssignerInterface::class);

        $attributeValue->expects($this->once())->method('setAttribute')->with($attribute);
        $attributeValue->expects($this->once())->method('setLocaleCode')->with('en_US');
        $attributeValue->expects($this->once())->method('setProduct')->with($product);

        $resolver->expects($this->once())->method('assign')->with($attributeValue, 'Red');

        $assigner = new ProductAttributeValueAssigner($valueRepository, $attributeRepository, $factory, $resolver);
        $assigner->assign($product, ['en_US' => 'Red'], 'color');
    }

    public function testAssignSkipsIfAttributeNotFound(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $attributeRepository = $this->createMock(RepositoryInterface::class);
        $attributeRepository->method('findOneBy')->with(['code' => 'color'])->willReturn(null);

        $valueRepository = $this->createMock(RepositoryInterface::class);
        $factory = $this->createMock(FactoryInterface::class);
        $resolver = $this->createMock(StorageBasedProductAttributeValueAssignerInterface::class);

        $resolver->expects($this->never())->method('assign');

        $assigner = new ProductAttributeValueAssigner($valueRepository, $attributeRepository, $factory, $resolver);
        $assigner->assign($product, ['en_US' => 'Red'], 'color');
    }

    public function testAssignUsesExistingValueIfFound(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $attributeValue = $this->createMock(ProductAttributeValueInterface::class);

        $attributeRepository = $this->createMock(RepositoryInterface::class);
        $attributeRepository->method('findOneBy')->with(['code' => 'color'])->willReturn($attribute);

        $valueRepository = $this->createMock(RepositoryInterface::class);
        $valueRepository->method('findOneBy')->willReturn($attributeValue);

        $factory = $this->createMock(FactoryInterface::class);
        $resolver = $this->createMock(StorageBasedProductAttributeValueAssignerInterface::class);

        $attributeValue->expects($this->never())->method('setAttribute');

        $resolver->expects($this->once())->method('assign')->with($attributeValue, 'Red');

        $assigner = new ProductAttributeValueAssigner($valueRepository, $attributeRepository, $factory, $resolver);
        $assigner->assign($product, ['en_US' => 'Red'], 'color');
    }
}
