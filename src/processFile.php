<?php
namespace Keboola\Processor\SkipLines;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @param \SplFileInfo $sourceFile
 * @param $destinationFolder
 * @param array $parameters
 */
function processFile(\SplFileInfo $sourceFile, $destinationFolder, array $parameters)
{
    if (is_dir($sourceFile->getPathname())) {
        $fs = new Filesystem();
        $slicedFiles = new \FilesystemIterator($sourceFile->getPathname(), \FilesystemIterator::SKIP_DOTS);
        $slicedDestination = $destinationFolder . '/' . $sourceFile->getFilename() . '/';
        if (!$fs->exists($slicedDestination)) {
            $fs->mkdir($slicedDestination);
        }
        foreach ($slicedFiles as $slicedFile) {
            $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " . $slicedFile->getPathname() . " > " . $slicedDestination . "/" . $slicedFile->getBasename();
            (new Process($copyCommand))->mustRun();
        }
    } else {
        $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " . $sourceFile->getPathname() . " > " . $destinationFolder . "/" . $sourceFile->getBasename();
        (new Process($copyCommand))->mustRun();
    }
}