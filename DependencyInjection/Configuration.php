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
                ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('vps_protocol')->defaultValue(2.23)->cannotBeEmpty()->end()
                ->scalarNode('mode')->defaultValue('test')->cannotBeEmpty()->end()
                ->arrayNode('redirect_urls')
                        ->children()
                                ->scalarNode('ok')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('notauthed')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('abort')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('rejected')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('authenticated')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('registered')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('error')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('invalid')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('fail')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('malformed')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('token_ok')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('token_error')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                ->end();

        return $treeBuilder;
    }
}
