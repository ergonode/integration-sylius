<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Fixture;

use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ResourceRelationMappingFixture extends AbstractResourceFixture
{
    public function getName(): string
    {
        return 'ergonode_resource_relation_mapping';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
                ->scalarNode('integration')->cannotBeEmpty()->end()
                ->scalarNode('type')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifNotInArray(
                            array_map(fn (RelationType $relationType) => $relationType->value, RelationType::cases()),
                        )
                        ->thenInvalid('Invalid type')
                        ->end()
                ->end()
                ->scalarNode('sylius_value')->cannotBeEmpty()->end()
                ->scalarNode('ergonode_value')->cannotBeEmpty()->end()
                ->booleanNode('import_once')->defaultFalse()->end()
                ->scalarNode('channel_code')->end()
        ;
    }
}
