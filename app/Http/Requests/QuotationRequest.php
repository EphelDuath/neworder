<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
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
            'quotation_number' => 'required',
            'quotation_prefix' => 'required',
            'reference_number' => 'required',
            'currency_id' => 'required',
            'item_type' => 'required',
        ];

        if (! $is_draft) {
            $rules['customer_id'] = 'required';
            $rules['date'] = 'required|date';
            $rules['expiry_date'] = 'required';
            $rules['expiry_date_detail'] = 'required_if:expiry_date,expiry_on_date|date|after_or_equal:date';
            $rules['subject'] = 'required';
            $rules['description'] = 'required';
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
            'quotation_number' => trans('quotation.quotation_number'),
            'quotation_prefix' => trans('quotation.prefix'),
            'reference_number' => trans('quotation.reference_number'),
            'currency_id' => trans('currency.currency'),
            'item_type' => trans('quotation.item_type'),
            'customer_id' => trans('user.customer'),
            'date' => trans('quotation.date'),
            'expiry_date' => trans('quotation.expiry_date'),
            'expiry_date_detail' => trans('quotation.expiry_date'),
            'subject' => trans('quotation.subject'),
            'description' => trans('quotation.description'),
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
            'expiry_date_detail.required_if' => trans('validation.required', ['attribute' => trans('quotation.expiry_date')])
        ];
    }
}
