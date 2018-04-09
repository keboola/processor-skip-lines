<?php

declare(strict_types=1);

// Catch all warnings and notices
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline, array $errcontext) : void {
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
    foreach ($finder as $sourceTable) {
        \Keboola\Processor\SkipLines\processFile($sourceTable, $dataFolder . "/out/tables", $parameters);
    }

    // move table manifests
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.manifest")->in($dataFolder . "/in/tables")->depth(0);
    foreach ($finder as $sourceTableManifest) {
        \Keboola\Processor\SkipLines\processManifest($sourceTableManifest, $dataFolder . "/out/tables");
    }

    // process files and sliced files
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->notName("*.manifest")->in($dataFolder . "/in/files")->depth(0);
    $outputPath = $dataFolder . "/out/files";
    foreach ($finder as $sourceFile) {
        \Keboola\Processor\SkipLines\processFile($sourceFile, $outputPath, $parameters);
    }

    // move file manifests
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.manifest")->in($dataFolder . "/in/files")->depth(0);
    foreach ($finder as $sourceFileManifest) {
        \Keboola\Processor\SkipLines\processManifest($sourceFileManifest, $dataFolder . "/out/files");
    }
} catch (\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException $e) {
    echo "Invalid configuration: " . $e->getMessage();
    exit(1);
} catch (\Keboola\Processor\SkipLines\Exception $e) {
    echo $e->getMessage();
    exit(1);
} catch (Throwable $e) {
    echo get_class($e) . ':' . $e->getMessage();
    echo "\nFile: " . $e->getFile();
    echo "\nLine: " . $e->getLine();
    echo "\nCode: " . $e->getCode();
    echo "\nTrace: " . $e->getTraceAsString() . "\n";
    exit(2);
}
