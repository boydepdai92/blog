<?php

namespace App\Repositories\Contracts;

use App\Repositories\BaseRepository;
use App\Repositories\Criteria\CriteriaInterface;

interface RepositoryInterface
{
    public function create(array $attributes);

    public function update(array $attributes, $id);

    public function delete($id);

    public function getFieldsSearchable();

    public function pushCriteria($criteria): BaseRepository;

    public function paginate($limit = null, $columns = ['*'], $method = "paginate");

    public function findWhere(array $where, $columns = ['*']);

    public function findWhereForUpdate(array $conditions, $columns = ['*']);

    public function findWhereFirst(array $conditions, $columns = ['*']);

    public function criteriaPaginate(CriteriaInterface $criteria, int $limit = 15, array $columns = ['*']);
}
