<?php
/**
 *
 *
 * @author  Liyanlin
 */

namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOfficeBilling extends Model
{
    // 2020-10-22 #2298 Turn on softDeletes
    use SoftDeletes;
    protected $table = "customer_office_billings";
    public $primaryKey = "id";

    protected $casts = [
    ];

    protected $guarded = ['id'];

    public function office()
    {
        return $this->hasOne('App\Models\CustomerOffice', 'id', 'customer_office_id');
    }

    public function validate()
    {

        $rules = [
            'name' => ['required', 'max:256'],
            'zip' => ['required', 'max:8'],
            'pref' => ['required', 'max:4'],
            'town' => ['required', 'max:30'],
            'street' => ['required', 'max:20'],
            'tel' => ['required', 'max:64'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
