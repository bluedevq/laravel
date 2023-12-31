<?php

namespace App\Base\Helpers\Dumper\Databases;

use App\Base\Helpers\Dumper\DbDumper;
use Symfony\Component\Process\Process;

class Sqlite extends DbDumper
{
    public function dumpToFile($dumpFile)
    {
        $process = $this->getProcess($dumpFile);
        $process->run();
        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    public function getDumpCommand($dumpFile)
    {
        $includeTables = rtrim(' ' . implode(' ', $this->includeTables));

        $dumpInSqlite = "echo 'BEGIN IMMEDIATE;\n.dump{$includeTables}'";
        if ($this->isWindows()) {
            $dumpInSqlite = "(echo BEGIN IMMEDIATE; & echo .dump{$includeTables})";
        }
        $quote = $this->determineQuote();

        $command = sprintf(
            "{$dumpInSqlite} | {$quote}%ssqlite3{$quote} --bail {$quote}%s{$quote}",
            $this->dumpBinaryPath,
            $this->dbName,
        );

        return $this->echoToFile($command, $dumpFile);
    }

    public function getProcess($dumpFile)
    {
        $command = $this->getDumpCommand($dumpFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }
}
