<?php

namespace App\Base\Traits\Enums;

use App\Base\Traits\HasEnumLabel;

enum StatusActive: int
{
    use HasEnumLabel;

    case DISABLED = 0;
    case ENABLED = 1;

    public function label(): string
    {
        return match ($this) {
            self::DISABLED => 'Dừng hoạt động',
            self::ENABLED => 'Hoạt động',
        };
    }
}
