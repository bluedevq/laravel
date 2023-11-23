<?php

namespace App\Base\Services;

use App\Base\Helpers\ExportCsv;
use App\Base\Providers\Facades\Storages\BaseStorage;

class BaseService
{
    protected $repository;

    public function __construct()
    {
        //
    }

    public function setRepository($repository): void
    {
        $this->repository = $repository;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function index($params)
    {
        return $this->getRepository()->getListForIndex($params);
    }

    public function store($params): bool
    {
        try {
            $this->prepareBeforeStore($params);
            $this->uploadToMedia($params);
            $this->getRepository()->create($params);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    public function update($id, $params): bool
    {
        try {
            $this->prepareBeforeUpdate($params);
            $this->uploadToMedia($params);
            $this->getRepository()->update($id, $params);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    public function destroy($id): bool
    {
        try {
            $this->getRepository()->delete($id);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    public function downloadCsv($params, $filename, $headers): void
    {
        $data = $this->getRepository()->getListForExport($params);
        $export = new ExportCsv($filename);
        $export->export($headers, $data);
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

    protected function prepareBeforeStore(&$params): void
    {
        //
    }

    protected function prepareBeforeUpdate(&$params): void
    {
        //
    }
}
