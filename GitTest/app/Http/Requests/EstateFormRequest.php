<?php

namespace App\Http\Requests;

class EstateFormRequest extends \Illuminate\Foundation\Http\FormRequest
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
        $rules = \App\Estate::rules();

        if($this->exists('zip2addr'))
        {
            //$rules['zip2addr_target'] = ['nullable','max:256'];
        }

        return $rules;

    }

    /**
     * set default values
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $input = $this->all();

        $input['user_id'] = \Illuminate\Support\Facades\Auth::user()->id;

        if(! isset($input['floor_area']))
            $input['floor_area']  = 0;

        if(! isset($input['floor_level']))
            $input['floor_level'] = 0;

        if(! isset($input['renovate_flg']))
            $input['renovate_flg'] = 0;
        
        $this->replace($input);
    }

}
