<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property\Common;

use BitBag\SyliusErgonodePlugin\Assigner\Property\BooleanValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\ToggleableInterface;

final class EnabledValueAssigner extends BooleanValueAssigner
{
    private const ENABLED = 'enabled';

    public function assign(ResourceInterface $resource, array $values, ResourceRelationMappingInterface $mapping): void
    {
        $this->assignValue($resource, $values, function (ToggleableInterface $resource, bool $value) {
            $resource->setEnabled($value);
        });
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        return $resource instanceof ToggleableInterface && self::ENABLED === $property;
    }
}
