<?php
/**
 *
 *
 * @author  Liyanlin
 */

namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOfficePeople extends Model
{
    // 2020-10-22 #2298 Turn on softDeletes
    use SoftDeletes;
    protected $table = "customer_office_people";
    public $primaryKey = "id";

    protected $casts = [
    ];

    protected $guarded = ['id'];

    public function customerRole()
    {
        return $this->hasOne('App\Models\CustomerRole', 'id', 'role');
    }

    public function office()
    {
        return $this->hasOne('App\Models\CustomerOffice', 'id', 'customer_office_id');
    }

    public function validate()
    {

        $rules = [
            'name' => ['required', 'max:256'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
