<?php

namespace App\Base\Helpers\Dumper\Databases;

use App\Base\Helpers\Dumper\DbDumper;
use App\Base\Helpers\Dumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    protected bool $skipComments = true;

    protected bool $useExtendedInserts = true;

    protected bool $useSingleTransaction = false;

    protected bool $skipLockTables = false;

    protected bool $doNotUseColumnStatistics = false;

    protected bool $useQuick = false;

    protected string $defaultCharacterSet = '';

    protected bool $dbNameWasSetAsExtraOption = false;

    protected bool $allDatabasesWasSetAsExtraOption = false;

    protected string $setGtidPurged = 'AUTO';

    protected bool $createTables = true;

    private $tempFileHandle;

    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->port = 3306;
    }

    public function skipComments()
    {
        $this->skipComments = true;

        return $this;
    }

    public function dontSkipComments()
    {
        $this->skipComments = false;

        return $this;
    }

    public function useExtendedInserts()
    {
        $this->useExtendedInserts = true;

        return $this;
    }

    public function dontUseExtendedInserts()
    {
        $this->useExtendedInserts = false;

        return $this;
    }

    public function useSingleTransaction()
    {
        $this->useSingleTransaction = true;

        return $this;
    }

    public function dontUseSingleTransaction()
    {
        $this->useSingleTransaction = false;

        return $this;
    }

    public function skipLockTables()
    {
        $this->skipLockTables = true;

        return $this;
    }

    public function doNotUseColumnStatistics()
    {
        $this->doNotUseColumnStatistics = true;

        return $this;
    }

    public function dontSkipLockTables()
    {
        $this->skipLockTables = false;

        return $this;
    }

    public function useQuick()
    {
        $this->useQuick = true;

        return $this;
    }

    public function dontUseQuick()
    {
        $this->useQuick = false;

        return $this;
    }

    public function setDefaultCharacterSet($characterSet)
    {
        $this->defaultCharacterSet = $characterSet;

        return $this;
    }

    public function setGtidPurged($setGtidPurged)
    {
        $this->setGtidPurged = $setGtidPurged;

        return $this;
    }

    public function dumpToFile($dumpFile)
    {
        $this->guardAgainstIncompleteCredentials();

        $tempFileHandle = tmpfile();
        $this->setTempFileHandle($tempFileHandle);

        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    public function addExtraOption($extraOption)
    {
        if (str_contains($extraOption, '--all-databases')) {
            $this->dbNameWasSetAsExtraOption = true;
            $this->allDatabasesWasSetAsExtraOption = true;
        }

        if (preg_match('/^--databases (\S+)/', $extraOption, $matches) === 1) {
            $this->setDbName($matches[1]);
            $this->dbNameWasSetAsExtraOption = true;
        }

        return parent::addExtraOption($extraOption);
    }

    public function doNotCreateTables()
    {
        $this->createTables = false;

        return $this;
    }

    public function getDumpCommand($dumpFile, $temporaryCredentialsFile)
    {
        // $quote = $this->determineQuote();
        $command = [
            // "{$quote}{$this->dumpBinaryPath}mysqldump{$quote}",
            "{$this->dumpBinaryPath}mysqldump",
            "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
        ];

        if (!$this->createTables) {
            $command[] = '--no-create-info';
        }

        if ($this->skipComments) {
            $command[] = '--skip-comments';
        }

        $command[] = $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert';

        if ($this->useSingleTransaction) {
            $command[] = '--single-transaction';
        }

        if ($this->skipLockTables) {
            $command[] = '--skip-lock-tables';
        }

        if ($this->doNotUseColumnStatistics) {
            $command[] = '--column-statistics=0';
        }

        if ($this->useQuick) {
            $command[] = '--quick';
        }

        if ('' !== $this->socket) {
            $command[] = "--socket={$this->socket}";
        }

        foreach ($this->excludeTables as $tableName) {
            $command[] = "--ignore-table={$this->dbName}.{$tableName}";
        }

        if (!empty($this->defaultCharacterSet)) {
            $command[] = '--default-character-set=' . $this->defaultCharacterSet;
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if ('AUTO' !== $this->setGtidPurged) {
            $command[] = '--set-gtid-purged=' . $this->setGtidPurged;
        }

        if (!$this->dbNameWasSetAsExtraOption) {
            $command[] = $this->dbName;
        }

        if (!empty($this->includeTables)) {
            $includeTables = implode(' ', $this->includeTables);
            $command[] = "--tables {$includeTables}";
        }

        foreach ($this->extraOptionsAfterDbName as $extraOptionAfterDbName) {
            $command[] = $extraOptionAfterDbName;
        }

        return $this->echoToFile(implode(' ', $command), $dumpFile);
    }

    public function getContentsOfCredentialsFile()
    {
        $contents = [
            '[client]',
            "user = '{$this->userName}'",
            "password = '{$this->password}'",
            "port = '{$this->port}'",
        ];

        if ('' === $this->socket) {
            $contents[] = "host = '{$this->host}'";
        }

        return implode(PHP_EOL, $contents);
    }

    public function guardAgainstIncompleteCredentials()
    {
        foreach (['userName', 'host'] as $requiredProperty) {
            if (strlen($this->$requiredProperty) === 0) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }

        if (strlen($this->dbName) === 0 && !$this->allDatabasesWasSetAsExtraOption) {
            throw CannotStartDump::emptyParameter('dbName');
        }
    }

    public function getProcess($dumpFile)
    {
        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }

    public function getTempFileHandle()
    {
        return $this->tempFileHandle;
    }

    public function setTempFileHandle($tempFileHandle)
    {
        $this->tempFileHandle = $tempFileHandle;
    }
}
