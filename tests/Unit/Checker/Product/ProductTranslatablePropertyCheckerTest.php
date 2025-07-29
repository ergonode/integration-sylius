<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Checker\Product;

use BitBag\SyliusErgonodePlugin\Checker\Product\ProductTranslatablePropertyChecker;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use PHPUnit\Framework\TestCase;

final class ProductTranslatablePropertyCheckerTest extends TestCase
{
    private ProductTranslatablePropertyChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ProductTranslatablePropertyChecker();
    }

    /**
     * @dataProvider provideTranslatableProperties
     */
    public function testReturnsTrueForTranslatableProperty(ProductPropertyMapping $property, bool $expected): void
    {
        $result = $this->checker->check($property);

        $this->assertSame($expected, $result);
    }

    public static function provideTranslatableProperties(): array
    {
        return [
            'name property' => [ProductPropertyMapping::NAME, true],
            'description property' => [ProductPropertyMapping::DESCRIPTION, true],
            'slug property' => [ProductPropertyMapping::SLUG, true],
            'short_description property' => [ProductPropertyMapping::SHORT_DESCRIPTION, true],
            'meta_keywords property' => [ProductPropertyMapping::META_KEYWORDS, true],
            'meta_description property' => [ProductPropertyMapping::META_DESCRIPTION, true],
            'code property' => [ProductPropertyMapping::ORIGINAL_PRICE, false],
            'main_taxon property' => [ProductPropertyMapping::MAIN_TAXON, false],
            'channels property' => [ProductPropertyMapping::CHANNELS, false],
        ];
    }
}
