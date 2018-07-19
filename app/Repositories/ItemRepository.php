<?php
namespace App\Repositories;

use App\Item;
use App\ItemPrice;
use App\Repositories\TaxationRepository;
use App\Repositories\ItemCategoryRepository;
use Illuminate\Validation\ValidationException;

class ItemRepository
{
    protected $item;
    protected $item_price;
    protected $item_category;
    protected $taxation;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Item $item,
        ItemPrice $item_price,
        ItemCategoryRepository $item_category,
        TaxationRepository $taxation
    ) {
        $this->item = $item;
        $this->item_price = $item_price;
        $this->item_category = $item_category;
        $this->taxation = $taxation;
    }

    /**
     * Get item query
     *
     * @return Item query
     */
    public function getQuery()
    {
        return $this->item;
    }

    /**
     * Count item
     *
     * @return integer
     */
    public function count()
    {
        return $this->item->count();
    }

    /**
     * List all items by detail & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->item->all()->pluck('detail', 'id')->all();
    }

    /**
     * List all items by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->item->all()->pluck('id')->all();
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function getAll()
    {
        return $this->item->with('itemPrice', 'itemCategory', 'taxation', 'itemPrice.currency')->get();
    }

    /**
     * Find item with given id or throw an error.
     *
     * @param integer $id
     * @return Item
     */
    public function findOrFail($id)
    {
        $item = $this->item->with('itemPrice', 'itemCategory', 'taxation', 'itemPrice.currency')->find($id);

        if (! $item) {
            throw ValidationException::withMessages(['message' => trans('item.could_not_find_item')]);
        }

        return $item;
    }

    /**
     * Paginate all items using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by          = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order            = isset($params['order']) ? $params['order'] : 'desc';
        $page_length      = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $name             = isset($params['name']) ? $params['name'] : '';
        $code             = isset($params['code']) ? $params['code'] : '';
        $item_category_id = isset($params['item_category_id']) ? $params['item_category_id'] : '';
        $taxation_id      = isset($params['taxation_id']) ? $params['taxation_id'] : '';

        return $this->item->with('itemPrice', 'itemCategory', 'taxation', 'itemPrice.currency')->filterByName($name)->filterByCode($code)->filterByItemCategoryId($item_category_id)->filterByTaxationId($taxation_id)->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new item.
     *
     * @param array $params
     * @return Item
     */
    public function create($params)
    {
        $this->validateInputId($params);

        $this->validatePrice($params);

        $item = $this->item->forceCreate($this->formatParams($params));

        $this->updatePrice($item, $params);

        return $item;
    }

    /**
     * Validate input ids.
     *
     * @param array $params
     * @return null
     */

    public function validateInputId($params)
    {
        $item_category_ids = $this->item_category->listId();
        $taxation_ids = $this->taxation->listId();

        $item_category_id = isset($params['item_category_id']) ? $params['item_category_id'] : null;
        $taxation_id = isset($params['taxation_id']) ? $params['taxation_id'] : null;

        if (! in_array($item_category_id, $item_category_ids)) {
            throw ValidationException::withMessages(['message' => trans('item.could_not_find_item_category')]);
        }

        if (! in_array($taxation_id, $taxation_ids)) {
            throw ValidationException::withMessages(['message' => trans('taxation.could_not_find')]);
        }
    }

    /**
     * Validate item price
     *
     * @param array $params
     * @param integer $id (optional)
     * @return void
     */
    private function validatePrice($params = array(), $id = null)
    {
        $prices = isset($params['price']) ? $params['price'] : [];

        if (! count($prices)) {
            throw ValidationException::withMessages(['message' => trans('currency.no_currency_found')]);
        }

        foreach ($prices as $price) {
            $currency_id = isset($price['id']) ? $price['id'] : null;
            $unit_price = isset($price['unit_price']) ? $price['unit_price'] : 0;

            if (! is_numeric($unit_price)) {
                throw ValidationException::withMessages(['item_price_'.$currency_id => trans('validation.numeric', ['attribute' => trans('item.item_price')])]);
            }

            if ($unit_price <= 0) {
                throw ValidationException::withMessages(['item_price_'.$currency_id => trans('validation.min.numeric', ['attribute' => trans('item.item_price'), 'min' => 0])]);
            }
        }
    }

    /**
     * Update item price
     *
     * @param Item $item
     * @param array $params
     * @return void
     */
    private function updatePrice(Item $item, $params)
    {
        $prices = isset($params['price']) ? $params['price'] : [];

        foreach ($prices as $price) {
            $item_price = $this->item_price->firstOrCreate([
                'item_id' => $item->id,
                'currency_id' => $price['id']
            ]);

            $item_price->unit_price = $price['unit_price'];
            $item_price->save();
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
            'code' => isset($params['code']) ? $params['code'] : null,
            'item_category_id' => isset($params['item_category_id']) ? $params['item_category_id'] : null,
            'taxation_id' => isset($params['taxation_id']) ? $params['taxation_id'] : null,
            'discount' => isset($params['discount']) ? $params['discount'] : 0,
            'description' => isset($params['description']) ? $params['description'] : null,
        ];

        return $formatted;
    }

    /**
     * Update given item.
     *
     * @param Item $item
     * @param array $params
     *
     * @return Item
     */
    public function update(Item $item, $params)
    {
        $this->validateInputId($params);

        $this->validatePrice($params);

        $item->forceFill($this->formatParams($params, 'update'))->save();

        $this->updatePrice($item, $params);

        return $item;
    }

    /**
     * Find item & check it can be deleted or not.
     *
     * @param integer $id
     * @return Item
     */
    public function deletable($id)
    {
        $item = $this->findOrFail($id);

        if ($item->invoiceItems()->count()) {
            throw ValidationException::withMessages(['message' => trans('item.has_many_invoice_items')]);
        }

        if ($item->quotationItems()->count()) {
            throw ValidationException::withMessages(['message' => trans('item.has_many_quotation_items')]);
        }
        
        return $item;
    }

    /**
     * Delete item.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Item $item)
    {
        return $item->delete();
    }

    /**
     * Delete multiple items.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->item->whereIn('id', $ids)->delete();
    }
}
