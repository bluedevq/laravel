<?php

namespace App\Base\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;

trait BaseSoftDelete
{
    use SoftDeletes;

    public static bool $applyDeletedFlag = true;

    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new BaseSoftDeletingScope());
    }

    public function initializeSoftDeletes()
    {
    }

    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());
        $time = $this->freshTimestamp();

        $columns = [];

        // deleted_flag
        if (!empty($this->getDeletedFlag())) {
            $flagOn = $this->getDeletedFlagValue(true);
            $this->{$this->getDeletedFlag()} = $flagOn;
            $columns[$this->getDeletedFlag()] = $flagOn;
        }

        // deleted_by
        if (!empty($this->getDeletedByColumn())) {
            $this->{$this->getDeletedByColumn()} = $this->getCurrentGuardUser();
            $columns[$this->getDeletedByColumn()] = $this->getCurrentGuardUser();
        }

        // deleted_at
        if (!empty($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);
        $this->syncOriginalAttributes(array_keys($columns));
    }

    public function restore(): ?bool
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        if (!empty($this->getDeletedFlag())) {
            $this->{$this->getDeletedFlag()} = $this->getDeletedFlagValue();
        }

        if (!empty($this->getDeletedByColumn())) {
            $this->{$this->getDeletedByColumn()} = null;
        }

        if (!empty($this->getDeletedAtColumn())) {
            $this->{$this->getDeletedAtColumn()} = null;
        }

        // Once we have saved the model, we will fire the "restored" event so this
        // developer will do anything they need to after a restore operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    public function trashed(): bool
    {
        if (!empty($this->getDeletedFlag())) {
            return $this->getDeletedFlagValue(true) == $this->{$this->getDeletedFlag()};
        }

        if (!empty($this->getDeletedAtColumn())) {
            return !is_null($this->{$this->getDeletedAtColumn()});
        }

        return false;
    }

    public function getDeletedFlag(): ?string
    {
        return getConfig('model_field.deleted.flag');
    }

    public function getQualifiedDeletedFlag(): ?string
    {
        return $this->qualifyColumn($this->getDeletedFlag());
    }

    public function getDeletedFlagValue(bool $isDeleted = false): ?string
    {
        return $isDeleted ? getConfig('deleted_flag.on') : getConfig('deleted_flag.off');
    }

    public function getApplyDeletedFlag(): bool
    {
        return self::$applyDeletedFlag;
    }

    public function getDeletedAtColumn()
    {
        return getConfig('model_field.deleted.at', null);
    }
}
