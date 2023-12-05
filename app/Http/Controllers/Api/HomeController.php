<?php

namespace App\Http\Controllers\Api;

use App\Base\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        return response()->json(['version' => '1.0']);
    }
}
