<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Criteria\CriteriaInterface;
use App\Repositories\Contracts\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model|Builder
     */
    protected $model;

    /**
     * @var Collection
     */
    protected $criteria;

    /**
     * @var array
     */
    protected $fieldSearchable = [];

    abstract public function model();

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->criteria = new Collection();
        $this->makeModel();
    }

    /**
     * @return Model
     * @throws Exception
     */
    public function makeModel(): Model
    {
        $model = app()->make($this->model());

        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Eloquent Model");
        }

        return $this->model = $model;
    }

    /**
     * @throws Exception
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * @throws Exception
     */
    public function create(array $attributes): Model
    {
        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetModel();

        return $model;
    }

    /**
     * @param array $attributes
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function update(array $attributes, $id)
    {
        $model = $this->model->firstOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetModel();

        return $model;
    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {
        $model = $this->model->firstOrFail($id);

        $this->resetModel();

        return $model->delete();
    }

    /**
     * @param null $limit
     * @param string[] $columns
     * @param string $method
     * @throws Exception
     */
    public function paginate($limit = null, $columns = ['*'], $method = "paginate")
    {
        $this->applyCriteria();
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->{$method}($limit, $columns);
        $results->appends(app('request')->query());
        $this->resetModel();

        return $results;
    }

    /**
     * @throws Exception
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyConditions($where);

        $model = $this->model->get($columns);

        $this->resetModel();

        return $model;
    }

    /**
     * @param array $conditions
     * @param string[] $columns
     * @return mixed
     * @throws Exception
     */
    public function findWhereForUpdate(array $conditions, $columns = ['*'])
    {
        $this->applyConditions($conditions);

        $results = $this->model->lockForUpdate()->first($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @throws Exception
     */
    public function findWhereFirst(array $conditions, $columns = ['*'])
    {
        $this->applyConditions($conditions);
        $this->applyCriteria();

        $results = $this->model->first($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @throws Exception
     */
    public function criteriaPaginate($criteria, int $limit = 15, array $columns = ['*'])
    {
        $this->pushCriteria($criteria);

        return $this->paginate($limit, $columns);
    }

    public function getCriteria(): Collection
    {
        return $this->criteria;
    }

    /**
     * @param $criteria
     * @return $this
     * @throws Exception
     */
    public function pushCriteria($criteria): BaseRepository
    {
        if (is_string($criteria)) {
            $criteria = new $criteria;
        }
        if (!$criteria instanceof CriteriaInterface) {
            throw new Exception("Class " . get_class($criteria) . " must be an instance of CriteriaInterface");
        }
        $this->criteria->push($criteria);

        return $this;
    }

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                if (strtoupper($condition) == 'IN') {
                    $this->model = $this->model->whereIn($field, $val);
                } else if (strtoupper($condition) == 'NOT_IN') {
                    $this->model = $this->model->whereNotIn($field, $val);
                } else {
                    $this->model = $this->model->where($field, $condition, $val);
                }
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    protected function applyCriteria(): BaseRepository
    {
        $criteria = $this->getCriteria();

        if ($criteria) {
            foreach ($criteria as $c) {
                if ($c instanceof CriteriaInterface) {
                    $this->model = $c->apply($this->model, $this);
                }
            }
        }

        return $this;
    }
}
