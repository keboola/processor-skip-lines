<?php

declare(strict_types=1);

use Keboola\Temp\Temp;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

require_once(__DIR__ . "/../vendor/autoload.php");

$testFolder = __DIR__;

$finder = new Finder();
$finder->directories()->sortByName()->in([$testFolder . '/top', $testFolder . '/bottom'])->depth(0);
$fs = new Filesystem();

foreach ($finder as $testSuite) {
    print "Test " . $testSuite->getPathname() . "\n";
    $temp = new Temp("processor-select-columns");
    $temp->initRunFolder();

    $copyCommand = "cp -R " . $testSuite->getPathname() . "/source/data/* " . $temp->getTmpFolder();
    Process::fromShellCommandline($copyCommand)->mustRun();

    $fs->mkdir($temp->getTmpFolder() . "/out/tables", 0777);
    $fs->mkdir($temp->getTmpFolder() . "/out/files", 0777);

    $runCommand = "export KBC_DATADIR=\"{$temp->getTmpFolder()}\" && php /code/src/run.php --data=" .
        $temp->getTmpFolder();
    $runProcess = Process::fromShellCommandline($runCommand);
    $runProcess->run();

    // detect errors
    if ($runProcess->getExitCode() > 0) {
        if (!$fs->exists($testSuite->getPathname() . "/expected")) {
            print "Failed as expected ({$runProcess->getExitCode()}): ";
            if ($runProcess->getOutput()) {
                print $runProcess->getOutput() . "\n";
            }
            if ($runProcess->getErrorOutput()) {
                print $runProcess->getErrorOutput() . "\n";
            }
        } else {
            print "Unexpectedly failed.\n";
            if ($runProcess->getOutput()) {
                print "\n" . $runProcess->getOutput() . "\n";
            }
            if ($runProcess->getErrorOutput()) {
                print "\n" . $runProcess->getErrorOutput() . "\n";
            }
            exit(1);
        }
        continue;
    }

    if ($runProcess->getOutput()) {
        print "\n" . $runProcess->getOutput() . "\n";
    }

    $diffCommand = "diff --exclude=.gitkeep --ignore-all-space --recursive " .
        $testSuite->getPathname() . "/expected/data/out " . $temp->getTmpFolder() . "/out";
    $diffProcess = Process::fromShellCommandline($diffCommand);
    $diffProcess->run();
    if ($diffProcess->getExitCode() > 0) {
        if ($diffProcess->getOutput()) {
            print "\n" . $diffProcess->getOutput() . "\n";
        }
        if ($diffProcess->getErrorOutput()) {
            print "\n" . $diffProcess->getErrorOutput() . "\n";
        }
        exit(1);
    }
}
