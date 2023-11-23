<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Base\Helpers\Zipper\Zipper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BackupLog implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $args = [];

    protected string $type;

    public function __construct($args = [])
    {
        $this->type = data_get($args, 0, 'daily');
    }

    public function handle()
    {
        match ($this->type) {
            'month' => $this->month(),
            'daily' => $this->daily(),
        };
    }

    protected function month()
    {
        try {
            $start = Carbon::now()->startofMonth()->subMonth()->firstOfMonth()->toDateString();
            $end = Carbon::now()->startofMonth()->subMonth()->endOfMonth()->toDateString();
            $filename = Carbon::now()->startOfMonth()->subMonth()->format('Y-m') . '.zip';
            $logsDir = storage_path("logs");
            $filePath = $logsDir . '/' . $filename;

            $zipper = Zipper::create($filePath);

            $listFolder = [];
            for ($i = strtotime($start); $i <= strtotime($end); $i = $i + (60 * 60 * 24)) {
                $folder = $logsDir . '/' . date('Y-m-d', $i);
                if (file_exists($folder)) {
                    $listFolder[] = $folder;
                    $zipper->add($folder);
                }
            }
            $zipper->close();
            $this->deleteFolders($listFolder);
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }

    protected function daily()
    {
        try {
            $keepDay = env('ZIP_LOG_KEEP_DAY', 5);
            $date = Carbon::now()->subDays($keepDay)->format('Y-m-d');
            $filename = $date . '.zip';
            $logDirs = storage_path("logs");
            $filePath = $logDirs . '/' . $filename;

            $folder = $logDirs . '/' . $date;
            if (file_exists($folder)) {
                $zipper = Zipper::create($filePath);
                $zipper->add($folder);
                $zipper->close();
                $this->deleteDir($folder);
            }
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }

    protected function deleteFolders($folders)
    {
        if (empty($folders)) {
            return;
        }

        foreach ($folders as $folder) {
            if (file_exists($folder)) {
                $this->deleteDir($folder);
            }
        }
    }

    protected function deleteDir($dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir($dir . '/' . $file)) {
                $this->deleteDir($dir . '/' . $file);
            } else {
                unlink($dir . '/' . $file);
            }
        }

        return rmdir($dir);
    }
}
