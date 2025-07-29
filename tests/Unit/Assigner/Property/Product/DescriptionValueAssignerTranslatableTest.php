<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\DescriptionValueAssignerTranslatable;
use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;

class DescriptionValueAssignerTranslatableTest extends TestCase
{
    private TextValueResolverInterface $textValueResolver;

    private TranslationAssignerInterface $translationAssigner;

    private DescriptionValueAssignerTranslatable $assigner;

    protected function setUp(): void
    {
        $this->textValueResolver = $this->createMock(TextValueResolverInterface::class);
        $this->translationAssigner = $this->createMock(TranslationAssignerInterface::class);

        $this->assigner = new DescriptionValueAssignerTranslatable(
            $this->textValueResolver,
            $this->translationAssigner,
        );
    }

    public function testAssignDescriptionToProductTranslation(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $translation = $this->createMock(ProductTranslationInterface::class);
        $values = ['en_US' => 'Detailed product description with HTML formatting'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->textValueResolver->expects($this->once())
            ->method('resolve')
            ->with('en_US', 'Detailed product description with HTML formatting')
            ->willReturn('Detailed product description with HTML formatting');

        $translation->method('getLocale')->willReturn('en_US');

        $product->expects($this->exactly(2))
            ->method('getTranslation')
            ->with('en_US')
            ->willReturn($translation);

        $this->translationAssigner->expects($this->once())
            ->method('assign')
            ->with($product, 'en_US');

        $translation->expects($this->once())
            ->method('setDescription')
            ->with('Detailed product description with HTML formatting');

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsProduct(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $product,
            ProductPropertyMapping::DESCRIPTION->value,
        ));
    }

    public function testDoesNotSupportProductWithInvalidProperty(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);

        $this->assertFalse($this->assigner->supports(
            $product,
            'invalid_property',
        ));
    }

    public function testSupportsOtherResource(): void
    {
        $resource = $this->createMock(ProductResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $resource,
            'description',
        ));
    }
}
