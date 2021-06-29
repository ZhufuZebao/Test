<?php
/**
 *
 * @author  Liyanlin
 */

namespace App\Models;

use DB;

class CustomerRole extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "customer_roles";

    public $primaryKey = "id";

    public $fillable = [
        'name'
    ];

    public function people()
    {
        return $this->belongsTo('App\Models\CustomerOfficePeople', 'id', 'role');
    }
}
