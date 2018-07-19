<?php
namespace App\Repositories;

use App\ExpenseCategory;
use Illuminate\Validation\ValidationException;

class ExpenseCategoryRepository
{
    protected $expense_category;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        ExpenseCategory $expense_category
    ) {
        $this->expense_category = $expense_category;
    }

    /**
     * Get expense category query
     *
     * @return ExpenseCategory query
     */
    public function getQuery()
    {
        return $this->expense_category;
    }

    /**
     * Count expense category
     *
     * @return integer
     */
    public function count()
    {
        return $this->expense_category->count();
    }

    /**
     * List all expense categories by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->expense_category->all()->pluck('name', 'id')->all();
    }

    /**
     * List all expense categories by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->expense_category->all()->pluck('id')->all();
    }

    /**
     * List all expense categories by name & id for select option
     *
     * @return array
     */
    public function selectAll()
    {
        return $this->expense_category->all(['name', 'id']);
    }

    /**
     * Get all expense categories
     *
     * @return array
     */
    public function getAll()
    {
        return $this->expense_category->all();
    }

    /**
     * Find expense category with given id or throw an error.
     *
     * @param integer $id
     * @return ExpenseCategory
     */
    public function findOrFail($id)
    {
        $expense_category = $this->expense_category->find($id);

        if (! $expense_category) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find_transaction_category', ['type' => trans('transaction.expense')])]);
        }

        return $expense_category;
    }

    /**
     * Paginate all expense categories using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'name';
        $order       = isset($params['order']) ? $params['order'] : 'asc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->expense_category->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new expense category.
     *
     * @param array $params
     * @return ExpenseCategory
     */
    public function create($params)
    {
        return $this->expense_category->forceCreate($this->formatParams($params));
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
     * Update given expense category.
     *
     * @param ExpenseCategory $expense_category
     * @param array $params
     *
     * @return ExpenseCategory
     */
    public function update(ExpenseCategory $expense_category, $params)
    {
        $expense_category->forceFill($this->formatParams($params, 'update'))->save();

        return $expense_category;
    }

    /**
     * Find expense category & check it can be deleted or not.
     *
     * @param integer $id
     * @return ExpenseCategory
     */
    public function deletable($id)
    {
        $expense_category = $this->findOrFail($id);

        if ($expense_category->transactions()->count()) {
            throw ValidationException::withMessages(['message' => trans('transaction.has_many_transactions',['type' => trans('transaction.expense')])]);
        }
        
        return $expense_category;
    }

    /**
     * Delete expense category.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(ExpenseCategory $expense_category)
    {
        return $expense_category->delete();
    }

    /**
     * Delete multiple expense categories.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->expense_category->whereIn('id', $ids)->delete();
    }
}
