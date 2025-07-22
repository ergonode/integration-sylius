<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\MetaKeywordsValueAssigner;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class MetaKeywordsValueAssignerTest extends TestCase
{
    private TextValueResolverInterface $textValueResolver;
    private TranslationAssignerInterface $translationAssigner;
    private MetaKeywordsValueAssigner $assigner;

    protected function setUp(): void
    {
        $this->textValueResolver = $this->createMock(TextValueResolverInterface::class);
        $this->translationAssigner = $this->createMock(TranslationAssignerInterface::class);

        $this->assigner = new MetaKeywordsValueAssigner(
            $this->textValueResolver,
            $this->translationAssigner
        );
    }

    public function testAssignMetaKeywordsToProductTranslation(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $translation = $this->createMock(ProductTranslationInterface::class);
        $values = ['en_US' => 'product,keywords,meta'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->textValueResolver->expects($this->once())
            ->method('resolve')
            ->with('en_US', 'product,keywords,meta')
            ->willReturn('product,keywords,meta');

        $translation->method('getLocale')->willReturn('en_US');

        $product->expects($this->exactly(2))
            ->method('getTranslation')
            ->with('en_US')
            ->willReturn($translation);

        $this->translationAssigner->expects($this->once())
            ->method('assign')
            ->with($product, 'en_US');

        $translation->expects($this->once())
            ->method('setMetaKeywords')
            ->with('product,keywords,meta');

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testDoNotAssignMetaKeywordsWhenLocaleDoesNotMatch(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $translation = $this->createMock(ProductTranslationInterface::class);
        $values = ['en_US' => 'product,keywords,meta'];
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);

        $this->textValueResolver->expects($this->once())
            ->method('resolve')
            ->with('en_US', 'product,keywords,meta')
            ->willReturn('product,keywords,meta');

        $translation->method('getLocale')->willReturn('fr_FR');

        $product->expects($this->once())
            ->method('getTranslation')
            ->with('en_US')
            ->willReturn($translation);

        $this->translationAssigner->expects($this->never())
            ->method('assign')
            ->with($product, 'en_US');

        $translation->expects($this->never())
            ->method('setMetaKeywords');

        $this->assigner->assign($product, $values, $mapping);
    }

    public function testSupportsProduct(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->assertTrue($this->assigner->supports(
            $product,
            ProductPropertyMapping::META_KEYWORDS->value
        ));
    }

    public function testDoesNotSupportProductWithInvalidProperty(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->assertFalse($this->assigner->supports(
            $product,
            'invalid_property'
        ));
    }

    public function testSupportsOtherResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->assertTrue($this->assigner->supports(
            $resource,
            'any_property'
        ));
    }
}
