<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        $type = request('type');

        $rules = [
            'type' => 'required|in:income,expense,account-transfer',
            'conversion_rate' => 'sometimes|required',
            'account_id' => 'required',
            'currency_id' => 'required',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method_id' => 'required',
        ];

        if ($type === 'income') {
            $rules['customer_id'] = 'required';
            $rules['transaction_category_id'] = 'required';
        } elseif ($type === 'expense') {
            $rules['supplier_id'] = 'required';
            $rules['transaction_category_id'] = 'required';
        } else {
            $rules['from_account_id'] = 'required';
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
        $type = request('type');

        return [
            'type' => trans('transaction.transaction_type'),
            'conversion_rate' => trans('transaction.conversion_rate'),
            'account_id' => trans('account.account'),
            'currency_id' => trans('currency.currency'),
            'amount' => trans('transaction.amount'),
            'date' => trans('transaction.date'),
            'payment_method_id' => trans('payment.payment_method'),
            'transaction_category_id' => trans('transaction.transaction_category', ['type' => ($type === 'income') ? trans('transaction.income') : trans('transaction.expense')]),
            'customer_id' => trans('user.customer'),
            'supplier_id' => trans('supplier.supplier'),
            'from_account_id' => trans('transaction.from_account'),
        ];
    }
}
