<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\DependencyInjection;

use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Storage\ResourceRelationMappingStorage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ResourceRelationMappingStoragePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('bitbag.ergonode_plugin.resource_relation_mapping_storage_registry')) {
            return;
        }

        $registryDefinition = $container->findDefinition('bitbag.ergonode_plugin.resource_relation_mapping_storage_registry');
        $repositoryReference = new Reference('bitbag_ergonode.repository.resource_relation_mapping');

        foreach (RelationType::cases() as $relationType) {
            $storageServiceId = sprintf('bitbag.ergonode_plugin.storage.resource_relation_mapping.%s', strtolower($relationType->value));

            $storageDefinition = new Definition(ResourceRelationMappingStorage::class);
            $storageDefinition->setArguments([
                $repositoryReference,
                $relationType,
            ]);

            $container->setDefinition($storageServiceId, $storageDefinition);

            $registryDefinition->addMethodCall('addStorage', [
                $relationType,
                new Reference($storageServiceId),
            ]);
        }
    }
}
