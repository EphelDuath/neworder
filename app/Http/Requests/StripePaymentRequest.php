<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StripePaymentRequest extends FormRequest
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
        return [
            'amount' => 'required|numeric|min:0',
            'address_line_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zipcode' => 'required',
            'country' => 'required',
            'phone' => 'required',
            'stripeToken' => 'required'
        ];
    }

    /**
     * Translate fields with user friendly name.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'amount' => trans('payment.amount'),
            'address_line_1' => trans('payment.address_line_1'),
            'city' => trans('payment.city'),
            'state' => trans('payment.state'),
            'zipcode' => trans('payment.zipcode'),
            'country' => trans('payment.country'),
            'phone' => trans('payment.phone'),
            'stripeToken' => 'Payment Token'
        ];
    }
}
