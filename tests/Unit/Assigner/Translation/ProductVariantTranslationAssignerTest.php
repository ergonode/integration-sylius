<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Translation;

use BitBag\SyliusErgonodePlugin\Assigner\Translation\ProductVariantTranslationAssigner;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Resource\Model\TranslatableInterface;

final class ProductVariantTranslationAssignerTest extends TestCase
{
    private FactoryInterface $productVariantTranslationFactory;
    private ProductVariantTranslationAssigner $assigner;

    protected function setUp(): void
    {
        $this->productVariantTranslationFactory = $this->createMock(FactoryInterface::class);
        $this->assigner = new ProductVariantTranslationAssigner($this->productVariantTranslationFactory);
    }

    public function testAssignCreatesAndAddsProductVariantTranslation(): void
    {
        $translatable = $this->createMock(ProductVariantInterface::class);
        $translation = $this->createMock(TranslationInterface::class);

        $this->productVariantTranslationFactory->expects($this->once())
            ->method('createNew')
            ->willReturn($translation);

        $translation->expects($this->once())
            ->method('setLocale')
            ->with('en_US');

        $translatable->expects($this->once())
            ->method('addTranslation')
            ->with($translation);

        $this->assigner->assign($translatable, 'en_US');
    }

    public function testAssignThrowsExceptionWithInvalidTranslatable(): void
    {
        $translatable = $this->createMock(TranslatableInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->assigner->assign($translatable, 'en_US');
    }
}
