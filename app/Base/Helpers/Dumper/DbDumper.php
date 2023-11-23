<?php

namespace App\Base\Helpers\Dumper;

use App\Base\Helpers\Dumper\Compressors\Compressor;
use App\Base\Helpers\Dumper\Exceptions\CannotSetParameter;
use App\Base\Helpers\Dumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    protected string $dbName = '';

    protected string $userName = '';

    protected string $password = '';

    protected string $host = 'localhost';

    protected int $port = 5432;

    protected string $socket = '';

    protected int $timeout = 0;

    protected string $dumpBinaryPath = '';

    protected array $includeTables = [];

    protected array $excludeTables = [];

    protected array $extraOptions = [];

    protected array $extraOptionsAfterDbName = [];

    protected Compressor $compressor;

    public function __construct(array $options)
    {
        $this->compressor = data_get($options, 'compressor');
        $this->host = data_get($options, 'host');
        $this->port = data_get($options, 'port');
        $this->dbName = data_get($options, 'dbName');
        $this->userName = data_get($options, 'userName');
        $this->password = data_get($options, 'password');
    }

    public static function create($options): static
    {
        return new static($options);
    }

    public function setDumpBinaryPath($dumpBinaryPath): static
    {
        if ('' !== $dumpBinaryPath && !str_ends_with($dumpBinaryPath, '/')) {
            $dumpBinaryPath .= '/';
        }

        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    public function getCompressorExtension()
    {
        return $this->compressor->useExtension();
    }

    public function includeTables($includeTables)
    {
        if (!empty($this->excludeTables)) {
            throw CannotSetParameter::conflictingParameters('includeTables', 'excludeTables');
        }

        if (!is_array($includeTables)) {
            $includeTables = explode(', ', $includeTables);
        }

        $this->includeTables = $includeTables;

        return $this;
    }

    public function excludeTables($excludeTables)
    {
        if (!empty($this->includeTables)) {
            throw CannotSetParameter::conflictingParameters('excludeTables', 'includeTables');
        }

        if (!is_array($excludeTables)) {
            $excludeTables = explode(', ', $excludeTables);
        }

        $this->excludeTables = $excludeTables;

        return $this;
    }

    public function addExtraOption($extraOption)
    {
        if (!empty($extraOption)) {
            $this->extraOptions[] = $extraOption;
        }

        return $this;
    }

    public function addExtraOptionAfterDbName($extraOptionAfterDbName)
    {
        if (!empty($extraOptionAfterDbName)) {
            $this->extraOptionsAfterDbName[] = $extraOptionAfterDbName;
        }

        return $this;
    }

    abstract public function dumpToFile($dumpFile);

    public function checkIfDumpWasSuccessFul(Process $process, $outputFile)
    {
        if (!$process->isSuccessful()) {
            throw DumpFailed::processDidNotEndSuccessfully($process);
        }

        if (!file_exists($outputFile)) {
            throw DumpFailed::dumpfileWasNotCreated($process);
        }

        if (filesize($outputFile) === 0) {
            throw DumpFailed::dumpfileWasEmpty($process);
        }
    }

    protected function getCompressCommand($command, $dumpFile)
    {
        $compressCommand = $this->compressor->useCommand();

        return "{$command} | {$compressCommand} > {$dumpFile}";

        /*
        if ($this->isWindows()) {
            return "{$command} | {$compressCommand} > {$dumpFile}";
        }
        return "(((({$command}; echo \$? >&3) | {$compressCommand} > {$dumpFile}) 3>&1) | (read x; exit \$x))";
        */
    }

    protected function echoToFile($command, $dumpFile)
    {
        $dumpFile = '"' . addcslashes($dumpFile, '\\"') . '"';

        if ($this->compressor) {
            return $this->getCompressCommand($command, $dumpFile);
        }

        return $command . ' > ' . $dumpFile;
    }

    protected function determineQuote()
    {
        return $this->isWindows() ? '"' : "'";
    }

    protected function isWindows()
    {
        return str_starts_with(strtoupper(PHP_OS), 'WIN');
    }
}
