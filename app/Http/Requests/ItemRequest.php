<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
            'item_category_id' => 'required',
            'discount' => 'numeric|min:0'
        ];
        
        if ($this->method() === 'POST') {
            $rules['name'] = 'required|unique:items';
            $rules['code'] = 'required|unique:items';
        } elseif ($this->method() === 'PATCH') {
            $rules['name'] = 'required|unique:items,name,'.$id.',id';
            $rules['code'] = 'required|unique:items,code,'.$id.',id';
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
            'name' => trans('item.item_name'),
            'code' => trans('item.item_code'),
            'item_category_id' => trans('item.item_category'),
            'discount' => trans('item.discount'),
        ];
    }
}
