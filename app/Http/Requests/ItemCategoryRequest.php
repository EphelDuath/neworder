<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemCategoryRequest extends FormRequest
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
            'type' => 'required|in:product,service'
        ];

        if ($this->method() === 'POST') {
            $rules['name'] = 'required|unique:item_categories';
        } elseif ($this->method() === 'PATCH') {
            $rules['name'] = 'required|unique:item_categories,name,'.$id.',id';
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
            'name' => trans('item.item_category_name'),
            'type' => trans('item.item_type'),
        ];
    }
}
