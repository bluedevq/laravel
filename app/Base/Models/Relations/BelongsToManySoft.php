<?php

namespace App\Base\Models\Relations;

use App\Base\Models\Concerns\InteractsWithPivotTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManySoft extends BelongsToMany
{
    use InteractsWithPivotTable;

    public bool $withSoftDeletes = false;

    protected $pivotDeletedAt;

    public function deletedAt()
    {
        return $this->pivotDeletedAt;
    }

    public function deletedFlag()
    {
        return getConfig('model_field.deleted.flag');
    }

    public function getQualifiedDeletedAtColumnName()
    {
        return $this->getQualifiedColumnName($this->deletedAt());
    }

    public function getQualifiedDeletedFlagColumnName()
    {
        return $this->getQualifiedColumnName(getConfig('model_field.deleted.flag'));
    }

    public function getDeletedFlagValue(bool $isDeleted = false): ?string
    {
        return $isDeleted ? getConfig('deleted_flag.on') : getConfig('deleted_flag.off');
    }

    public function getQualifiedColumnName($column)
    {
        return $this->table . '.' . $column;
    }

    public function withSoftDeletes($deletedAt = 'deleted_at')
    {
        $this->withSoftDeletes = true;

        $this->pivotDeletedAt = $deletedAt;

        $this->macro('withoutTrashedPivots', function () {
            $this->query->withGlobalScope('withoutTrashedPivots', function (Builder $query) {
                $query->where($this->getQualifiedDeletedFlagColumnName(), '=', $this->getDeletedFlagValue());
            })->withoutGlobalScopes(['onlyTrashedPivots']);

            return $this;
        });

        $this->macro('withTrashedPivots', function () {
            $this->query->withoutGlobalScopes(['withoutTrashedPivots', 'onlyTrashedPivots']);

            return $this;
        });

        $this->macro('onlyTrashedPivots', function () {
            $this->query->withGlobalScope('onlyTrashedPivots', function (Builder $query) {
                $query->where($this->getQualifiedDeletedFlagColumnName(), '=', $this->getDeletedFlagValue(true));
            })->withoutGlobalScopes(['withoutTrashedPivots']);

            return $this;
        });

        $this->macro('forceDetach', function ($ids = null, $touch = true) {
            $this->withSoftDeletes = false;

            return tap($this->detach($ids, $touch), function () {
                $this->withSoftDeletes = true;
            });
        });

        $this->macro('syncWithForceDetaching', function ($ids) {
            $this->withSoftDeletes = false;

            return tap($this->sync($ids), function () {
                $this->withSoftDeletes = true;
            });
        });

        return $this->withPivot($this->deletedAt())->withoutTrashedPivots();
    }

    protected function performJoin($query = null)
    {
        $query = $query ?: $this->query;
        $baseTable = $this->related->getTable();
        $key = $baseTable . '.' . $this->relatedKey;

        $query->join($this->table, $key, '=', $this->getQualifiedRelatedPivotKeyName())
            ->when($this->withSoftDeletes, function (Builder $query) {
                $query->where($this->getQualifiedDeletedFlagColumnName(), '=', $this->getDeletedFlagValue());
            });

        return $this;
    }
}
