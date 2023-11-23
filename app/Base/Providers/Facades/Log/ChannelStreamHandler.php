<?php

namespace App\Base\Providers\Facades\Log;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class ChannelStreamHandler extends StreamHandler
{
    public const LOG_FORMAT = "%message% %context% %extra%\n";

    protected string $channel;

    public function __construct($channel, $stream, $level = Logger::DEBUG, bool $bubble = true, ?int $filePermission = null, bool $useLocking = false)
    {
        $this->channel = $channel;
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
    }

    public function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter(static::LOG_FORMAT, null, true, true);
    }

    public function isHandling(LogRecord $record): bool
    {
        if (isset($record['channel'])) {
            return $record['level'] >= $this->level && $record['channel'] == $this->channel;
        }

        return $record['level'] >= $this->level;
    }
}
