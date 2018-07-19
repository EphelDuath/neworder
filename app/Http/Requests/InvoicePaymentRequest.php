<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoicePaymentRequest extends FormRequest
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
            'account_id' => 'required',
            'payment_method_id' => 'required',
            'income_category_id' => 'required',
            'date' => 'required|date',
            'conversion_rate' => 'numeric|min:0'
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
            'amount' => trans('invoice.amount'),
            'account_id' => trans('account.account'),
            'payment_method_id' => trans('payment.payment_method'),
            'income_category_id' => trans('transaction.transaction_category', ['type' => trans('transaction.income')]),
            'date' => trans('transaction.date'),
            'conversion_rate' => trans('transaction.conversion_rate')
        ];
    }
}
