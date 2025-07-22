<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\SlugValueAssigner;
use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;

class SlugValueAssignerTest extends TestCase
{
    private TextValueResolverInterface $textValueResolver;
    private TranslationAssignerInterface $translationAssigner;
    private SlugValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->textValueResolver = $this->createMock(TextValueResolverInterface::class);
        $this->translationAssigner = $this->createMock(TranslationAssignerInterface::class);

        $this->assigner = new SlugValueAssigner(
            $this->textValueResolver,
            $this->translationAssigner
        );
    }

    public function testAssignSlugToProductTranslation(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $translation = $this->createMock(ProductTranslationInterface::class);
        $values = ['en_US' => 'test-product-slug'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->textValueResolver->expects($this->once())
            ->method('resolve')
            ->with('en_US', 'test-product-slug')
            ->willReturn('test-product-slug');

        $translation->method('getLocale')->willReturn('en_US');

        $product->expects($this->exactly(2))
            ->method('getTranslation')
            ->with('en_US')
            ->willReturn($translation);

        $this->translationAssigner->expects($this->once())
            ->method('assign')
            ->with($product, 'en_US');

        $translation->expects($this->once())
            ->method('setSlug')
            ->with('test-product-slug');

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testDoNotAssignSlugWhenLocaleDoesNotMatch(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $translation = $this->createMock(ProductTranslationInterface::class);
        $values = ['en_US' => 'test-product-slug'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->textValueResolver->expects($this->once())
            ->method('resolve')
            ->with('en_US', 'test-product-slug')
            ->willReturn('test-product-slug');

        $translation->method('getLocale')->willReturn('fr_FR');

        $product->expects($this->once())
            ->method('getTranslation')
            ->with('en_US')
            ->willReturn($translation);

        $this->translationAssigner->expects($this->never())
            ->method('assign')
            ->with($product, 'en_US');

        $translation->expects($this->never())
            ->method('setSlug');

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsWhenResourceIsAProduct(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);

        $this->assertTrue($this->assigner->supports($product, ProductPropertyMapping::SLUG->value));
    }

    public function testDoesNotSupportWithNonMatchingProperty(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);

        $this->assertFalse($this->assigner->supports($product, 'non_matching_property'));
    }

    public function testSupportsWhenResourceIsNotAProduct(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertTrue($this->assigner->supports($resource, 'any_property'));
    }
}
