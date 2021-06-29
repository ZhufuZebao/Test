<?php
/**
 *
 * @author  Reiko Mori
 */
namespace App;

use DB;

class CustomerRole extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "customer_roles";

    public $primaryKey = "id";

    public $fillable = [
        'name'
    ];

    public function persons()
    {
        return $this->hasMany('App\CustomerOfficePerson', 'customer_role_id', 'id');
    }
}
