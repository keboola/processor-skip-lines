<?php
// Catch all warnings and notices
set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
require __DIR__ . "/vendor/autoload.php";

use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

$arguments = getopt("", ["data:"]);
if (!isset($arguments["data"])) {
    $dataFolder = "/data";
} else {
    $dataFolder = $arguments["data"];
}

$configFile = $dataFolder . "/config.json";
if (!file_exists($configFile)) {
    echo "Config file not found" . "\n";
    exit(2);
}

try {
    $jsonDecode = new JsonDecode(true);
    $jsonEncode = new \Symfony\Component\Serializer\Encoder\JsonEncode();

    $config = $jsonDecode->decode(
        file_get_contents($dataFolder . "/config.json"),
        JsonEncoder::FORMAT
    );

    $parameters = (new \Symfony\Component\Config\Definition\Processor())->processConfiguration(
        new \Keboola\Processor\SkipLines\ConfigDefinition(),
        [isset($config["parameters"]) ? $config["parameters"] : []]
    );

    // process tables and sliced tables
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->notName("*.manifest")->in($dataFolder . "/in/tables")->depth(0);
    $outputPath = $dataFolder . "/out/tables";
    foreach ($finder as $sourceTable) {
        if (is_dir($sourceTable->getPathname())) {
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $slicedFiles = new FilesystemIterator($sourceTable->getPathname(), FilesystemIterator::SKIP_DOTS);
            $slicedDestination = $outputPath . '/' . $sourceTable->getFilename() . '/';
            if (!$fs->exists($slicedDestination)) {
                $fs->mkdir($slicedDestination);
            }
            foreach ($slicedFiles as $slicedFile) {
                $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " . $slicedFile->getPathName() . " > " . $slicedDestination . "/" . $slicedFile->getBasename();
                (new \Symfony\Component\Process\Process($copyCommand))->mustRun();
            }
        } else {
            $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " . $sourceTable->getPathName() . " > " . $outputPath . "/" . $sourceTable->getBasename();
            (new \Symfony\Component\Process\Process($copyCommand))->mustRun();
        }
    }

    // move table manifests
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.manifest")->in($dataFolder . "/in/tables")->depth(0);
    $outputPath = $dataFolder . "/out/tables";
    foreach ($finder as $sourceTableManifest) {
        $copyCommand = "mv " . $sourceTableManifest->getPathName() . " " . $outputPath . "/" . $sourceTableManifest->getBasename();
        (new \Symfony\Component\Process\Process($copyCommand))->mustRun();
    }

    // process files
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->notName("*.manifest")->in($dataFolder . "/in/files")->depth(0);
    $outputPath = $dataFolder . "/out/files";
    foreach ($finder as $sourceFile) {
        $copyCommand = "tail -n +" . ($parameters["lines"] + 1) . " " . $sourceFile->getPathName() . " > " . $outputPath . "/" . $sourceFile->getBasename();
        (new \Symfony\Component\Process\Process($copyCommand))->mustRun();
    }

    // move file manifests
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.manifest")->in($dataFolder . "/in/files")->depth(0);
    $outputPath = $dataFolder . "/out/files";
    foreach ($finder as $sourceFileManifest) {
        $copyCommand = "mv " . $sourceFileManifest->getPathName() . " " . $outputPath . "/" . $sourceFileManifest->getBasename();
        (new \Symfony\Component\Process\Process($copyCommand))->mustRun();
    }
} catch (\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException $e) {
    echo "Invalid configuration: " . $e->getMessage();
    exit(1);
} catch (\Keboola\Processor\SkipLines\Exception $e) {
    echo $e->getMessage();
    exit(1);
}
