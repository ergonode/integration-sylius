<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property;

use BitBag\SyliusErgonodePlugin\Resolver\FloatValueResolverInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

abstract class FloatValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly FloatValueResolverInterface $resolver,
    ) {
    }

    protected function assignValue(ResourceInterface $resource, array $values, callable $callable): void
    {
        $value = array_shift($values);

        if (null === $value = $this->resolver->resolve($value)) {
            return;
        }

        $callable($resource, $value);
    }
}
