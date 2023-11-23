<?php

namespace App\Base\Validators\Contracts;

use App\Base\Validators\Concerns\BaseValidatesAttributes;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator;

class BaseValidatorContract extends Validator
{
    use BaseValidatesAttributes;

    public function __construct(Translator $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
        $this->setCustomMessages($this->customMessages);
    }
}
