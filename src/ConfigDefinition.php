<?php

namespace Keboola\Processor\SkipLines;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigDefinition implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("parameters");

        $rootNode
            ->children()
                ->integerNode("lines")
                    ->min(1)
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
