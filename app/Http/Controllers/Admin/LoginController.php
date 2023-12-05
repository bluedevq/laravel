<?php

namespace App\Http\Controllers\Admin;

use App\Base\Http\Controllers\BaseController;
use App\Validators\AdministratorValidator;
use Illuminate\Support\MessageBag;

class LoginController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest')->except('logout');
        $this->title = __('messages.page_title.admin.login');
        $this->validator = app(AdministratorValidator::class);
    }

    public function login()
    {
        return $this->render();
    }

    public function postLogin()
    {
        if (!$this->validator->validateLogin(request()->all())) {
            return redirect()->back()
                ->withErrors($this->validator->errorsBag())
                ->withInput(request()->except('password'));
        }

        $userData = [
            'email' => request('email'),
            'password' => request('password'),
        ];

        if (getGuard()->attempt($userData)) {
            return redirect('admin.home');
        }

        $errors = new MessageBag(['email' => [__('validation.email_password_valid')]]);

        return redirect()->back()
            ->withErrors($errors)
            ->withInput(request()->except('password'));
    }

    public function logout()
    {
        getGuard()->logout();

        return redirect('admin.login');
    }
}
