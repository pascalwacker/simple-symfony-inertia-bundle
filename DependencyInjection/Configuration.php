<?php

namespace Paw\SimpleSymfonyInertiaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    final public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('paw_simple_inertia');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('root_view')->defaultValue('app.html.twig')->end()
            ->end();

        return $treeBuilder;
    }
}
