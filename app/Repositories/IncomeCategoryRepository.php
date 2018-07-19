<?php
namespace App\Repositories;

use App\IncomeCategory;
use Illuminate\Validation\ValidationException;

class IncomeCategoryRepository
{
    protected $income_category;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        IncomeCategory $income_category
    ) {
        $this->income_category = $income_category;
    }

    /**
     * Get income category query
     *
     * @return IncomeCategory query
     */
    public function getQuery()
    {
        return $this->income_category;
    }

    /**
     * Count income category
     *
     * @return integer
     */
    public function count()
    {
        return $this->income_category->count();
    }

    /**
     * List all income categories by name & id for select option
     *
     * @return array
     */
    public function selectAll()
    {
        return $this->income_category->all(['name', 'id']);
    }

    /**
     * List all income categories by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->income_category->all()->pluck('name', 'id')->all();
    }

    /**
     * List all income categories by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->income_category->all()->pluck('id')->all();
    }

    /**
     * Get all income categories
     *
     * @return array
     */
    public function getAll()
    {
        return $this->income_category->all();
    }

    /**
     * Find income category with given id or throw an error.
     *
     * @param integer $id
     * @return IncomeCategory
     */
    public function findOrFail($id)
    {
        $income_category = $this->income_category->find($id);

        if (! $income_category) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find_transaction_category', ['type' => trans('transaction.income')])]);
        }

        return $income_category;
    }

    /**
     * Paginate all income categories using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'name';
        $order       = isset($params['order']) ? $params['order'] : 'asc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->income_category->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new income category.
     *
     * @param array $params
     * @return IncomeCategory
     */
    public function create($params)
    {
        return $this->income_category->forceCreate($this->formatParams($params));
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $action = 'create')
    {
        $formatted = [
            'name'        => isset($params['name']) ? $params['name'] : null,
            'description' => isset($params['description']) ? $params['description'] : null
        ];

        return $formatted;
    }

    /**
     * Update given income category.
     *
     * @param IncomeCategory $income_category
     * @param array $params
     *
     * @return IncomeCategory
     */
    public function update(IncomeCategory $income_category, $params)
    {
        $income_category->forceFill($this->formatParams($params, 'update'))->save();

        return $income_category;
    }

    /**
     * Find income category & check it can be deleted or not.
     *
     * @param integer $id
     * @return IncomeCategory
     */
    public function deletable($id)
    {
        $income_category = $this->findOrFail($id);

        if ($income_category->transactions()->count()) {
            throw ValidationException::withMessages(['message' => trans('transaction.has_many_transactions',['type' => trans('transaction.income')])]);
        }
        
        return $income_category;
    }

    /**
     * Delete income category.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(IncomeCategory $income_category)
    {
        return $income_category->delete();
    }

    /**
     * Delete multiple income categories.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->income_category->whereIn('id', $ids)->delete();
    }
}
