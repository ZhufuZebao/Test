<?php
/**
 *
 *
 * @author  Liyanlin
 */

namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Illuminate\Support\Facades\Auth;

class CustomerOffice extends Model
{
    // 2020-10-22 #2298 Turn on softDeletes
    use SoftDeletes;
    protected $table = "customer_offices";
    protected $guarded = ['id'];
    public $keyword;

    public function init($params)
    {
        $this->keyword = $params;
    }

    public function search($ids)
    {
        $q = CustomerOffice::whereHas('customer',function ($q1){
            $q1->where('enterprise_id',Auth::user()->enterprise_id);
        })->with("customer",'people');
        if ($ids){
            $q->whereNotIn('id',$ids);
        }
        if (strlen($this->keyword) > 0) {
            $q->where(function ($q1){
                $q1->whereHas('customer', function ($q2) {
                    $q2->where('name', 'LIKE', "%{$this->keyword}%");
                })->orWhere('name', 'LIKE', "%{$this->keyword}%");
            });
        }
        return $q->orderBy('customer_id')->get();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id', 'id');
    }


    public function people()
    {
        return $this->hasMany('App\Models\CustomerOfficePeople', 'customer_office_id', 'id');
    }

    public function billings()
    {
        return $this->hasMany('App\Models\CustomerOfficeBilling', 'customer_office_id', 'id');
    }

    /* public function people()
     * {
     *     return $this->hasMany('App\Customer', 'id', 'customer_id');
     * }*/

    public function validate()
    {

        $rules = [
            'zip' => ['required', 'numeric','digits_between:1,7'],
            'pref' => ['required', 'max:4'],
            'town' => ['required', 'max:30'],
            'street' => ['required', 'max:20'],
            'tel' => ['required', 'max:256'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

    public static function boot()
    {
        parent::boot();
        // 事業所を削除したら担当者と請求先も削除
        static::deleted(function ($office) {
            error_log(print_r('-----office_deleted-----', true));
            foreach ($office->billings as $billing) {
                $billing->delete();
            }
            foreach ($office->people as $person) {
                $person->delete();
            }
        });
    }
}
