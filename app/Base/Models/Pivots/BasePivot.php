<?php

namespace App\Base\Models\Pivots;

use App\Base\Models\Concerns\BaseSoftDelete;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BasePivot extends Pivot
{
    use BaseSoftDelete;
}
