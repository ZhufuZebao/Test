<?php

namespace App\Http\Requests;

class JobVacancyFormRequest extends \Illuminate\Foundation\Http\FormRequest
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

    public function rules()
    {
        return \App\JobVacancy::rules();
    }

    protected function prepareForValidation()
    {
    }

    protected function failedValidation($validator)
    {
    }
}
