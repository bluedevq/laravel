<?php

namespace App\Base\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class BaseModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected array $dates = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->mergeActionBy();
    }

    public function getCreatedAtColumn()
    {
        return getConfig('model_field.created.at');
    }

    public function getDeletedFlag()
    {
        return getConfig('model_field.deleted.flag');
    }

    public function getUpdatedAtColumn()
    {
        return getConfig('model_field.updated.at');
    }

    public function getCreatedByColumn()
    {
        return getConfig('model_field.created.by');
    }

    public function getUpdatedByColumn()
    {
        return getConfig('model_field.updated.by');
    }

    public function getDeletedByColumn()
    {
        return getConfig('model_field.deleted.by');
    }

    public function getDeletedAtColumn()
    {
        return getConfig('model_field.deleted.at');
    }

    public function save(array $options = []): bool
    {
        $attribute = $this->getAttributes();
        $getKeyName = $this->getKeyName();
        $createdAt = $this->getCreatedAtColumn();
        $updatedAt = $this->getUpdatedAtColumn();
        $createdBy = $this->getCreatedByColumn();
        $updatedBy = $this->getUpdatedByColumn();
        $now = date('Y-m-d H:i:s');

        // created
        if ($createdAt && empty(Arr::get($attribute, $getKeyName))) {
            $attribute[$createdAt] = $now;
        }
        if ($createdBy && empty(Arr::get($attribute, $getKeyName))) {
            $attribute[$createdBy] = $this->getCurrentGuardUser();
        }

        // updated
        if ($updatedAt) {
            $attribute[$updatedAt] = $now;
        }
        if ($updatedBy) {
            $attribute[$updatedBy] = $this->getCurrentGuardUser();
        }

        $this->setRawAttributes([])->fill($attribute);

        return parent::save($options);
    }

    public function insert(array $values)
    {
        $createdAt = $this->getCreatedAtColumn();
        $updatedAt = $this->getUpdatedAtColumn();
        $createdBy = $this->getCreatedByColumn();
        $updatedBy = $this->getUpdatedByColumn();
        $now = date('Y-m-d H:i:s');

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $values = array_map(function ($items) use ($createdAt, $createdBy, $updatedAt, $updatedBy, $now) {
            if (!empty($createdAt) && !array_key_exists($createdAt, $items)) {
                $items[$createdAt] = $now;
            }

            if (!empty($updatedAt) && !array_key_exists($updatedAt, $items)) {
                $items[$updatedAt] = $now;
            }

            if (!empty($createdBy) && !array_key_exists($createdBy, $items)) {
                $items[$createdBy] = $this->getCurrentGuardUser();
            }

            if (!empty($updatedBy) && !array_key_exists($updatedBy, $items)) {
                $items[$updatedBy] = $this->getCurrentGuardUser();
            }

            return $items;
        }, $values);

        return parent::insert($values);
    }

    public function getNextInsertId(): int
    {
        return match ($this->getConnection()->getDriverName()) {
            'mysql' => $this->getNextIdMysql($this->getTable()),
            'pgsql' => $this->getNextIdPostgresql(),
        };
    }

    public function getCurrentGuardUser()
    {
        try {
            $id = getGuard()->check() ? getGuard()->user()->getKey() : null;
        } catch (\Exception $exception) {
            // not write logs
            return;
        }

        return $id;
    }

    protected function mergeActionBy()
    {
        if (empty($this->fillable)) {
            return;
        }

        $merge = [];

        if (!empty($this->getDeletedFlag())) {
            $merge[] = $this->getDeletedFlag();
        }
        if (!empty($this->getCreatedByColumn())) {
            $merge[] = $this->getCreatedByColumn();
        }
        if (!empty($this->getCreatedAtColumn())) {
            $merge[] = $this->getCreatedAtColumn();
        }
        if (!empty($this->getUpdatedByColumn())) {
            $merge[] = $this->getUpdatedByColumn();
        }
        if (!empty($this->getUpdatedAtColumn())) {
            $merge[] = $this->getUpdatedAtColumn();
        }
        if (!empty($this->getDeletedByColumn())) {
            $merge[] = $this->getDeletedByColumn();
        }
        if (!empty($this->getDeletedAtColumn())) {
            $merge[] = $this->getDeletedAtColumn();
        }

        $this->mergeFillable($merge);
    }

    protected function getNextIdMysql($table): int
    {
        $statement = $this->getConnection()->select("SHOW TABLE STATUS LIKE '{$table}'");

        return $statement[0]->Auto_increment;
    }

    protected function getNextIdPostgresql(): int
    {
        $statement = $this->getConnection()->select("SELECT nextval('{$this->getSequence()}')");

        return $statement[0]->nextval;
    }
}
