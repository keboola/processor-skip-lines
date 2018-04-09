<?php

declare(strict_types=1);

namespace Keboola\Processor\SkipLines;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

function processManifest(\SplFileInfo $sourceFile, string $destinationFolder) : void
{
    $copyCommand = "mv " . $sourceFile->getPathname() . " " . $destinationFolder . "/" . $sourceFile->getBasename();
    (new Process($copyCommand))->mustRun();
}
