<?php

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
