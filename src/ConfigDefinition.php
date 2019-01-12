<?php

declare(strict_types=1);

namespace Keboola\Processor\SkipLines;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
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
        // @formatter:on
        return $parametersNode;
    }
}
