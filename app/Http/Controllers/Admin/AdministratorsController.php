<?php

namespace App\Http\Controllers\Admin;

use App\Base\Http\Controllers\BaseController;
use App\Repositories\AdministratorRepository;
use App\Services\AdministratorService;
use App\Validators\AdministratorValidator;

class AdministratorsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->title = __('messages.page_title.admin.administrators');
        $this->repository = app(AdministratorRepository::class);
        $this->service = app(AdministratorService::class);
        $this->validator = app(AdministratorValidator::class);
    }
}
