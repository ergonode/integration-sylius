<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Checker\Variant;

use BitBag\SyliusErgonodePlugin\Checker\Variant\ProductVariantTranslatablePropertyChecker;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use PHPUnit\Framework\TestCase;

final class ProductVariantTranslatablePropertyCheckerTest extends TestCase
{
    private ProductVariantTranslatablePropertyChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ProductVariantTranslatablePropertyChecker();
    }

    /**
     * @dataProvider provideTranslatableProperties
     */
    public function testReturnsTrueForTranslatableProperty(ProductVariantPropertyMapping $property, bool $expected): void
    {
        $result = $this->checker->check($property);

        $this->assertSame($expected, $result);
    }

    public static function provideTranslatableProperties(): array
    {
        return [
            'name property' => [ProductVariantPropertyMapping::NAME, true],
            'width property' => [ProductVariantPropertyMapping::WIDTH, false],
            'height property' => [ProductVariantPropertyMapping::HEIGHT, false],
            'depth property' => [ProductVariantPropertyMapping::DEPTH, false],
            'weight property' => [ProductVariantPropertyMapping::WEIGHT, false],
        ];
    }
}
