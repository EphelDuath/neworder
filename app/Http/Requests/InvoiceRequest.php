<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
        $is_draft = request('is_draft');

        $rules = [
            'invoice_number' => 'required',
            'invoice_prefix' => 'required',
            'reference_number' => 'required',
            'currency_id' => 'required',
            'item_type' => 'required',
        ];

        if (! $is_draft) {
            $rules['customer_id'] = 'required';
            $rules['date'] = 'required|date';
            $rules['due_date'] = 'required';
            $rules['due_date_detail'] = 'required_if:due_date,due_on_date|date|after_or_equal:date';
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
            'invoice_number' => trans('invoice.invoice_number'),
            'invoice_prefix' => trans('invoice.prefix'),
            'reference_number' => trans('invoice.reference_number'),
            'currency_id' => trans('currency.currency'),
            'item_type' => trans('invoice.item_type'),
            'customer_id' => trans('user.customer'),
            'date' => trans('invoice.date'),
            'due_date' => trans('invoice.due_date'),
            'due_date_detail' => trans('invoice.due_date'),
        ];
    }

    /**
     * Custom message for fields.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'due_date_detail.required_if' => trans('validation.required', ['attribute' => trans('invoice.due_date')])
        ];
    }
}
