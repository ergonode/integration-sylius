<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\DependencyInjection;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class RegisterResourceRelationMappingTypesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('bitbag.sylius_ergonode_plugin.form_registry.resource_relation_mapping_types')) {
            return;
        }

        $formRegistry = $container->getDefinition('bitbag.sylius_ergonode_plugin.form_registry.resource_relation_mapping_types');

        $relationMappingTypes = [];
        foreach ($container->findTaggedServiceIds('bitbag.sylius_ergonode_plugin.resource_relation_mapping_type') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['type'], $attribute['label'])) {
                    throw new InvalidArgumentException(sprintf('Resource relation mapping  type `%s` needs to have `type` and `label` attributes.', $id));
                }

                $class = $container->getDefinition($id)->getClass();
                $relationMappingTypes[$attribute['type']] = ['label' => $attribute['label'], 'class' => $class];
                $formRegistry->addMethodCall('add', [$attribute['type'], 'mapping', $class]);
            }
        }
        $container->setParameter('bitbag.sylius_ergonode_plugin.resource_relation_mapping_types', $relationMappingTypes);
    }
}
