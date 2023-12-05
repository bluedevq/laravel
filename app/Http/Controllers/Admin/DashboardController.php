<?php

namespace App\Http\Controllers\Admin;

use App\Base\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return $this->render();
    }
}
