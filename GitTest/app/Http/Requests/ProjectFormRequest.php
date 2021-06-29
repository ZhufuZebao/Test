<?php

namespace App\Http\Requests;

class ProjectFormRequest extends \Illuminate\Foundation\Http\FormRequest
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
        $rules = \App\Project::rules();

        $rules['image'] = ['nullable','file','mimes:jpeg,jpg,png,gif','max:10000']; // max 10 Mb

        return $rules;
    }

}
