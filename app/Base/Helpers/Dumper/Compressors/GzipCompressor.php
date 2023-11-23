<?php

namespace App\Base\Helpers\Dumper\Compressors;

class GzipCompressor implements Compressor
{
    public function useCommand(): string
    {
        return 'gzip';
    }

    public function useExtension(): string
    {
        return 'gz';
    }
}
