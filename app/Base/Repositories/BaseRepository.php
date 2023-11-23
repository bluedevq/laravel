<?php

namespace App\Base\Repositories;

use App\Base\Repositories\Contracts\BaseRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->setModel();
    }

    public function find($id, array $columns = ['*'])
    {
        return $this->newQuery()->find($id, $columns);
    }

    public function findMany($ids, array $columns = ['*'])
    {
        return $this->newQuery()->findMany($ids, $columns);
    }

    public function findByField($field, $value, array $columns = ['*'])
    {
        return $this->newQuery()->where($field, $value)->get($columns);
    }

    public function findOrFail($id, array $columns = ['*'])
    {
        return $this->newQuery()->findOrFail($id, $columns);
    }

    public function findOrNew($id, array $columns = ['*'])
    {
        return $this->newQuery()->findOrNew($id, $columns);
    }

    public function firstOrNew(array $attributes, array $values = [])
    {
        return $this->newQuery()->firstOrNew($attributes, $values);
    }

    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->newQuery()->firstOrCreate($attributes, $values);
    }

    public function firstOrFail(array $columns = ['*'])
    {
        return $this->newQuery()->firstOrFail($columns);
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->newQuery()->updateOrCreate($attributes, $values);
    }

    public function create(array $attributes)
    {
        return $this->newQuery()->create($attributes);
    }

    public function forceCreate($attributes)
    {
        return $this->newQuery()->forceCreate($attributes);
    }

    public function update($id, $attributes)
    {
        $record = $this->find($id);

        if (empty($record)) {
            return false;
        }

        return tap($record, function ($instance) use ($attributes) {
            $instance->fill($attributes)->save();
        });
    }

    public function forceUpdate($id, $attributes)
    {
        $record = $this->find($id);

        if (empty($record)) {
            return false;
        }

        return tap($record, function ($instance) use ($attributes) {
            $instance->forceFill($attributes)->save();
        });
    }

    public function delete($id)
    {
        $result = $this->find($id);

        if (empty($result)) {
            return false;
        }

        return $result->delete();
    }

    public function forceDelete($id)
    {
        $result = $this->find($id);

        if (empty($result)) {
            return false;
        }

        return $result->forceDelete();
    }

    public function restore($id)
    {
        return $this->newQuery()->where('id', '=', $id)->restore();
    }

    public function newInstance(array $attributes = [], bool $exists = false): ?Model
    {
        return $this->getModel()->newInstance($attributes, $exists);
    }

    public function get(array $columns = ['*'])
    {
        return $this->newQuery()->get($columns);
    }

    public function first(array $columns = ['*'])
    {
        return $this->newQuery()->first($columns);
    }

    public function chunk($count, callable $callback)
    {
        return $this->newQuery()->chunk($count, $callback);
    }

    public function each(callable $callback, int $count = 1000)
    {
        return $this->newQuery()->each($callback, $count);
    }

    public function paginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null)
    {
        return $this->newQuery()->paginate($perPage, $columns, $pageName, $page);
    }

    public function simplePaginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null)
    {
        return $this->newQuery()->simplePaginate($perPage, $columns, $pageName, $page);
    }

    public function count(string $columns = '*')
    {
        return $this->newQuery()->count($columns);
    }

    public function getQuery(array $criteria = [])
    {
        return $this->newQuery()->getQuery();
    }

    public function newQuery()
    {
        return $this->model instanceof Model ? $this->model->newQuery() : clone $this->model;
    }

    public function setModel()
    {
        if (empty($this->model)) {
            throw new Exception("Model is not defined.");
        }

        $model = app($this->model);

        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        $this->model = $model;

        return $this->model;
    }

    public function getModel(): ?Model
    {
        return $this->model instanceof Model
            ? clone $this->model
            : $this->model->getModel();
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->model, $method], $arguments);
    }
}
