<?php

namespace App\Base\Validators;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\Facades\Lang;
use Prettus\Validator\LaravelValidator;

class BaseValidator extends LaravelValidator
{
    public array $ruleDefault;

    public array $messageDefault;

    protected $model;

    protected $rules;

    protected $messages;

    protected $data;

    public function __construct(Factory $validator)
    {
        parent::__construct($validator);
        $this->ruleDefault = [];
        $this->messageDefault = [];
        $this->rules = [];
        $this->messages = [];
        $this->data = [];
        $this->model ?? null;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model): void
    {
        $this->model = $model;
    }

    protected function getAttributeNames(): array
    {
        return (array)Lang::get('models.' . app($this->getModel())->getTable() . '.attributes');
    }

    public function with(array $data)
    {
        $this->data = array_replace_recursive($this->data, $data, $this->data);

        return $this;
    }

    public function getData($key = null, $default = null): array
    {
        if ($key) {
            return data_get($this->data, $key, $default);
        }

        return $this->data;

    }

    public function setData($data): void
    {
        $this->data = array_replace_recursive($this->data, $data);
    }

    protected function addRulesMessages($rules = [], $messages = [], $default = true): static
    {
        $this->setRules($rules, $default);
        $this->setMessages($messages, $default);

        return $this;
    }

    public function setRules($rules = [], $default = true)
    {
        if ($default) {
            $this->rules = array_merge($this->getRulesDefault(), $rules);

            return;
        }

        $this->rules = $rules;

        return $this;
    }

    public function setMessages($messages = [], $default = true)
    {
        if ($default) {
            $this->messages = array_merge($this->getMessagesDefault(), $messages);

            return;
        }

        $this->messages = $messages;

        return $this;
    }

    protected function getRulesDefault(): array
    {
        return $this->ruleDefault;
    }

    protected function getMessagesDefault(): array
    {
        return $this->messageDefault;
    }

    public function customErrorsBag(): array
    {
        $all = [];
        $errorsMessage = $this->errorsBag()->messages();

        foreach ($errorsMessage as $key => $messages) {
            $messages = is_array($messages) ? reset($messages) : $messages;
            $each = collect([$key => $messages])->all();
            $all = array_merge($all, $each);
        }

        return $all;
    }

    public function passes($action = null): bool
    {
        $this->setData($this->data);
        $rules = $action ? $this->getRules($action) : $this->rules;
        $validator = $this->validator->make($this->data, $rules, $this->messages)->setAttributeNames($this->getAttributeNames());

        $beforeMethod = '_beforeValidate' . ucfirst($action);
        if (method_exists($this, $beforeMethod)) {
            $this->{$beforeMethod}($validator);
        } elseif (method_exists($this, '_before' . ucfirst($action))) {
            $this->{'_before' . ucfirst($action)}($validator);
        }

        $fails = $validator->fails();

        $afterMethod = '_afterValidate' . ucfirst($action);
        if (method_exists($this, $afterMethod)) {
            $this->{$afterMethod}($validator);
        } elseif (method_exists($this, '_after' . ucfirst($action))) {
            $this->{'_after' . ucfirst($action)}($validator);
        }

        if ($fails || !empty($validator->errors()->messages())) {
            $this->errors = $validator->messages();

            return false;
        }

        return true;
    }

    public function getInArrayRule($array): string
    {
        return '|in:' . $this->implode(',', $array);
    }

    protected function implode($prefix, $params): ?string
    {
        return join($prefix, array_map(function ($value) {
            return null === $value ? 'NULL' : $value;
        }, $params));
    }

    public function validateCreate($data): bool
    {
        return $this->addRulesMessages()->with($data)->passes();
    }

    public function validateUpdate($data): bool
    {
        return $this->addRulesMessages()->with($data)->passes();
    }

    public function validateShow($id): bool
    {
        $modelName = app($this->model)->getModel()->getTable();
        $data = ['id' => $id];
        $rules = ['id' => 'required|integer|custom_exists:' . $modelName . ',id'];

        return $this->addRulesMessages($rules, [], false)->with($data)->passes();
    }
}
