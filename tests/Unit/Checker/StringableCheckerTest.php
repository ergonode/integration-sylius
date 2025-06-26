<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Checker;

use BitBag\SyliusErgonodePlugin\Checker\StringableChecker;
use PHPUnit\Framework\TestCase;

final class StringableCheckerTest extends TestCase
{
    private StringableChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new StringableChecker();
    }

    public function test_returns_true_for_null(): void
    {
        $this->assertTrue($this->checker->check(null));
    }

    public function test_returns_true_for_scalar_types(): void
    {
        $this->assertTrue($this->checker->check(42));
        $this->assertTrue($this->checker->check(3.14));
        $this->assertTrue($this->checker->check('text'));
        $this->assertTrue($this->checker->check(true));
    }

    public function test_returns_true_for_stringable_object(): void
    {
        $stringable = new class() implements \Stringable {
            public function __toString(): string
            {
                return 'stringable';
            }
        };

        $this->assertTrue($this->checker->check($stringable));
    }

    public function test_returns_true_for_object_with___toString(): void
    {
        $withToString = new class() {
            public function __toString(): string
            {
                return 'toString';
            }
        };

        $this->assertTrue($this->checker->check($withToString));
    }

    public function test_returns_false_for_non_stringable_object(): void
    {
        $nonStringable = new class() {
        };

        $this->assertFalse($this->checker->check($nonStringable));
    }

    public function test_returns_false_for_array(): void
    {
        $this->assertFalse($this->checker->check(['not', 'stringable']));
    }
}
