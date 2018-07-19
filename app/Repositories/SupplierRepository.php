<?php
namespace App\Repositories;

use App\Supplier;
use App\Repositories\CompanyRepository;
use Illuminate\Validation\ValidationException;

class SupplierRepository
{
    protected $supplier;
    protected $company;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Supplier $supplier,
        CompanyRepository $company
    ) {
        $this->supplier = $supplier;
        $this->company = $company;
    }

    /**
     * Get supplier query
     *
     * @return Supplier query
     */
    public function getQuery()
    {
        return $this->supplier->with('company');
    }

    /**
     * Count supplier
     *
     * @return integer
     */
    public function count()
    {
        return $this->supplier->count();
    }

    /**
     * List all suppliers by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->supplier->all()->pluck('name', 'id')->all();
    }

    /**
     * List all suppliers by id
     *
     * @return array
     */

    public function listId()
    {
        return $this->supplier->all()->pluck('id')->all();
    }

    /**
     * List all suppliers by name & id for select option
     *
     * @return array
     */
    public function selectAll()
    {
        return $this->supplier->all(['name', 'id']);
    }

    /**
     * Get all suppliers
     *
     * @return array
     */
    public function getAll()
    {
        return $this->supplier->with('company')->all();
    }

    /**
     * Find supplier with given id or throw an error.
     *
     * @param integer $id
     * @return Supplier
     */
    public function findOrFail($id)
    {
        $supplier = $this->supplier->find($id);

        if (! $supplier) {
            throw ValidationException::withMessages(['message' => trans('supplier.could_not_find')]);
        }

        return $supplier;
    }

    /**
     * Paginate all suppliers using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order       = isset($params['order']) ? $params['order'] : 'desc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $name        = isset($params['name']) ? $params['name'] : '';
        $email        = isset($params['email']) ? $params['email'] : '';
        $phone        = isset($params['phone']) ? $params['phone'] : '';

        return $this->supplier->with('company')->filterByName($name)->filterByEmail($email)->filterByPhone($phone)->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new supplier.
     *
     * @param array $params
     * @return Supplier
     */
    public function create($params)
    {
        $this->validateInputId($params);
        
        return $this->supplier->forceCreate($this->formatParams($params));
    }

    /**
     * Validate input ids.
     *
     * @param array $params
     * @return null
     */

    public function validateInputId($params)
    {
        $company_ids = $this->company->listId();

        $company_id = isset($params['company_id']) ? $params['company_id'] : null;

        if (! in_array($company_id, $company_ids)) {
            throw ValidationException::withMessages(['message' => trans('company.could_not_find')]);
        }
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
            'name'           => isset($params['name']) ? $params['name'] : null,
            'company_id'     => (isset($params['company_id']) && $params['company_id']) ? $params['company_id'] : null,
            'email'          => isset($params['email']) ? $params['email'] : null,
            'phone'          => isset($params['phone']) ? $params['phone'] : null,
            'address_line_1' => isset($params['address_line_1']) ? $params['address_line_1'] : null,
            'address_line_2' => isset($params['address_line_2']) ? $params['address_line_2'] : null,
            'city'           => isset($params['city']) ? $params['city'] : null,
            'state'          => isset($params['state']) ? $params['state'] : null,
            'zipcode'        => isset($params['zipcode']) ? $params['zipcode'] : null,
            'country_id'     => isset($params['country_id']) ? $params['country_id'] : null
        ];

        return $formatted;
    }

    /**
     * Update given supplier.
     *
     * @param Supplier $supplier
     * @param array $params
     *
     * @return Supplier
     */
    public function update(Supplier $supplier, $params)
    {
        $this->validateInputId($params);
        
        $supplier->forceFill($this->formatParams($params, 'update'))->save();

        return $supplier;
    }

    /**
     * Find supplier & check it can be deleted or not.
     *
     * @param integer $id
     * @return Supplier
     */
    public function deletable($id)
    {
        $supplier = $this->findOrFail($id);

        if ($supplier->transactions()->count()) {
            throw ValidationException::withMessages(['message' => trans('supplier.has_many_transactions')]);
        }
        
        return $supplier;
    }

    /**
     * Delete supplier.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Supplier $supplier)
    {
        return $supplier->delete();
    }

    /**
     * Delete multiple suppliers.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->supplier->whereIn('id', $ids)->delete();
    }
}
