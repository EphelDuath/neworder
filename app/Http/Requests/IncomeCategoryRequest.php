<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeCategoryRequest extends FormRequest
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

        $rules = [];

        if ($this->method() === 'POST') {
            $rules['name'] = 'required|unique:income_categories';
        } elseif ($this->method() === 'PATCH') {
            $rules['name'] = 'required|unique:income_categories,name,'.$id.',id';
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
            'name' => trans('transaction.transaction_category_name')
        ];
    }
}
