<?php

namespace App\Base\Traits\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ImageUrl implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        $value = data_get($attributes, substr($key, 5));
        if (empty($value)) {
            return $value;
        }

        return getFileUrl($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
