<?php

namespace App\Base\Providers\Facades\Schema;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

class CustomSchema extends Schema
{
    public static function connection($name): Builder
    {
        $schema = static::$app['db']->connection($name)->getSchemaBuilder();

        return self::changeBlueprint($schema);
    }

    protected static function getFacadeAccessor(): string
    {
        return 'db.custom.schema';
    }

    protected static function changeBlueprint($schema)
    {
        $schema->blueprintResolver(function ($table, $callback) {
            return new CustomBlueprint($table, $callback);
        });

        return $schema;
    }
}
