<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Resolver\Value;

use BitBag\SyliusErgonodePlugin\Checker\StringableCheckerInterface;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class TextValueResolverTest extends TestCase
{
    public function test_returns_value_if_stringable(): void
    {
        $stringableChecker = $this->createMock(StringableCheckerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $stringableChecker->method('check')->with('Hello')->willReturn(true);
        $logger->expects(self::never())->method('warning');

        $resolver = new TextValueResolver($stringableChecker, $logger);

        $result = $resolver->resolve('en_US', 'Hello');
        $this->assertSame('Hello', $result);
    }

    public function test_returns_null_and_logs_if_not_stringable(): void
    {
        $stringableChecker = $this->createMock(StringableCheckerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $stringableChecker->method('check')->with(['bad' => 'array'])->willReturn(false);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with($this->stringContains('Value for locale en_US is not stringable'));

        $resolver = new TextValueResolver($stringableChecker, $logger);

        $result = $resolver->resolve('en_US', ['bad' => 'array']);
        $this->assertNull($result);
    }
}
