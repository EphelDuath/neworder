<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserProfileRequest extends FormRequest
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
        $type = $this->route('type');

        $rules = [
            'first_name' => 'sometimes|required',
            'last_name' => 'sometimes|required',
            'gender' => 'sometimes|required',
            'date_of_birth' => 'date_format:Y-m-d|nullable',
            'date_of_anniversary' => 'after:date_of_birth|date_format:Y-m-d|nullable',
            'country_id' => 'sometimes|required',
            'phone' => 'sometimes|required'
        ];

        if ($type === 'staff') {
            $rules['role_id'] = 'required';
            $rules['designation_id'] = 'required';
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
        return  [
            'first_name' => trans('user.first_name'),
            'last_name' => trans('user.last_name'),
            'gender' => trans('user.gender'),
            'date_of_birth' => trans('user.date_of_birth'),
            'date_of_anniversary' => trans('user.date_of_anniversary'),
            'designation_id' => trans('designation.designation'),
            'role_id' => trans('role.role'),
            'country_id' => trans('user.country'),
            'phone' => trans('user.phone')
        ];
    }
}
