<?php

namespace App\Base\Helpers;

class ExportCsv
{
    public $filename = '';

    public const FILE_EXTENSION = '.csv';

    public function __construct($filename = '')
    {
        if (empty($filename)) {
            $filename = 'export_' . date('YmdHis');
        }
        $this->filename = $filename;
    }

    public function export($dataHeader, $dataExport, bool $isSJIS = false, string $delimiter = ',')
    {
        $filename = $this->filename . self::FILE_EXTENSION;

        if ($isSJIS) {
            $filename = $this->setSJIS($filename);
            if (!empty($dataHeader)) {
                $dataHeader = $this->setHeaderSJIS($dataHeader);
            }
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $csvFile = fopen('php://output', 'w');
        } else { // UTF-8 BOM
            header('Content-Encoding: UTF-8');
            header('Content-type: application/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $csvFile = fopen('php://output', 'w');
            // Insert the UTF-8 BOM in the file
            fputs($csvFile, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        }

        if (!empty($dataHeader)) {
            $this->putItemCsv($csvFile, $dataHeader, $delimiter);
        }

        $listKeys = array_keys($dataHeader);

        if (!empty($dataExport)) {
            foreach ($dataExport as $item) {
                $tmp = [];

                foreach ($listKeys as $field) {
                    if (!is_null($field) && isset($item[$field])) {
                        $tmp[] = $isSJIS ? $this->setSJIS($item[$field]) : $item[$field];
                    }
                }

                if (!empty($tmp)) {
                    $this->putItemCsv($csvFile, $tmp, $delimiter);
                }
            }
        }

        fclose($csvFile);
        exit;
    }

    protected function setSJIS($filename)
    {
        return mb_convert_encoding($filename, 'SJIS', 'UTF-8');
    }

    protected function setHeaderSJIS($headers): array
    {
        $data = [];
        foreach ($headers as $key => $header) {
            $data[$key] = mb_convert_encoding($header, 'SJIS', 'UTF-8');
        }

        return $data;
    }

    protected function putItemCsv($handle, $item, $delimiter)
    {
        $item = array_map(function ($value) {
            return '"' . $value . '"';
        }, $item);

        return fputs($handle, implode($delimiter, $item) . "\r\n");
    }
}
