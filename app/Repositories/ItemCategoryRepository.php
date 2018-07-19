<?php
namespace App\Repositories;

use App\ItemCategory;
use Illuminate\Validation\ValidationException;

class ItemCategoryRepository
{
    protected $item_category;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        ItemCategory $item_category
    ) {
        $this->item_category = $item_category;
    }

    /**
     * Get item category query
     *
     * @return ItemCategory query
     */
    public function getQuery()
    {
        return $this->item_category;
    }

    /**
     * Count item category
     *
     * @return integer
     */
    public function count()
    {
        return $this->item_category->count();
    }

    /**
     * List all item categories by detail & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->item_category->all()->pluck('detail', 'id')->all();
    }

    /**
     * List all item categories by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->item_category->all()->pluck('id')->all();
    }

    /**
     * Get all item categories
     *
     * @return array
     */
    public function getAll()
    {
        return $this->item_category->all();
    }

    /**
     * Find item category with given id or throw an error.
     *
     * @param integer $id
     * @return ItemCategory
     */
    public function findOrFail($id)
    {
        $item_category = $this->item_category->find($id);

        if (! $item_category) {
            throw ValidationException::withMessages(['message' => trans('item.could_not_find_item_category')]);
        }

        return $item_category;
    }

    /**
     * Paginate all item categories using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'name';
        $order       = isset($params['order']) ? $params['order'] : 'asc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->item_category->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new item category.
     *
     * @param array $params
     * @return ItemCategory
     */
    public function create($params)
    {
        return $this->item_category->forceCreate($this->formatParams($params));
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
            'type'        => isset($params['type']) ? $params['type'] : 'product',
            'description' => isset($params['description']) ? $params['description'] : null
        ];

        return $formatted;
    }

    /**
     * Update given item category.
     *
     * @param ItemCategory $item_category
     * @param array $params
     *
     * @return ItemCategory
     */
    public function update(ItemCategory $item_category, $params)
    {
        $item_category->forceFill($this->formatParams($params, 'update'))->save();

        return $item_category;
    }

    /**
     * Find item category & check it can be deleted or not.
     *
     * @param integer $id
     * @return ItemCategory
     */
    public function deletable($id)
    {
        $item_category = $this->findOrFail($id);

        if ($item_category->items()->count()) {
            throw ValidationException::withMessages(['message' => trans('item.has_many_items')]);
        }
        
        return $item_category;
    }

    /**
     * Delete item category.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(ItemCategory $item_category)
    {
        return $item_category->delete();
    }

    /**
     * Delete multiple item categories.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->item_category->whereIn('id', $ids)->delete();
    }
}
