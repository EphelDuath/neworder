<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
            'opening_balance' => 'required|numeric',
            'type' => 'required|in:bank,cash',
            'number' => 'required_if:type,bank',
            'bank_name' => 'required_if:type,bank'
        ];

        if ($this->method() === 'POST') {
            $rules['name'] = 'required|unique:accounts';
        } elseif ($this->method() === 'PATCH') {
            $rules['name'] = 'required|unique:accounts,name,'.$id.',id';
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
            'name' => trans('account.name'),
            'opening_balance' => trans('account.opening_balance'),
            'type' => trans('account.type'),
            'number' => trans('account.number'),
            'bank_name' => trans('account.bank_name')
        ];
    }
}
