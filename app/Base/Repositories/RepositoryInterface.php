<?php

namespace App\Base\Repositories;

use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function find($id, array $columns = ['*']);

    public function findMany($ids, array $columns = ['*']);

    public function findByField($field, $value, array $columns = ['*']);

    public function findOrFail($id, array $columns = ['*']);

    public function findOrNew($id, array $columns = ['*']);

    public function firstOrNew(array $attributes, array $values = []);

    public function firstOrCreate(array $attributes, array $values = []);

    public function firstOrFail(array $columns = ['*']);

    public function updateOrCreate(array $attributes, array $values = []);

    public function create(array $attributes);

    public function forceCreate($attributes);

    public function update($id, $attributes);

    public function forceUpdate($id, $attributes);

    public function delete($id);

    public function forceDelete($id);

    public function restore($id);

    public function newInstance(array $attributes = [], bool $exists = false): ?Model;

    public function get(array $columns = ['*']);

    public function first(array $columns = ['*']);

    public function chunk($count, callable $callback);

    public function each(callable $callback, int $count = 1000);

    public function paginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null);

    public function simplePaginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null);

    public function count(string $columns = '*');

    public function getQuery();

    public function setModel();

    public function getModel(): ?Model;

    public function newQuery();

    public static function __callStatic($method, $arguments);

    public function __call($method, $arguments);
}
