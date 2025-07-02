<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property;

use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

abstract class PropertyValueAssigner
{
    abstract public function assign(
        ResourceInterface $resource,
        array $values,
        ResourceRelationMappingInterface $mapping,
    ): void;

    abstract public function supports(ResourceInterface $resource, string $property): bool;
}
