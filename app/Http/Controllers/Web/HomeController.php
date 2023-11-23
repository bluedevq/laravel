<?php

namespace App\Http\Controllers\Web;

use App\Base\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        return $this->render();
    }
}
