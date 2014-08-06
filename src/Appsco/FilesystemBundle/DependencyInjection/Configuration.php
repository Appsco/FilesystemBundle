<?php

namespace Appsco\FilesystemBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('appsco_filesystem');
        $rootNode->children()
            ->arrayNode('rackspace')
                ->canBeEnabled()
                ->children()
                    ->arrayNode('client')
                        ->children()
                            ->scalarNode('url')
                                ->cannotBeEmpty()
                                ->defaultValue('https://lon.identity.api.rackspacecloud.com/v2.0/')
                            ->end()
                            ->scalarNode('username')->cannotBeEmpty()->isRequired()->end()
                            ->scalarNode('apikey')->cannotBeEmpty()->isRequired()->end()
                        ->end()
                    ->end()
                    ->arrayNode('objectstore')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('type')->isRequired()->defaultNull()->end()
                            ->scalarNode('name')->defaultNull()->end()
                            ->scalarNode('region')->defaultValue('LON')->end()
                            ->scalarNode('urlType')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
