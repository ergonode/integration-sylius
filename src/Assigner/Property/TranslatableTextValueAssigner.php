<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property;

use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

abstract class TranslatableTextValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        protected TextValueResolverInterface $textValueResolver,
    ) {
    }

    protected function assignValue(ResourceInterface $resource, array $values, callable $callback): void
    {
        if (!$resource instanceof ProductInterface) {
            return;
        }

        foreach ($values as $locale => $value) {
            $value = $this->textValueResolver->resolve($locale, $value);
            if (null === $value) {
                continue;
            }

            $callback($resource, $value, $locale);
        }
    }
}
