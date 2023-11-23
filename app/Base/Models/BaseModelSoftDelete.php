<?php

namespace App\Base\Models;

use App\Base\Models\Concerns\BaseSoftDelete;
use App\Base\Models\Relations\HasRelationships;

class BaseModelSoftDelete extends BaseModel
{
    use BaseSoftDelete;
    use HasRelationships;
}
