<?php

declare(strict_types=1);

namespace Keboola\Processor\SkipLines;

use Keboola\Component\BaseComponent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class Component extends BaseComponent
{
    public function run(): void
    {
        /** @var Config $config */
        $config = $this->getConfig();
        // process tables and sliced tables
        $finder = new Finder();
        $finder->notName("*.manifest")->in($this->getDataDir() . "/in/tables")->depth(0);
        foreach ($finder as $sourceTable) {
            $this->processFile($sourceTable, $this->getDataDir() . "/out/tables", $config);
        }

        // move table manifests
        $finder = new Finder();
        $finder->name("*.manifest")->in($this->getDataDir() . "/in/tables")->depth(0);
        foreach ($finder as $sourceTableManifest) {
            $this->processManifest($sourceTableManifest, $this->getDataDir() . "/out/tables");
        }

        // process files and sliced files
        $finder = new Finder();
        $finder->notName("*.manifest")->in($this->getDataDir() . "/in/files")->depth(0);
        $outputPath = $this->getDataDir() . "/out/files";
        foreach ($finder as $sourceFile) {
            $this->processFile($sourceFile, $outputPath, $config);
        }

        // move file manifests
        $finder = new Finder();
        $finder->name("*.manifest")->in($this->getDataDir() . "/in/files")->depth(0);
        foreach ($finder as $sourceFileManifest) {
            $this->processManifest($sourceFileManifest, $this->getDataDir() . "/out/files");
        }
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }

    private function processManifest(\SplFileInfo $sourceFile, string $destinationFolder): void
    {
        $process = new Process(
            ['mv', $sourceFile->getPathname(), $destinationFolder . "/" . $sourceFile->getBasename()]
        );
        $process->mustRun();
    }

    private function processFile(\SplFileInfo $sourceFile, string $destinationFolder, Config $config): void
    {
        if (is_dir($sourceFile->getPathname())) {
            $fs = new Filesystem();
            $slicedFiles = new Finder();
            $slicedFiles->files()->in($sourceFile->getPathname());
            $slicedDestination = $destinationFolder . '/' . $sourceFile->getFilename() . '/';
            foreach ($slicedFiles as $slicedFile) {
                $fs->mkdir($slicedDestination . $slicedFile->getRelativePath());
                $this->skipLinesInFile(
                    $slicedFile->getPathname(),
                    $slicedDestination . $slicedFile->getRelativePathname(),
                    $config->getDirectionFrom(),
                    $config->getLines()
                );
            }
        } else {
            $this->skipLinesInFile(
                $sourceFile->getPathname(),
                $destinationFolder . "/" . $sourceFile->getBasename(),
                $config->getDirectionFrom(),
                $config->getLines()
            );
        }
    }

    private function skipLinesInFile(string $sourcePath, string $destinationPath, string $direction, int $lines): void
    {
        if ($direction === 'bottom') {
            $copyCommand = "busybox head -n -" . $lines . " " .
                escapeshellarg($sourcePath) . " > " .
                escapeshellarg($destinationPath);
        } else {
            $copyCommand = "busybox tail -n +" . ($lines + 1) . " " .
                escapeshellarg($sourcePath) . " > " .
                escapeshellarg($destinationPath);
        }
        $process = Process::fromShellCommandline($copyCommand);
        $process->setTimeout(null);
        $process->mustRun();
    }
}
