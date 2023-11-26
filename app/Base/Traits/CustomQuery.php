<?php

namespace App\Base\Traits;

trait CustomQuery
{
    protected array $operators = [
        'gt' => 'greaterThan',
        'gteq' => 'greaterThanOrEqual',
        'lt' => 'lessThan',
        'lteq' => 'lessThanOrEqual',
        'eq' => 'equal',
        'neq' => 'notEqual',
        'in' => 'in',
        'nin' => 'notIn',
        'cons_f' => 'containsFirst',
        'cons_l' => 'containsLast',
        'cons' => 'contains',
        'lteqt' => 'lessThanOrEqualWithTime',
        'gteqt' => 'greaterThanOrEqualWithTime',
        'isnull' => 'isNull',
        'notnull' => 'notNull',
    ];

    protected object $builder;

    protected object $oldBuilder;

    protected string|null $table = null;

    protected array $queryParams = [];

    protected string|null $sortField = null;

    protected string $sortType = 'DESC';

    public function init($table = null): void
    {
        $this->queryParams = request()->all();
        $this->sortField = str_contains($this->sortField, '.') === false ? $this->table . '.' . $this->sortField : $this->sortField;
        $this->builder = $this;
        $this->oldBuilder = $this;
        $this->table = $table;
    }

    public function search(array $query = [], array $columns = []): object
    {
        // init builder
        $this->resetBuilder();
        $this->queryParams = $query;

        // return if it has no parameters
        if (empty($query)) {
            $this->builder->select($this->buildColumn($columns));

            if (!empty($this->sortField) && !empty($this->sortType)) {
                $this->builder->orderBy($this->sortField, $this->sortType);
            }

            return $this->builder;
        }

        // set sort type
        if (isset($query['direction'])) {
            $this->sortType = $query['direction'];
        }

        // set sort field
        if (isset($query['sort'])) {
            $this->sortField = $query['sort'];
        }

        // build sql
        foreach ($query as $virtual => $value) {
            if (is_array($value)) {
                $this->needWhereInOrNotIn($virtual, $value) ? $this->buildInOrNotInConditions($virtual, $value) : $this->buildConditions($virtual, $value);
                continue;
            }

            if (trim($value) !== '') {
                $this->buildConditions($virtual, $value);
            }
        }

        $this->builder->select($this->buildColumn($columns));

        if (!empty($this->sortField) && !empty($this->sortType)) {
            $this->builder->orderBy($this->sortField, $this->sortType);
        }

        return $this->builder;
    }

    protected function resetBuilder(): void
    {
        $this->builder = $this->oldBuilder;
    }

    protected function needWhereInOrNotIn($field, $value): bool
    {
        if (is_multi_array($value)) {
            return true;
        }

        return str_contains($field, '_in') || str_contains($field, '_nin');
    }

    protected function buildInOrNotInConditions($field, $value): bool|null
    {
        if (is_multi_array($value)) {
            $table = $field;
            foreach ($value as $f => $v) {
                if (!$this->needWhereInOrNotIn($f, $v)) {
                    continue;
                }

                $this->mapCondition($f, $v, $table);
            }

            return true;
        }

        return $this->mapCondition($field, $value, '');
    }

    protected function buildConditions($field, $value, string $table = ''): bool|null
    {
        if (!is_array($value) && trim($value) !== '') {
            return $this->mapCondition($field, $value, $table);
        }

        if (empty($value)) {
            return false;
        }

        foreach ($value as $f => $v) {
            $this->buildConditions($f, $v, $field);
        }

        return true;
    }

    protected function mapCondition($field, $value, $table): bool
    {
        $virtual = explode('_', $field);
        if (count($virtual) < 2) {
            return false;
        }

        $operator = end($virtual);
        array_pop($virtual);
        $virtual = implode('_', $virtual);
        $field = $table ? ($table . '.' . $virtual) : $virtual;

        if (array_key_exists($operator, $this->operators)) {
            $function = data_get($this->operators, $operator);
            $this->{$function}($field, $value);
        }

        return true;
    }

    protected function buildColumn($columns): mixed
    {
        empty($columns) ? $columns = [$this->table . '.*'] : null;
        foreach ($columns as &$column) {
            $column = !str_contains($column, '.') ? $this->table . '.' . $column : $column;
        }

        return $columns;
    }

    protected function equal($field, $value): void
    {
        $this->builder = $this->builder->where($field, $value);
    }

    protected function notEqual($field, $value): void
    {
        $this->builder = $this->builder->where($field, '!=', $value);
    }

    protected function greaterThan($field, $value): void
    {
        $this->builder = $this->builder->where($field, '>', $value . '%');
    }

    protected function greaterThanOrEqual($field, $value): void
    {
        $this->builder = $this->builder->where($field, '>=', $value);
    }

    protected function greaterThanOrEqualWithTime($field, $value): void
    {
        $value .= ' 00:00:00';
        $this->builder = $this->builder->where($field, '>=', $value);
    }

    protected function lessThan($field, $value): void
    {
        $this->builder = $this->builder->where($field, '<', $value);
    }

    protected function lessThanOrEqual($field, $value): void
    {
        $this->builder = $this->builder->where($field, '<=', $value);
    }

    protected function lessThanOrEqualWithTime($field, $value): void
    {
        $value .= ' 23:59:59';
        $this->builder = $this->builder->where($field, '<=', $value);
    }

    protected function in($field, $value): void
    {
        $this->builder = $this->builder->whereIn($field, (array)$value);
    }

    protected function notIn($field, $value): void
    {
        $this->builder = $this->builder->whereNotIn($field, (array)$value);
    }

    protected function contains($field, $value): void
    {
        $this->builder = $this->builder->where($field, 'LIKE', '%' . str_replace('%', '\%', $value) . '%');
    }

    protected function containsFirst($field, $value): void
    {
        $this->builder = $this->builder->where($field, 'LIKE', '%' . str_replace('%', '\%', $value));
    }

    protected function containsLast($field, $value): void
    {
        $this->builder = $this->builder->where($field, 'LIKE', str_replace('%', '\%', $value) . '%');
    }

    protected function isNull($field, $value): void
    {
        $this->builder = $this->builder->whereNull($field);
    }

    protected function notNull($field, $value): void
    {
        $this->builder = $this->builder->whereNotNull($field);
    }
}
