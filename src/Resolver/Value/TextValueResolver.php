<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver\Value;

use BitBag\SyliusErgonodePlugin\Checker\StringableCheckerInterface;
use Psr\Log\LoggerInterface;

final readonly class TextValueResolver implements TextValueResolverInterface
{
    public function __construct(
        private StringableCheckerInterface $stringableChecker,
        private LoggerInterface $logger,
    ) {
    }

    public function resolve(string $locale, mixed $value): ?string
    {
        if (!$this->stringableChecker->check($value)) {
            $this->logger->warning(sprintf(
                'Value for locale %s is not stringable. Value: %s',
                $locale,
                json_encode($value),
            ));

            return null;
        }

        return $value;
    }
}
