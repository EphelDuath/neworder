<?php
namespace App\Repositories;

use App\Company;
use Illuminate\Validation\ValidationException;

class CompanyRepository
{
    protected $company;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Company $company
    ) {
        $this->company = $company;
    }

    /**
     * Get company query
     *
     * @return Company query
     */
    public function getQuery()
    {
        return $this->company;
    }

    /**
     * Count company
     *
     * @return integer
     */
    public function count()
    {
        return $this->company->count();
    }

    /**
     * List all companies by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->company->all()->pluck('name', 'id')->all();
    }

    /**
     * List all companies by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->company->all()->pluck('id')->all();
    }

    /**
     * List all companies by name & id for select option
     *
     * @return array
     */
    public function selectAll()
    {
        return $this->company->all(['name', 'id']);
    }

    /**
     * Get all companies
     *
     * @return array
     */
    public function getAll()
    {
        return $this->company->all();
    }

    /**
     * Find company with given id or throw an error.
     *
     * @param integer $id
     * @return Company
     */
    public function findOrFail($id)
    {
        $company = $this->company->find($id);

        if (! $company) {
            throw ValidationException::withMessages(['message' => trans('company.could_not_find')]);
        }

        return $company;
    }

    /**
     * Paginate all companies using given params.
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
        $website     = isset($params['website']) ? $params['website'] : '';
        $email       = isset($params['email']) ? $params['email'] : '';
        $phone       = isset($params['phone']) ? $params['phone'] : '';

        return $this->company->filterByName($name)->filterByEmail($email)->filterByWebsite($website)->filterByPhone($phone)->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new company.
     *
     * @param array $params
     * @return Company
     */
    public function create($params)
    {
        return $this->company->forceCreate($this->formatParams($params));
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
            'email'          => isset($params['email']) ? $params['email'] : null,
            'website'        => isset($params['website']) ? $params['website'] : null,
            'logo'           => null,
            'phone'          => isset($params['phone']) ? $params['phone'] : null,
            'fax'            => isset($params['fax']) ? $params['fax'] : null,
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
     * Update given company.
     *
     * @param Company $company
     * @param array $params
     *
     * @return Company
     */
    public function update(Company $company, $params)
    {
        $company->forceFill($this->formatParams($params, 'update'))->save();

        return $company;
    }

    /**
     * Find company & check it can be deleted or not.
     *
     * @param integer $id
     * @return Company
     */
    public function deletable($id)
    {
        $company = $this->findOrFail($id);

        if ($company->profiles()->count()) {
            throw ValidationException::withMessages(['message' => trans('company.has_many_customers')]);
        }

        if ($company->suppliers()->count()) {
            throw ValidationException::withMessages(['message' => trans('company.has_many_suppliers')]);
        }
        
        return $company;
    }

    /**
     * Delete company.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Company $company)
    {
        return $company->delete();
    }

    /**
     * Delete multiple companies.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->company->whereIn('id', $ids)->delete();
    }
}
