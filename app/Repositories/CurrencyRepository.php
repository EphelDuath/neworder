<?php
namespace App\Repositories;

use App\Currency;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\ValidationException;

class CurrencyRepository
{
    protected $currency;
    protected $transaction;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Currency $currency,
        TransactionRepository $transaction
    ) {
        $this->currency = $currency;
        $this->transaction = $transaction;
    }

    /**
     * Get currency query
     *
     * @return Currency query
     */
    public function getQuery()
    {
        return $this->currency;
    }

    /**
     * Count currency
     *
     * @return integer
     */
    public function count()
    {
        return $this->currency->count();
    }

    /**
     * List all currencies by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->currency->all()->pluck('name', 'id')->all();
    }

    /**
     * List all currencies by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->currency->all()->pluck('id')->all();
    }

    /**
     * Get all currencies
     *
     * @return array
     */
    public function getAll()
    {
        return $this->currency->all();
    }

    /**
     * Get all currencies for currency input for select option
     *
     * @return array
     */
    public function selectAllForCurrencyInput()
    {
        return $this->currency->all(['id','name','position','symbol','decimal_place']);
    }

    /**
     * Get default currency
     *
     * @return array
     */
    public function default()
    {
        return $this->currency->filterByIsDefault(1)->first();
    }

    /**
     * Find currency with given id or throw an error.
     *
     * @param integer $id
     * @return Currency
     */
    public function findOrFail($id)
    {
        $currency = $this->currency->find($id);

        if (! $currency) {
            throw ValidationException::withMessages(['message' => trans('currency.could_not_find')]);
        }

        return $currency;
    }

    /**
     * Paginate all currencies using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'name';
        $order       = isset($params['order']) ? $params['order'] : 'asc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->currency->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new currency.
     *
     * @param array $params
     * @return Currency
     */
    public function create($params)
    {
        return $this->currency->forceCreate($this->formatParams($params));
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $currency = null)
    {
        $is_default = (isset($params['is_default']) && $params['is_default']) ? 1 : 0;

        if (
            $currency && 
            ((! $currency->id && $is_default && $this->transaction->count()) ||
            ($currency->id && $this->transaction->count() && (($is_default && !$currency->is_default) || (!is_default && $currency->is_default))))
        ) {
            throw ValidationException::withMessages(['message' => trans('currency.no_change_in_default_currency_after_transaction')]);
        }

        if ($is_default) {
            if ($currency && $currency->id) {
                $this->currency->where('id', '!=', $currency->id)->update(['is_default' => 0]);
            } else {
                $this->currency->whereNotNull('id')->update(['is_default' => 0]);
            }
        }

        $formatted = [
            'name'          => isset($params['name']) ? $params['name'] : null,
            'symbol'        => isset($params['symbol']) ? $params['symbol'] : null,
            'position'      => isset($params['position']) ? $params['position'] : 'prefix',
            'decimal_place' => isset($params['decimal_place']) ? $params['decimal_place'] : 2,
            'is_default'    => $is_default
        ];

        $formatted['slug'] = createSlug($formatted['name']);

        if ($this->count() < 1) {
            $formatted['is_default'] =  1;
        }

        return $formatted;
    }

    /**
     * Update given currency.
     *
     * @param Currency $currency
     * @param array $params
     *
     * @return Currency
     */
    public function update(Currency $currency, $params)
    {
        $currency->forceFill($this->formatParams($params, $currency))->save();

        if (! $this->default()) {
            $default_currency = $this->currency->first();
            $default_currency->is_default = 1;
            $default_currency->save();
        }

        return $currency;
    }

    /**
     * Find currency & check it can be deleted or not.
     *
     * @param integer $id
     * @return Currency
     */
    public function deletable($id)
    {
        $currency = $this->findOrFail($id);

        if ($currency->is_default) {
            throw ValidationException::withMessages(['message' => trans('currency.default_cannot_be_deleted')]);
        }

        if ($currency->invoices()->count()) {
            throw ValidationException::withMessages(['message' => trans('currency.has_many_invoices')]);
        }

        if ($currency->quotations()->count()) {
            throw ValidationException::withMessages(['message' => trans('currency.has_many_quotations')]);
        }

        if ($currency->transactions()->count()) {
            throw ValidationException::withMessages(['message' => trans('currency.has_many_transactions')]);
        }
        
        return $currency;
    }

    /**
     * Delete currency.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Currency $currency)
    {
        return $currency->delete();
    }

    /**
     * Delete multiple currencies.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->currency->whereIn('id', $ids)->delete();
    }
}
