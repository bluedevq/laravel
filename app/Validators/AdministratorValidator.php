<?php

namespace App\Validators;

use App\Base\Validators\BaseValidator;
use App\Models\Administrator;

class AdministratorValidator extends BaseValidator
{
    protected $model = Administrator::class;

    public function validateLogin($params): bool
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        return $this->addRulesMessages($rules, [], false)->with($params)->passes();
    }
}
