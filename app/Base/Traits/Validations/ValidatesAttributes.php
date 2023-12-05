<?php

namespace App\Base\Traits\Validations;

use Illuminate\Support\Facades\DB;

trait ValidatesAttributes
{
    protected $customMessages = [
        'date_format_multiple' => 'The :attribute field does not match the format datetime.',
    ];

    public function validateDateFormatMultiple($attribute, $value, $parameters): bool
    {
        if (!$value) {
            return true;
        }

        foreach ($parameters as $parameter) {
            $parsed = date_parse_from_format($parameter, $value);
            if (0 === $parsed['error_count'] && 0 === $parsed['warning_count']) {
                return true;
            }
        }

        return false;
    }

    public function validateCustomExists($attribute, $value, $parameters): bool
    {
        if (empty($parameters) || !is_array($parameters)) {
            return false;
        }

        $exists = DB::table($parameters[0])->where($parameters[1], '=', $value);
        $deletedFlag = getConfig('model_field.deleted.flag');
        if (!empty($deletedFlag)) {
            $exists->where($deletedFlag, '=', getConfig('deleted_flag.off'));
        }

        return (bool)$exists->first();
    }

    public function validateCustomUnique($attribute, $value, $parameters): bool
    {
        if (empty($parameters) || !is_array($parameters)) {
            return false;
        }

        $table = data_get($parameters, 0, '');
        $field = data_get($parameters, 1, '');
        $ignore = data_get($parameters, 2, '');
        $deletedFlag = getConfig('model_field.deleted.flag');

        if (empty($field)) {
            $field = $attribute;
        }

        $unique = DB::table($table)->where($field, '=', $value);

        if (!empty($ignore)) {
            $unique->where('id', '<>', $ignore);
        }

        if (!empty($deletedFlag)) {
            $unique->where($deletedFlag, '=', getConfig('deleted_flag.off'));
        }

        $unique = $unique->first();

        return empty($unique);
    }

    public function validateCustomExtention($attribute, $value, $parameters): bool
    {
        return in_array($value->getClientOriginalExtension(), $parameters);
    }
}
