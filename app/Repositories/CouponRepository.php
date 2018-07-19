<?php
namespace App\Repositories;

use App\Coupon;
use App\Transaction;
use Illuminate\Validation\ValidationException;

class CouponRepository
{
    protected $coupon;
    protected $transaction;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Coupon $coupon,
        Transaction $transaction
    ) {
        $this->coupon = $coupon;
        $this->transaction = $transaction;
    }

    /**
     * Get coupon query
     *
     * @return Coupon query
     */
    public function getQuery()
    {
        return $this->coupon;
    }

    /**
     * Count coupon
     *
     * @return integer
     */
    public function count()
    {
        return $this->coupon->count();
    }

    /**
     * List all coupons by code & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->coupon->all()->pluck('code', 'id')->all();
    }

    /**
     * Get all coupons
     *
     * @return array
     */
    public function getAll()
    {
        return $this->coupon->all();
    }

    /**
     * Find coupon with given id or throw an error.
     *
     * @param integer $id
     * @return Coupon
     */
    public function findOrFail($id)
    {
        $coupon = $this->coupon->find($id);

        if (! $coupon) {
            throw ValidationException::withMessages(['message' => trans('coupon.could_not_find')]);
        }

        return $coupon;
    }

    /**
     * Paginate all coupons using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order       = isset($params['order']) ? $params['order'] : 'desc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->coupon->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new coupon.
     *
     * @param array $params
     * @return Coupon
     */
    public function create($params)
    {
        return $this->coupon->forceCreate($this->formatParams($params));
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
            'code'             => isset($params['code']) ? $params['code'] : null,
            'discount'         => isset($params['discount']) ? $params['discount'] : 0,
            'valid_start_date' => isset($params['valid_start_date']) ? $params['valid_start_date'] : null,
            'valid_end_date'   => isset($params['valid_end_date']) ? $params['valid_end_date'] : null,
            'valid_days'       => isset($params['valid_days']) ? implode(',',$params['valid_days']) : null,
            'new_user'         => (isset($params['new_user']) && $params['new_user']) ? 1 : 0,
            'max_use_count'    => isset($params['max_use_count']) ? $params['max_use_count'] : 0,
            'description'      => isset($params['description']) ? $params['description'] : null
        ];

        if ($action === 'create')
            $formatted['user_id'] = \Auth::user()->id;

        return $formatted;
    }

    /**
     * Validate coupon.
     *
     * @param string $coupon
     * @return array
     */
    public function validate($coupon)
    {
        if (! $coupon)
            return [];

        $coupon = $this->coupon->filterByCode($coupon)->first();

        if (! $coupon || $coupon->valid_start_date > date('Y-m-d')) {
            throw ValidationException::withMessages(['message' => trans('coupon.invalid_coupon')]);
        }

        if ($coupon->valid_end_date < date('Y-m-d')) {
            throw ValidationException::withMessages(['message' => trans('coupon.coupon_expired')]);
        }

        if ($coupon->use_count >= $coupon->max_use_count) {
            throw ValidationException::withMessages(['message' => trans('coupon.coupon_usage_expired')]);
        }

        $valid_day = ($coupon->valid_day) ? explode(',',$coupon->valid_day) : [];

        if ($coupon->valid_day && count($valid_day) && !in_array(strtolower(date('l')),$valid_day)) {

            $coupon_days = ($coupon->valid_day) ? explode(',',strtoupper($coupon->valid_day)) : [];

            throw ValidationException::withMessages(['message' => trans('coupon.valid_on_days',['days' => implode(',',$coupon_days)])]);
        }
        
        if ($coupon->new_user && $this->transaction->filterByStatus(1)->filterByGatewayStatus('success')->filterByUserId(\Auth::user()->id)->count()) {
            throw ValidationException::withMessages(['message' => trans('coupon.coupon_for_new_user')]);
        }

        return ['message' => trans('coupon.coupon_applied',['discount' => formatNumber($coupon->discount)]), 'discount' => ($coupon ? formatNumber($coupon->discount) : 0)];
    }

    /**
     * Increment coupon use count.
     *
     * @param string $coupon
     * @return void
     */
    public function incrementUseCount($coupon)
    {
        $this->coupon->filterByCode($coupon)->increment('use_count');
    }

    /**
     * Update given coupon.
     *
     * @param Coupon $coupon
     * @param array $params
     *
     * @return Coupon
     */
    public function update(Coupon $coupon, $params)
    {
        $coupon->forceFill($this->formatParams($params, 'update'))->save();

        return $coupon;
    }

    /**
     * Delete coupon.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Coupon $coupon)
    {
        return $coupon->delete();
    }

    /**
     * Delete multiple coupons.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->coupon->whereIn('id', $ids)->delete();
    }
}
