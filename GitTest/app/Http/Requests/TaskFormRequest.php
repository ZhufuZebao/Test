<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskFormRequest extends FormRequest
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
        
        return [
            'name'          => ['required', 'max:64'],
            'st_date'       => ['required', 'before_or_equal:ed_date'],
            'ed_date'       => ['required', 'after_or_equal:st_date'],
            'parent_id'     => ['nullable', 'exists:tasks,id'],
            'note'          => ['nullable', 'max:128'],
        ];
    }
}
