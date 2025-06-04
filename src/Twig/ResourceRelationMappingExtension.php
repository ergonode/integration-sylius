<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ResourceRelationMappingExtension extends AbstractExtension
{
    public function __construct(
        private iterable $resourceRelationMappingTypes,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('bitbag_ergonode_resource_relation_mappings', [$this, 'getRelationMappings']),
        ];
    }

    public function getRelationMappings(): iterable
    {
        return $this->resourceRelationMappingTypes;
    }
}
