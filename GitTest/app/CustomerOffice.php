<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOffice extends Model
{
    use SoftDeletes;
    protected $table = "customer_offices";
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
    ];

    public function customer()
    {
        return $this->hasOne('App\Customer', 'id', 'customer_id');
    }

    public function people()
    {
        return $this->hasMany('App\CustomerOfficePerson', 'customer_office_id', 'id');
    }

    public function billings()
    {
        return $this->hasMany('App\CustomerOfficeBilling', 'customer_office_id', 'id');
    }

    /* public function people()
     * {
     *     return $this->hasMany('App\Customer', 'id', 'customer_id');
     * }*/

    public function validate()
    {
        
        $rules = [
            'name'      => ['required', 'max:256'],
            'zip'       => ['required', 'max:256'],
            'pref'      => ['required', 'max:4'],
            'town'      => ['required', 'max:256'],
            'street'    => ['required', 'max:256'],
            'house'     => ['nullable', 'max:256'],
            'tel'       => ['required', 'max:256'],
            'fax'       => ['nullable', 'max:256'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

    public static function boot()
    {
        parent::boot();
        // 事業所を削除したら担当者と請求先も削除
        static::deleted(function ($office) {
            error_log(print_r('-----office_deleted-----',true));
            foreach($office->billings as $billing) {
                $billing->delete();
            }
            foreach($office->people as $person) {
                $person->delete();
            }
        });
    }
}
