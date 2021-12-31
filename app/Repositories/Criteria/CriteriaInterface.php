<?php

namespace App\Repositories\Criteria;

use App\Repositories\Contracts\RepositoryInterface;

interface CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository);
}
