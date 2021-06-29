<?php
/**
 * Created by PhpStorm.
 * User: P0128147
 * Date: 2019/10/12
 * Time: 10:51
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWorkarea extends Model
{
    use SoftDeletes;
    protected $table = "user_workareas";
    protected $guarded = ['id'];

    public function accounts()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }

    public function workareas()
    {
        return $this->hasOne('App\Models\Workarea','id','area_id');
    }

    public function workplaces()
    {
        return $this->hasOne('App\Models\Workplace','id','place_id');
    }
}