<?php

namespace App\Http\Requests;

class CustomerOfficePersonFormRequest extends \Illuminate\Foundation\Http\FormRequest
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
        if($this->exists('zip2addr'))
            return [
                'billing_zip'     => ['required','max:256'],
            ];

        return [
            'name'            => ['required', 'max:256'],
            'position'        => ['nullable', 'max:256'],
            'dept'            => ['nullable', 'max:256'],
            'customer_role_id'=> ['nullable', 'exists:customer_roles,id'],
            'bill_here'       => ['boolean'],
            'billing_name'    => ['required_if:bill_here,!=,0', 'max:256'],
            'billing_dept'    => ['required_if:bill_here,!=,0', 'max:256'],
            'billing_person'  => ['required_if:bill_here,!=,0', 'max:256'],
            'billing_zip'     => ['required_if:bill_here,!=,0', 'max:256'],
            'billing_pref_id' => ['required_if:bill_here,!=,0', 'exists:prefs,id'],
            'billing_town'    => ['required_if:bill_here,!=,0', 'max:256'],
            'billing_street'  => ['required_if:bill_here,!=,0', 'max:256'],
            'billing_tel'     => ['required_if:bill_here,!=,0', 'max:256'],
            'billing_house'   => ['nullable', 'max:256'],
            'billing_fax'     => ['nullable', 'max:256'],
        ];
    }

    /**
     * set default values
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $input = $this->all();

        if(! isset($input['customer_role_id']))
            $input['customer_role_id']  = null;

        if('on' == isset($input['bill_here']))
            $input['bill_here']  = 1;
        else
            $input['bill_here']  = 0;
        
        $this->replace($input);
    }

}
