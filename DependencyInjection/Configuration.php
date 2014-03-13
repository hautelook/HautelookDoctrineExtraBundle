<?php

namespace Hautelook\RepositoryServiceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hautelook_repository_service');
        $rootNode
            ->children()
                ->arrayNode('entity')
                    ->cannotBeEmpty()
                    ->children()
                        ->scalarNode('location')
                            ->info('The location of the entities')
                            ->example('%kernel.root_dir%/../src/YourBundle/Entity')
                        ->end()
                        ->scalarNode('repository_location')
                            ->info('The location of the repositories')
                            ->example('%kernel.root_dir%/../src/YourBundle/Entity/Repository')
                        ->end()
                        ->scalarNode('service_prefix')
                            ->info('Prefix to use for service definition IDs')
                            ->example('vendor_bundle.entity.repository')
                        ->end()
                        ->scalarNode('namespace')
                            ->info('Entity namespace')
                            ->example('VendorBundle\Entity')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
