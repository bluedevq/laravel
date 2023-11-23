<?php

namespace App\Base\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BaseSoftDeletingScope extends SoftDeletingScope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!empty($model->getDeletedFlag()) && $model->getApplyDeletedFlag()) {
            $builder->where($model->getQualifiedDeletedFlag(), $model->getDeletedFlagValue());
        }

        if ($this->hasJoin($builder)) {
            foreach ($builder->getQuery()->joins as $join) {
                $tableName = str_replace(' AS ', '_x_x_', str_replace(' as ', '_x_x_', $join->table));
                $tableName = explode('_x_x_', $tableName);
                $tableName = end($tableName);

                $join->on(function ($query) use ($tableName, $model) {
                    $query->whereRaw(DB::raw("{$tableName}.{$model->getDeletedFlag()} = " . getConfig('deleted_flag.off')));
                });
            }
        }

    }

    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $update = [];

            // deleted flag
            $deletedFlag = $this->getDeletedFlagColumn($builder);
            if (!empty($deletedFlag)) {
                $update[$deletedFlag] = $builder->getModel()->getDeletedFlagValue(true);
            }

            // deleted at
            $deletedAt = $this->getDeletedAtColumn($builder);
            if (!empty($deletedAt)) {
                $update[$deletedAt] = $builder->getModel()->freshTimestampString();
            }

            if (!empty($update)) {
                return $builder->update($update);
            }

            return $builder;
        });
    }

    protected function addRestore(Builder $builder)
    {
        $builder->macro('restore', function (Builder $builder) {
            $builder->withTrashed();

            $update = [];

            // deleted flag
            $deletedFlag = $builder->getModel()->getDeletedFlag();
            if (!empty($deletedFlag)) {
                $update[$deletedFlag] = $builder->getModel()->getDeletedFlagValue();
            }

            // deleted at
            $deletedAt = $builder->getModel()->getDeletedAtColumn();
            if (!empty($deletedAt)) {
                $update[$deletedAt] = null;
            }

            if (!empty($update)) {
                return $builder->update($update);
            }

            return $builder;
        });
    }

    protected function addWithoutTrashed(Builder $builder)
    {
        $builder->macro('withoutTrashed', function (Builder $builder) {
            $model = $builder->getModel();
            $builder->withoutGlobalScope($this);

            // deleted flag
            $deletedFlag = $model->getDeletedFlag();
            if (!empty($deletedFlag)) {
                $builder->where($model->getQualifiedDeletedFlag(), $model->getDeletedFlagValue());
            }

            // deleted at
            $deletedAt = $model->getDeletedAtColumn();
            if (!empty($deletedAt)) {
                $builder->whereNull($model->getQualifiedDeletedAtColumn());
            }

            return $builder;
        });
    }

    protected function addOnlyTrashed(Builder $builder)
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();
            $builder->withoutGlobalScope($this);

            // deleted flag
            $deletedFlag = $model->getDeletedFlag();
            if (!empty($deletedFlag)) {
                $builder->where($model->getQualifiedDeletedFlag(), $model->getDeletedFlagValue(true));
            }

            // deleted at
            $deletedAt = $model->getDeletedAtColumn();
            if (!empty($deletedAt)) {
                $builder->whereNotNull($model->getQualifiedDeletedAtColumn());
            }

            return $builder;
        });
    }

    protected function hasJoin($builder)
    {
        try {
            return count((array)$builder->getQuery()->joins) > 0;
        } catch (\Exception $e) {
            // check is join
        }

        return false;
    }

    protected function getDeletedAtColumn(Builder $builder)
    {
        if (count((array)$builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDeletedAtColumn();
        }

        return $builder->getModel()->getDeletedAtColumn();
    }

    protected function getDeletedFlagColumn(Builder $builder)
    {
        if (count((array)$builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDeletedFlag();
        }

        return $builder->getModel()->getDeletedFlag();
    }
}
