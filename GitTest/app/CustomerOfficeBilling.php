<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOfficeBilling extends Model
{
    use SoftDeletes;
    protected $table = "customer_office_billings";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'name',
        'zip',
        'pref',
        'town',
        'street',
        'house',
        'tel',
        'fax',
        'people_name',
        'position',
        'dept',
        'email'
    ];

    public function office()
    {
        return $this->hasOne('App\CustomerOffice', 'id', 'customer_office_id');
    }

    public function validate()
    {
        
        $rules = [
            'name'           => ['required', 'max:256'],
            'zip'       => ['required', 'max:256'],
            'pref'      => ['required', 'max:4'],
            'town'      => ['required', 'max:256'],
            'street'    => ['required', 'max:256'],
            'house'     => ['nullable', 'max:256'],
            'tel'       => ['required', 'max:256'],
            'fax'       => ['nullable', 'max:256'],
            'people_name'    => ['required', 'max:256'],
            'position'       => ['nullable', 'max:256'],
            'dept'       => ['nullable', 'max:256'],
            'email'       => ['nullable', 'max:256']
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
