<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyRequest extends FormRequest
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
            'symbol' => 'required',
            'position' => 'required|in:prefix,suffix',
            'decimal_place' => 'required|integer|min:0|max:4'
        ];

        if ($this->method() === 'POST') {
            $rules['name'] = 'required|unique:currencies';
        } elseif ($this->method() === 'PATCH') {
            $rules['name'] = 'required|unique:currencies,name,'.$id.',id';
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
            'name' => trans('currency.name'),
            'symbol' => trans('currency.symbol'),
            'position' => trans('currency.position'),
            'decimal_place' => trans('currency.decimal_place'),
        ];
    }
}
