<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Checker\Category;

use BitBag\SyliusErgonodePlugin\Checker\Category\CategoryTranslatablePropertyChecker;
use BitBag\SyliusErgonodePlugin\Enum\CategoryPropertyMapping;
use PHPUnit\Framework\TestCase;

final class CategoryTranslatablePropertyCheckerTest extends TestCase
{
    private CategoryTranslatablePropertyChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new CategoryTranslatablePropertyChecker();
    }

    /**
     * @dataProvider provideTranslatableProperties
     */
    public function testReturnsTrueForTranslatableProperty(?CategoryPropertyMapping $property, bool $expected): void
    {
        $result = $this->checker->check($property);

        $this->assertSame($expected, $result);
    }

    public static function provideTranslatableProperties(): array
    {
        return [
            'description property' => [CategoryPropertyMapping::DESCRIPTION, true],
            'slug property' => [CategoryPropertyMapping::SLUG, true],
            'code property' => [CategoryPropertyMapping::MEDIA, false],
            'null property' => [null, false],
        ];
    }
}
