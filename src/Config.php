<?php

declare(strict_types=1);

namespace Keboola\Processor\SkipLines;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getDirectionFrom(): string
    {
        return $this->getValue(['parameters', 'direction_from']);
    }

    public function getLines(): int
    {
        return (int) $this->getValue(['parameters', 'lines']);
    }
}
