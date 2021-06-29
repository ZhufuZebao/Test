<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOfficePerson extends Model
{
    use SoftDeletes;
    protected $table = "customer_office_people";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'name',
        'position',
        'dept',
        'role',
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
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
