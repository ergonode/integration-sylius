<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductAttributeValueAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductAttributeValueAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\StorageBasedProductAttributeValueAssignerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Repository\ProductAttributeValueRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductAttributeValueAssignerTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private ProductAttributeValueRepositoryInterface $valueRepository;

    private RepositoryInterface $attributeRepository;

    private FactoryInterface $factory;

    private StorageBasedProductAttributeValueAssignerInterface $resolver;

    private ProductAttributeValueAssignerInterface $assigner;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->valueRepository = $this->createMock(ProductAttributeValueRepositoryInterface::class);
        $this->attributeRepository = $this->createMock(RepositoryInterface::class);
        $this->factory = $this->createMock(FactoryInterface::class);
        $this->resolver = $this->createMock(StorageBasedProductAttributeValueAssignerInterface::class);

        $this->assigner = new ProductAttributeValueAssigner(
            $this->valueRepository,
            $this->attributeRepository,
            $this->factory,
            $this->resolver,
            $this->entityManager,
        );
    }

    public function testAssignCreatesAndAssignsAttributeValueIfNotExists(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $attributeValue = $this->createMock(ProductAttributeValueInterface::class);
        $product = $this->createMock(ProductInterface::class);

        $this->attributeRepository->method('findOneBy')->with(['code' => 'color'])->willReturn($attribute);
        $this->valueRepository->method('findOneBy')->willReturn(null);
        $this->factory->method('createNew')->willReturn($attributeValue);

        $attributeValue->expects($this->once())->method('setAttribute')->with($attribute);
        $attributeValue->expects($this->once())->method('setLocaleCode')->with('en_US');
        $attributeValue->expects($this->once())->method('setProduct')->with($product);

        $this->resolver->expects($this->once())->method('assign')->with($attributeValue, 'Red');

        $this->assigner->assign($product, ['en_US' => 'Red'], 'color');
    }

    public function testAssignSkipsIfAttributeNotFound(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $this->attributeRepository->expects($this->once())->method('findOneBy')->with(['code' => 'color'])->willReturn(null);
        $this->resolver->expects($this->never())->method('assign');
        $this->assigner->assign($product, ['en_US' => 'Red'], 'color');
    }

    public function testAssignUsesExistingValueIfFound(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $attributeValue = $this->createMock(ProductAttributeValueInterface::class);
        $attributeValue->method('getProduct')->willReturn($product);

        $this->attributeRepository->method('findOneBy')->with(['code' => 'color'])->willReturn($attribute);
        $this->valueRepository->method('findOneBy')->willReturn($attributeValue);

        $attributeValue->expects($this->never())->method('setAttribute');
        $this->resolver->expects($this->once())->method('assign')->with($attributeValue, 'Red');

        $this->assigner->assign($attributeValue->getProduct(), ['en_US' => 'Red'], 'color');
    }
}
