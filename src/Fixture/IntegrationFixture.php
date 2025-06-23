<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class IntegrationFixture extends AbstractResourceFixture
{
    public function getName(): string
    {
        return 'ergonode_integration';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('code')->cannotBeEmpty()->end()
                ->scalarNode('category_tree_code')->end()
                ->scalarNode('main_language_code')->end()
                ->booleanNode('enabled')->end()
                ->scalarNode('api_key')->cannotBeEmpty()->end()
                ->scalarNode('api_url')->cannotBeEmpty()->end()
        ;
    }
}
