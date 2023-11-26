<?php

namespace App\Base\Traits;

trait HidesDefaultAttributes
{
    protected array $defaultHidden = [
        'deleted_flag',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    public function getHidden(): array
    {
        return array_merge($this->defaultHidden, $this->hidden);
    }
}
