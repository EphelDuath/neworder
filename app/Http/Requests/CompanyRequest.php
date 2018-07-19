<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'email' => 'required|email',
            'website' => 'required|url',
            'country_id' => 'required'
        ];

        if ($this->method() === 'POST') {
            $rules['name'] = 'required|unique:companies';
        } elseif ($this->method() === 'PATCH') {
            $rules['name'] = 'required|unique:companies,name,'.$id.',id';
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
            'name' => trans('company.name'),
            'email' => trans('company.email'),
            'website' => trans('company.website'),
            'country_id' => trans('company.country'),
        ];
    }
}
