<?php

use App\Jobs\BackupDB;
use App\Jobs\BackupLog;
use Illuminate\Support\Facades\Artisan;

// backup logs
Artisan::command('log:backup {params?*}', function ($params) {
    BackupLog::dispatch($params);
    $this->info('App\Jobs\BackupLog: Successfully !');
})->purpose('Backup logs');

// backup database
Artisan::command('db:backup {params?*}', function ($params) {
    BackupDB::dispatch($params);
    $this->info('App\Jobs\BackupDB: Successfully !');
})->purpose('Backup database');
