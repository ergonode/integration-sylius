<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Translation;

use BitBag\SyliusErgonodePlugin\Assigner\Translation\AttributeTranslationAssigner;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Resource\Model\TranslatableInterface;

final class AttributeTranslationAssignerTest extends TestCase
{
    private FactoryInterface $attributeTranslationFactory;
    private AttributeTranslationAssigner $assigner;

    protected function setUp(): void
    {
        $this->attributeTranslationFactory = $this->createMock(FactoryInterface::class);
        $this->assigner = new AttributeTranslationAssigner($this->attributeTranslationFactory);
    }

    public function testAssignCreatesAndAddsAttributeTranslation(): void
    {
        $translatable = $this->createMock(AttributeInterface::class);
        $translation = $this->createMock(TranslationInterface::class);

        $this->attributeTranslationFactory->expects($this->once())
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
