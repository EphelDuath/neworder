<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('id');

        $rules = [
            'discount' => 'required|numeric',
            'valid_start_date' => 'required|date',
            'valid_end_date' => 'required|date|after_or_equal:valid_start_date',
            'max_use_count' => 'required|integer'
        ];

        if ($this->method() === 'POST') {
            $rules['code'] = 'required|unique:coupons';
        } elseif ($this->method() === 'PATCH') {
            $rules['code'] = 'required|unique:coupons,code,'.$id.',id';
        }

        return $rules;
    }

    /**
     * Translate fields with user friendly name.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'code' => trans('coupon.code'),
            'discount' => trans('coupon.discount'),
            'valid_start_date' => trans('coupon.valid_start_date'),
            'valid_end_date' => trans('coupon.valid_end_date'),
            'max_use_count' => trans('coupon.max_use_count'),
        ];
    }
}
