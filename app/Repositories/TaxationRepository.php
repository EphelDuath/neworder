<?php
namespace App\Repositories;

use App\Taxation;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\ValidationException;

class TaxationRepository
{
    protected $taxation;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Taxation $taxation
    ) {
        $this->taxation = $taxation;
    }

    /**
     * Get taxation query
     *
     * @return Taxation query
     */
    public function getQuery()
    {
        return $this->taxation;
    }

    /**
     * Count taxation
     *
     * @return integer
     */
    public function count()
    {
        return $this->taxation->count();
    }

    /**
     * List all taxations by detail & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->taxation->all()->pluck('detail', 'id')->all();
    }

    /**
     * List all taxations by id
     *
     * @return array
     */

    public function listId()
    {
        return $this->taxation->all()->pluck('id')->all();
    }

    /**
     * Get all taxations
     *
     * @return array
     */
    public function getAll()
    {
        return $this->taxation->all();
    }

    /**
     * Get default taxation
     *
     * @return array
     */
    public function default()
    {
        return $this->taxation->filterByIsDefault(1)->first();
    }

    /**
     * Find taxation with given id or throw an error.
     *
     * @param integer $id
     * @return Taxation
     */
    public function findOrFail($id)
    {
        $taxation = $this->taxation->find($id);

        if (! $taxation) {
            throw ValidationException::withMessages(['message' => trans('taxation.could_not_find')]);
        }

        return $taxation;
    }

    /**
     * Paginate all taxations using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'name';
        $order       = isset($params['order']) ? $params['order'] : 'asc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->taxation->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new taxation.
     *
     * @param array $params
     * @return Taxation
     */
    public function create($params)
    {
        $this->validateTaxationName($params);

        return $this->taxation->forceCreate($this->formatParams($params));
    }

    /**
     * Validate unique taxation name with value.
     *
     * @param array $params
     * @param integer $id [default null]
     * @return null
     */
    public function validateTaxationName($params, $id = null)
    {
        $query = $this->taxation->whereNotNull('id');

        if ($id) {
            $query->where('id', '!=', $id);
        }

        if ($query->filterByValue($params['value'])->filterByName($params['name'])->count()) {
            throw ValidationException::withMessages(['name' => trans('taxation.taxation_exists')]);
        }
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $id = null)
    {
        $is_default = (isset($params['is_default']) && $params['is_default']) ? 1 : 0;

        if ($is_default) {
            if ($id) {
                $this->taxation->where('id', '!=', $id)->update(['is_default' => 0]);
            } else {
                $this->taxation->whereNotNull('id')->update(['is_default' => 0]);
            }
        }

        $formatted = [
            'name'        => isset($params['name']) ? $params['name'] : null,
            'value'       => isset($params['value']) ? $params['value'] : 0,
            'description' => isset($params['description']) ? $params['description'] : null,
            'is_default'  => $is_default
        ];

        if ($this->count() < 1) {
            $formatted['is_default'] =  1;
        }

        return $formatted;
    }

    /**
     * Update given taxation.
     *
     * @param Taxation $taxation
     * @param array $params
     *
     * @return Taxation
     */
    public function update(Taxation $taxation, $params)
    {
        $this->validateTaxationName($params, $taxation->id);

        $taxation->forceFill($this->formatParams($params, $taxation->id))->save();

        if (! $this->default()) {
            $default_taxation = $this->taxation->first();
            $default_taxation->is_default = 1;
            $default_taxation->save();
        }

        return $taxation;
    }

    /**
     * Find taxation & check it can be deleted or not.
     *
     * @param integer $id
     * @return Taxation
     */
    public function deletable($id)
    {
        $taxation = $this->findOrFail($id);

        if ($taxation->is_default) {
            throw ValidationException::withMessages(['message' => trans('taxation.default_cannot_be_deleted')]);
        }
        
        return $taxation;
    }

    /**
     * Delete taxation.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Taxation $taxation)
    {
        return $taxation->delete();
    }

    /**
     * Delete multiple taxations.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->taxation->whereIn('id', $ids)->delete();
    }
}
