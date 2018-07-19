<?php
namespace App\Repositories;

use App\CustomerGroup;
use Illuminate\Validation\ValidationException;

class CustomerGroupRepository
{
    protected $customer_group;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        CustomerGroup $customer_group
    ) {
        $this->customer_group = $customer_group;
    }

    /**
     * Get customer group query
     *
     * @return CustomerGroup query
     */
    public function getQuery()
    {
        return $this->customer_group;
    }

    /**
     * Count customer group
     *
     * @return integer
     */
    public function count()
    {
        return $this->customer_group->count();
    }

    /**
     * List all customer groups by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->customer_group->all()->pluck('name', 'id')->all();
    }

    /**
     * List all customer groups by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->customer_group->all()->pluck('id')->all();
    }

    /**
     * List all customer groups by name & id for select option
     *
     * @return array
     */
    public function selectAll()
    {
        return $this->customer_group->all(['name', 'id']);
    }

    /**
     * Get all customer groups
     *
     * @return array
     */
    public function getAll()
    {
        return $this->customer_group->all();
    }

    /**
     * Find customer group with given id or throw an error.
     *
     * @param integer $id
     * @return CustomerGroup
     */
    public function findOrFail($id)
    {
        $customer_group = $this->customer_group->find($id);

        if (! $customer_group) {
            throw ValidationException::withMessages(['message' => trans('customer_group.could_not_find')]);
        }

        return $customer_group;
    }

    /**
     * Paginate all customer groups using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order       = isset($params['order']) ? $params['order'] : 'desc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->customer_group->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new customer group.
     *
     * @param array $params
     * @return CustomerGroup
     */
    public function create($params)
    {
        return $this->customer_group->forceCreate($this->formatParams($params));
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
     * Update given customer group.
     *
     * @param CustomerGroup $customer_group
     * @param array $params
     *
     * @return CustomerGroup
     */
    public function update(CustomerGroup $customer_group, $params)
    {
        $customer_group->forceFill($this->formatParams($params, 'update'))->save();

        return $customer_group;
    }

    /**
     * Delete customer group.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(CustomerGroup $customer_group)
    {
        return $customer_group->delete();
    }

    /**
     * Delete multiple customer groups.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->customer_group->whereIn('id', $ids)->delete();
    }
}
