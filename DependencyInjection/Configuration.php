<?php

namespace Msi\Bundle\CmfBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
        $rootNode = $treeBuilder->root('msi_cmf');

        $rootNode
            ->children()
                ->scalarNode('tiny_mce')
                    ->defaultValue('MsiCmfBundle:Form:tiny_mce.html.twig')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('app_locales')
                    ->prototype('scalar')->end()
                    ->defaultValue(array('en', 'fr'))
                    ->cannotBeEmpty()
                ->end()
            ->end();

        $this->addPageSection($rootNode);
        $this->addBlockSection($rootNode);

        return $treeBuilder;
    }

    private function addPageSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('page')
                    ->children()
                        ->arrayNode('layouts')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addBlockSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('block')
                    ->children()
                        ->arrayNode('actions')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('templates')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('slots')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
