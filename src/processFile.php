<?php

declare(strict_types=1);

namespace Keboola\Processor\SkipLines;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

function processFile(\SplFileInfo $sourceFile, string $destinationFolder, array $parameters) : void
{
    if (is_dir($sourceFile->getPathname())) {
        $fs = new Filesystem();
        $slicedFiles = new Finder();
        $slicedFiles->files()->in($sourceFile->getPathname());
        $slicedDestination = $destinationFolder . '/' . $sourceFile->getFilename() . '/';
        foreach ($slicedFiles as $slicedFile) {
            $fs->mkdir($slicedDestination . $slicedFile->getRelativePath());
            if ($parameters['direction_from'] === 'bottom') {
                $copyCommand = "head -n -" . $parameters["lines"] . " " .
                    escapeshellarg($slicedFile->getPathname()) . " > " .
                    escapeshellarg($slicedDestination . $slicedFile->getRelativePathname());
            } else {
                $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " .
                    escapeshellarg($slicedFile->getPathname()) . " > " .
                    escapeshellarg($slicedDestination . $slicedFile->getRelativePathname());
            }
            $process = new Process($copyCommand);
            $process->setTimeout(null);
            $process->mustRun();
        }
    } else {
        if ($parameters['direction_from'] === 'bottom') {
            $copyCommand = "head -n -" . $parameters["lines"] . " " .
                escapeshellarg($sourceFile->getPathname()) . " > " .
                escapeshellarg($destinationFolder . "/" . $sourceFile->getBasename());
        } else {
            $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " .
                escapeshellarg($sourceFile->getPathname()) . " > " .
                escapeshellarg($destinationFolder . "/" . $sourceFile->getBasename());
        }

        $process = new Process($copyCommand);
        $process->setTimeout(null);
        $process->mustRun();
    }
}
