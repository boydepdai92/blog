<?php

namespace App\Repositories\Criteria;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use App\Repositories\Contracts\RepositoryInterface;

class RequestCriteria implements CriteriaInterface
{
	# Mapping from request field to database field
	protected $criteriaFields = [
		'from_time' => 'created_at',
		'to_time'   => 'created_at',
	];

	const NOT_EQUAL = '_not_equal';

	private $operators = [
		self::NOT_EQUAL => '!='
	];

	/**
	 * @var Request
	 */
	protected $request;
	protected $dates;

	public function __construct(Request $request = null)
	{
		if (is_null($request)) {
			$this->request = app('request');
		} else {
			$this->request = $request;
		}
	}

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return Builder
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function apply($model, RepositoryInterface $repository): Builder
    {
		$this->dates = app($repository->model())->getDates();

		$model = $this->applySearch($model, $repository);

        return $this->applyOrder($model);
	}

    protected function getRequestInput()
	{
		return $this->request->input();
	}

	/**
	 * @param Model|Builder $model
	 * @param RepositoryInterface $repository
	 * @return mixed
	 */
	protected function applySearch($model, RepositoryInterface $repository)
	{
		$searchableFields = $this->cleanSearchableFields($repository->getFieldsSearchable());

		$fields = $this->parseFields($searchableFields);

        return $model->where(function ($query) use ($fields, $searchableFields) {
            if (isset($fields['and'])) {
                $query = $this->applyAndSearch($query, $fields['and'], $searchableFields);
            }

            if (isset($fields['or'])) {
                $this->applyOrSearch($query, $fields['or'], $searchableFields);
            }
        });
	}

	/**
	 * @param array $searchableFields
	 * @return array
	 */
	protected function cleanSearchableFields(array $searchableFields): array
    {
		if (empty($searchableFields)) return [];

		foreach ($searchableFields as $index => $value) {
			if (is_numeric($index)) {
				$searchableFields[$value] = "=";
			}
		}

		return $searchableFields;
	}

	/**
	 * @param array $searchableFields
	 * @return array
	 */
	protected function parseFields(array $searchableFields): array
    {
		$fields = [];
		if(empty($searchableFields)) return [];

		$andFields = $orFields = [];

		foreach ($this->getRequestInput() as $key => $value) {
			if ($value === '' || is_null($value) || !key_exists($key, $this->criteriaFields)) continue;

			$mappingValue = $this->criteriaFields[$key];

			#parseFields
			if (stripos($mappingValue, ',')) {
				$fieldParts = explode(',', $mappingValue);
				$block = $this->getBlock($fieldParts, $value, $searchableFields);
				$orFields[] = $block;
			} elseif(key_exists($mappingValue, $searchableFields)) {
				if (key_exists($mappingValue, $andFields)) {
					$andFields[$mappingValue] .= "," . $value;
					continue;
				}
				$andFields[$mappingValue] = $value;
			}
		}

		if (!empty($andFields)) {
			$fields['and'] = $andFields;
		}

		if (!empty($orFields)) {
			$fields['or'] = $orFields;
		}

		return $fields;
	}

	private function getBlock($fieldParts, $value, $searchableFields): array
    {
		$block = [];
		foreach ($fieldParts as $field) {
			if (key_exists($field, $searchableFields)) {
				$block[$field] = $value;
			}
		}

		return $block;
	}

	/**
	 * @param Builder $query
	 * @param $fields
	 * @param $searchableFields
	 * @return Builder
     */
	protected function applyAndSearch(Builder $query, $fields, $searchableFields): Builder
    {
		return $this->buildQuery($query, $fields, $searchableFields);
	}

	/**
	 * @param Builder $query
	 * @param $blocks
	 * @param $searchableFields
	 * @return Builder
     */
	protected function applyOrSearch(Builder $query, $blocks, $searchableFields): Builder
    {
		foreach ($blocks as $block) {
			$query = $query->where(function (Builder $query) use ($block, $searchableFields) {
				$this->buildQuery($query, $block, $searchableFields,false);
			});
		}

		return $query;
	}

	/**
	 * @param Builder $query
	 * @param $field
	 * @param $condition
	 * @param $value
	 * @param $isAnd
	 * @return Builder
     */
	protected function buildWhere(Builder $query, $field, $condition, $value, $isAnd = true): Builder
    {
		$boolean = ($isAnd) ? 'and' : 'or';

		if ($condition === 'nullable') {
			$not = ($value == "true" || $value == 1);

			return $query->whereNull($field, $boolean, $not);
		}

		$value = (is_string($value) && stripos($value, ',')) ? explode(',', $value) : $value;

		if (is_array($value)) {

            if (in_array($field, $this->dates)) {
                for($i = 0; $i < count($value); $i++) {
                    $value[$i] = $this->toDateTime($value[$i]);
                }
            }
            return $condition === 'between'
				? $query->whereBetween($field, $value, $boolean)
				: $query->whereIn($field, $value, $boolean);
		}

		return $condition === 'between'
			? $query->where($field,'>=', $value, $boolean)
			: $query->where($field, $condition, $value, $boolean);
	}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function applyOrder(Builder $model): Builder
    {
		return $this->applyRequestOrder($model, ['id' => 'DESC']);
	}


    /**
     * @param Builder $model
     * @param array $orders
     * @return Builder
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function applyRequestOrder(Builder $model, array $orders = []): Builder
    {
		$orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'order_by'));
		$sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sorted_by'), 'ASC');

		if (!empty($orderBy)) {
			$orders[$orderBy] = $sortedBy;
		}

		foreach ($orders as $key => $value) {
			$model = $model->orderBy($key, $value);
		}

		return $model;
	}

	/**
	 * @param $field
	 * @return array
	 */
	private function getRelation($field): array
    {
		$relation = null;

		if (stripos($field, '.')) {
			$explode = explode('.', $field);
			$field = array_pop($explode);
			$relation = implode('.', $explode);
		}

		return [$relation, $field];
	}

	/**
	 * @param Builder $query
	 * @param $fields
	 * @param $searchableFields
	 * @param bool $forceAnd
	 * @return Builder
	 */
	private function buildQuery(Builder $query, $fields, $searchableFields, $forceAnd = true): Builder
    {
		$isFirstWhere = $isAnd = true;

		foreach ($fields as $field => $value) {
			list($field, $condition) = $this->getConditionAndField($field, $searchableFields);

			if ($condition == 'like') {
				$value = '%' . $value . '%';
			}

			if (!$forceAnd && !$isFirstWhere) {
				$isAnd = false;
			}

			list($relation, $field) = $this->getRelation($field);
			$isFirstWhere = false;
			if (!is_null($relation)) {
				$boolean = ($isAnd) ? 'and' : 'or';
				$query = $query->has($relation, '>=', 1, $boolean, function (Builder $query) use ($field, $condition, $value) {
					$this->buildWhere($query, $query->getModel()->qualifyColumn($field), $condition, $value);
				});
				continue;
			}
			$this->buildWhere($query, $field, $condition, $value, $isAnd);
		}

		return $query;
	}

	private function getConditionAndField($field, $searchableFields): array
    {
		foreach (array_keys($this->operators) as $operator) {
			if (stripos($field, $operator)) {
				$condition = $this->operators[$operator];
				$field = trim($field, $operator);
				break;
			}
		}

		if (!isset($condition)) {
			$condition = $searchableFields[$field];
		}

		return [$field, $condition];
	}

	protected function toDateTime($value): Carbon
    {
	    return Carbon::createFromTimestamp($value);
    }
}
