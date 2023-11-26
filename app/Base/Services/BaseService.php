<?php

namespace App\Base\Services;

use App\Base\Helpers\ExportCsv;
use App\Base\Providers\Facades\Storages\BaseStorage;
use App\Base\Repositories\BaseRepository;

class BaseService
{
    public $repository;

    public function __construct()
    {
        $this->repository = app(BaseRepository::class);
    }

    public function index($params)
    {
        return $this->repository->list($params);
    }

    public function store($params): bool
    {
        try {
            $this->beforeStore($params);
            $this->uploadToMedia($params);
            $this->repository->create($params);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    public function update($id, $params): bool
    {
        try {
            $this->beforeUpdate($params);
            $this->uploadToMedia($params);
            $this->repository->update($id, $params);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    public function destroy($id): bool
    {
        try {
            $this->repository->delete($id);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    public function downloadCsv($params, $filename, $headers): void
    {
        (new ExportCsv($filename))->export($headers, $this->repository->export($params));
    }

    protected function uploadToMedia(&$params, $subFolder = ''): void
    {
        $isUpload = $params['is_upload'] ?? [];

        foreach ($isUpload as $field) {
            $file = $params[$field] ?? '';

            if (empty($file)) {
                continue;
            }

            $filename = explode('/', $file);
            $filename = end($filename);
            $controller = getControllerName();
            $folder = !empty($subFolder) ? $subFolder . '/' : (!empty($controller) ? $controller . '/' : '');
            $newPath = $folder . '' . $filename;

            $uploaded = BaseStorage::moveFromTmpToMedia($file, $newPath);
            if ($uploaded) {
                $params[$field] = $uploaded;
            }
        }
    }

    protected function beforeStore(&$params): void
    {
        //
    }

    protected function beforeUpdate(&$params): void
    {
        //
    }
}
