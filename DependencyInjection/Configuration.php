<?php

namespace L3\Bundle\CasBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('l3_cas');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('host')->defaultValue(300)->end()
            ->scalarNode('path')->defaultValue('')->end()
            ->scalarNode('port')->defaultValue(443)->end()
            ->scalarNode('ca')->defaultNull()->end()
            ->booleanNode('handleLogoutRequest')->defaultValue(false)->end()
            ->scalarNode('casLogoutTarget')->defaultNull()->end()
            ->booleanNode('force')->defaultValue(true)->end()
            ->booleanNode('gateway')->defaultValue(true)->end()
            ->end();

        return $treeBuilder;
    }
}
