<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Translation;

use BitBag\SyliusErgonodePlugin\Assigner\Translation\ProductTranslationAssigner;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Resource\Model\TranslatableInterface;

final class ProductTranslationAssignerTest extends TestCase
{
    private FactoryInterface $productTranslationFactory;

    private ProductTranslationAssigner $assigner;

    protected function setUp(): void
    {
        $this->productTranslationFactory = $this->createMock(FactoryInterface::class);
        $this->assigner = new ProductTranslationAssigner($this->productTranslationFactory);
    }

    public function testAssignCreatesAndAddsProductTranslation(): void
    {
        $translatable = $this->createMock(ProductInterface::class);
        $translation = $this->createMock(TranslationInterface::class);

        $this->productTranslationFactory->expects($this->once())
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
