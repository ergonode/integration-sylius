<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Factory;

use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMapping;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;

final readonly class ResourceRelationMappingFactory implements ResourceRelationMappingFactoryInterface
{
    public function createNew(): ResourceRelationMappingInterface
    {
        return new ResourceRelationMapping();
    }

    public function createTyped(string $type): ResourceRelationMappingInterface
    {
        $enumType = RelationType::from($type);

        $resourceRelationMapping = $this->createNew();
        $resourceRelationMapping->setRelationType($enumType);

        return $resourceRelationMapping;
    }
}
