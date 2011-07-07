<?php

namespace Insig\SagepayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('insig_sagepay');

        $rootNode
            ->children()
                ->scalarNode('vps_protocol')->defaultValue('2.23')->end()
                ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('sagepay_url')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('notification_url')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('redirect_url')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}