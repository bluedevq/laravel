<?php

namespace App\Jobs;

use App\Base\Helpers\Dumper\Compressors\GzipCompressor;
use App\Base\Helpers\Dumper\DbDumper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BackupDB implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $name;

    protected int $maxFile;

    protected string $path;

    protected DbDumper $database;

    public function __construct()
    {
        /**
         * @var DbDumper $database
         */
        $database = match (env('DB_CONNECTION')) {
            'mysql' => 'App\Base\Helpers\Dumper\Databases\MySql',
            'pgsql' => 'App\Base\Helpers\Dumper\Databases\PostgreSql',
        };

        $this->database = $database::create([
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'dbName' => env('DB_DATABASE'),
            'userName' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'compressor' => new GzipCompressor(),
        ]);

        $this->name = 'database_backup_' . date('YmdHis') . '.sql.gz';
        $this->maxFile = env('DUMP_DB_MAX_FILE', 7);
        $this->path = database_path('backups');
    }

    public function handle()
    {
        try {
            $this->database->dumpToFile($this->path . '/' . $this->name);
            $this->deleteFiles();
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }

    protected function deleteFiles()
    {
        $files = array_diff(scandir($this->path), ['.', '..', '.gitignore']);
        $files = array_values($files);
        if (count($files) >= $this->maxFile) {
            $lists = array_slice($files, 0, count($files) - $this->maxFile);
            if (!empty($lists)) {
                foreach ($lists as $list) {
                    unlink($this->path . '/' . $list);
                }
            }
        }
    }
}
