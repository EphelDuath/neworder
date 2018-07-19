<?php
namespace App\Repositories;

use App\PaymentMethod;
use Illuminate\Validation\ValidationException;

class PaymentMethodRepository
{
    protected $payment_method;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        PaymentMethod $payment_method
    ) {
        $this->payment_method = $payment_method;
    }

    /**
     * Get payment method query
     *
     * @return PaymentMethod query
     */
    public function getQuery()
    {
        return $this->payment_method;
    }

    /**
     * Count payment method
     *
     * @return integer
     */
    public function count()
    {
        return $this->payment_method->count();
    }

    /**
     * List all payment methods by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->payment_method->all()->pluck('name', 'id')->all();
    }

    /**
     * List all payment methods by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->payment_method->all()->pluck('id')->all();
    }

    /**
     * List all payment methods filtered by type by name & id for select option
     *
     * @return array
     */
    public function selectAllPaymentMethodByType($type)
    {
        return $this->payment_method->filterByType($type)->get(['name','id']);
    }

    /**
     * Get all payment methods
     *
     * @return array
     */
    public function getAll()
    {
        return $this->payment_method->all();
    }

    /**
     * Find payment method with given id or throw an error.
     *
     * @param integer $id
     * @return PaymentMethod
     */
    public function findOrFail($id)
    {
        $payment_method = $this->payment_method->find($id);

        if (! $payment_method) {
            throw ValidationException::withMessages(['message' => trans('payment_method.could_not_find')]);
        }

        return $payment_method;
    }

    /**
     * Paginate all payment methods using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order       = isset($params['order']) ? $params['order'] : 'desc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->payment_method->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new payment method.
     *
     * @param array $params
     * @return PaymentMethod
     */
    public function create($params)
    {
        $this->validateName($params);

        return $this->payment_method->forceCreate($this->formatParams($params));
    }

    /**
     * Validate unique payment method name with type.
     *
     * @param array $params
     * @param integer $id [default null]
     * @return null
     */
    public function validateName($params, $id = null)
    {
        $query = $this->payment_method->whereNotNull('id');

        if ($id) {
            $query->where('id', '!=', $id);
        }

        if ($query->filterByName($params['name'])->filterByType($params['type'])->count()) {
            throw ValidationException::withMessages(['name' => trans('payment.payment_method_exists')]);
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
            'name'        => isset($params['name']) ? $params['name'] : null,
            'type'        => isset($params['type']) ? $params['type'] : null,
            'description' => isset($params['description']) ? $params['description'] : null
        ];

        return $formatted;
    }

    /**
     * Update given payment method.
     *
     * @param PaymentMethod $payment_method
     * @param array $params
     *
     * @return PaymentMethod
     */
    public function update(PaymentMethod $payment_method, $params)
    {
        $this->validateName($params, $payment_method->id);

        $payment_method->forceFill($this->formatParams($params, 'update'))->save();

        return $payment_method;
    }

    /**
     * Find payment method & check it can be deleted or not.
     *
     * @param integer $id
     * @return PaymentMethod
     */
    public function deletable($id)
    {
        $payment_method = $this->findOrFail($id);

        if ($payment_method->transactions()->count()) {
            throw ValidationException::withMessages(['message' => trans('payment.has_many_transactions')]);
        }
        
        return $payment_method;
    }

    /**
     * Delete payment method.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(PaymentMethod $payment_method)
    {
        return $payment_method->delete();
    }

    /**
     * Delete multiple payment methods.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->payment_method->whereIn('id', $ids)->delete();
    }
}
