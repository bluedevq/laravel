<?php

namespace App\Repositories;

use App\Base\Repositories\BaseRepository;
use App\Models\Administrator;

class AdministratorRepository extends BaseRepository
{
    public $model = Administrator::class;

    public function __construct()
    {
        parent::__construct();
    }
}
