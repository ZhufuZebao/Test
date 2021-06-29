<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobOfferCreateRequest extends FormRequest
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

    public function attributes()
    {
        return [
            'vacancy_id' => "求人",
            'content'    => "メッセージ",
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(request()->isMethod('get'))
        {
            return [];
        }

        return [
            'content'    => ['required','string','min:2','max:20480'],
            'vacancy_id' => ['required','exists:job_vacancies,id'],
        ];
    }

}
