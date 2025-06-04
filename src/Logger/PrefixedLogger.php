<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Logger;

use Psr\Log\LoggerInterface;

final class PrefixedLogger implements LoggerInterface
{
    private const FORMAT = '[BitBag/SyliusErgonodePlugin] %s';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }


    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->logger->emergency(sprintf(self::FORMAT, $message), $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->logger->alert(sprintf(self::FORMAT, $message), $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->logger->critical(sprintf(self::FORMAT, $message), $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->logger->error(sprintf(self::FORMAT, $message), $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->logger->warning(sprintf(self::FORMAT, $message), $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->logger->notice(sprintf(self::FORMAT, $message), $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->logger->info(sprintf(self::FORMAT, $message), $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->logger->debug(sprintf(self::FORMAT, $message), $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, sprintf(self::FORMAT, $message), $context);
    }
}
