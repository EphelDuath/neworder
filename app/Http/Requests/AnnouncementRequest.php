<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
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
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'audience' => 'required|in:customer,staff',
            'designation_id' => 'array|required_if:audience,staff'
        ];
        
        if ($this->method() === 'POST') {
            $rules['title'] = 'required|unique:announcements';
        } elseif ($this->method() === 'PATCH') {
            $rules['title'] = 'required|unique:announcements,title,'.$id.',id';
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
            'title' => trans('announcement.title'),
            'start_date' => trans('announcement.start_date'),
            'end_date' => trans('announcement.end_date'),
            'audience' => trans('announcement.audience'),
            'designation_id' => trans('announcement.designation'),
        ];
    }
}
