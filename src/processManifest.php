<?php
namespace Keboola\Processor\SkipLines;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * @param SplFileInfo $sourceFile
 * @param $destinationFolder
 */
function processManifest(\SplFileInfo $sourceFile, $destinationFolder)
{
    $copyCommand = "mv " . $sourceFile->getPathname() . " " . $destinationFolder . "/" . $sourceFile->getBasename();
    (new Process($copyCommand))->mustRun();
}
