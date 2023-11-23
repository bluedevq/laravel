<?php

namespace App\Base\Providers\Facades\Storages;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewStorage
{
    public function __call($name, $arguments)
    {
        return call_user_func_array([Storage::class, $name], $arguments);
    }

    public function put($file, $content): bool
    {
        if (!$this->isUploadFile($content)) {
            $content = $this->base64ToFile($content);
        }

        return Storage::put($file, $content);
    }

    public function path($file): string
    {
        return str_replace('/', '\\', storage_path($file));
    }

    public function url($fileName): string
    {
        if (!$fileName) {
            return '';
        }

        if (str_contains($fileName, 'http')) {
            return $fileName;
        }

        $fileName = str_replace('\\', '/', $fileName);

        return urldecode(Storage::url($fileName));
    }

    public function moveFromTmpToMedia($filePath, string $newName = ''): string
    {
        if (!Storage::exists($filePath)) {
            logError(__('messages.file_does_not_exist') . PHP_EOL . '(File path: ' . $filePath . ')');

            return false;
        }

        $newFilePath = getMediaDir(!empty($newName) ? $newName : $filePath);
        $nameBackup = $newFilePath . '_' . time();
        $logs = "(File path: " . $filePath . ", New path: " . $newFilePath . ")";

        try {
            if (Storage::exists($newFilePath)) {
                Storage::move($newFilePath, $nameBackup);
            }

            if (!Storage::move($filePath, $newFilePath)) {
                logError(__('messages.file_upload_failed') . PHP_EOL . $logs);

                return '';
            }

            if (Storage::exists($nameBackup)) {
                Storage::delete($nameBackup);
            }

            return $newFilePath;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $logs . PHP_EOL . $exception->getTraceAsString());
            if (Storage::exists($nameBackup)) {
                Storage::move($nameBackup, $newFilePath);
            }

            return '';
        }
    }

    public function uploadToTmp($content, $fileName = null): string
    {
        if (empty($fileName)) {
            $fileName = $this->genFileName($content);
        }

        if (!$this->validationFile($fileName, $content)) {
            return '';
        }

        $newFilePath = getTmpUploadDir(date('Y-m-d')) . '/' . $fileName;
        $this->deleteTmpDaily();
        $logs = "(Filename: " . $fileName . ', New path: ' . $newFilePath . ")";

        if ($this->isUploadFile($content)) {
            if (!Storage::putFileAs(getTmpUploadDir(date('Y-m-d')), $content, $fileName, 'public')) {
                logError(__('messages.file_upload_failed') . PHP_EOL . $logs);

                return '';
            }

            return $newFilePath;
        }

        if (!$this->put($newFilePath, $content)) {
            logError(__('messages.file_upload_failed') . PHP_EOL . $logs);

            return '';
        }

        return $newFilePath;
    }

    public function uploadFileToMedia($file, string $fileName = '', array $options = ['public-read']): array
    {
        $result = [
            'status' => true,
            'filename' => '',
        ];

        if (empty($fileName)) {
            $fileName = $this->genFileName($file);
        }

        if (!$this->validationFile($fileName, $file)) {
            $result['status'] = false;

            return $result;
        }

        $newFileSavePath = getMediaDir($fileName);
        if ($this->isUploadFile($file)) {
            $r = Storage::putFileAs(getMediaDir(), $file, $fileName, $options);
        } else {
            $r = $this->put($newFileSavePath, $file);
        }

        return [
            'status' => (bool)$r,
            'filename' => $r ? $newFileSavePath : '',
        ];
    }

    public function genFileName($file): string
    {
        $controller = getControllerName();
        $folder = !empty($controller) ? $controller . '/' : '';
        $pathInfo = $this->mbPathinfo($file->getClientOriginalName());
        $random = time() . sprintf('%09d', rand(0, 999999999));

        return $folder . Str::uuid()->toString() . '_' . $random . '.' . data_get($pathInfo, 'extension');
    }

    public function mbPathInfo($filepath): array
    {
        preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $filepath, $m);

        return [
            'dirname' => $m[1] ?? '',
            'basename' => $m[2] ?? '',
            'extension' => $m[5] ?? '',
            'filename' => $m[3] ?? '',
        ];
    }

    protected function isUploadFile($data): bool
    {
        return $data instanceof UploadedFile;
    }

    protected function base64ToFile($fileData): bool|string
    {
        @list($type, $fileData) = explode(';', $fileData);
        @list(, $fileData) = explode(',', $fileData);

        return base64_decode($fileData);
    }

    protected function validationFile($fileName, $content): bool
    {
        if ($this->isUploadFile($content)) {
            $ext = $content->getClientOriginalExtension();
        } else {
            $ext = Arr::last(explode('.', $fileName));
        }

        $extBlacklist = (array)getConfig('ext_blacklist', ['php', 'phtml', 'html']);

        if (in_array($ext, $extBlacklist)) {
            logError(__('messages.file_upload_blacklist') . PHP_EOL . "(File: " . $fileName . ", Blacklist: " . json_encode($extBlacklist) . ")");

            return false;
        }

        return true;
    }

    protected function deleteTmpDaily(): void
    {
        for ($i = 2; $i <= 30; $i++) { // from 2 days ago
            $directory = getTmpUploadDir(today()->subDays($i)->format('Y-m-d'));
            Storage::deleteDirectory($directory);
        }
    }
}
