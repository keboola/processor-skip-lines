<?php

namespace Keboola\Processor\SkipLines;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
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
        $slicedFiles = new Finder();
        $slicedFiles->files()->in($sourceFile->getPathname());
        $slicedDestination = $destinationFolder . '/' . $sourceFile->getFilename() . '/';
        if (!$fs->exists($slicedDestination)) {
            $fs->mkdir($slicedDestination);
        }
        foreach ($slicedFiles as $slicedFile) {
            if ($parameters['direction_from'] === 'bottom') {
                $copyCommand = "head -n -" . $parameters["lines"] . " " . escapeshellarg($slicedFile->getPathname()) . " > " . escapeshellarg($slicedDestination . "/" . $slicedFile->getBasename());
            } else {
                $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " . escapeshellarg($slicedFile->getPathname()) . " > " . escapeshellarg($slicedDestination . "/" . $slicedFile->getBasename());
            }

            (new Process($copyCommand))->mustRun();
        }
    } else {
        if ($parameters['direction_from'] === 'bottom') {
            $copyCommand = "head -n -" . $parameters["lines"] . " " . escapeshellarg($sourceFile->getPathname()) . " > " . escapeshellarg($destinationFolder . "/" . $sourceFile->getBasename());
        } else {
            $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " . escapeshellarg($sourceFile->getPathname()) . " > " . escapeshellarg($destinationFolder . "/" . $sourceFile->getBasename());
        }

        (new Process($copyCommand))->mustRun();
    }
}
