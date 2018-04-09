<?php

declare(strict_types=1);

namespace Keboola\Processor\SkipLines;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigDefinition implements ConfigurationInterface
{
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("parameters");

        $rootNode
            ->children()
                ->integerNode("lines")
                    ->min(1)
                ->end()
                ->enumNode('direction_from')
                    ->values(['top', 'bottom'])
                    ->defaultValue('top')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
