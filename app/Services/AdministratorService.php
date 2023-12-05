<?php

namespace App\Services;

use App\Base\Services\BaseService;
use App\Repositories\AdministratorRepository;

class AdministratorService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->repository = app(AdministratorRepository::class);
    }
}
