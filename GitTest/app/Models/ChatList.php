<?php
/**
 * Created by PhpStorm.
 * User: P0123443
 * Date: 2019/07/11
 * Time: 17:36
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatList extends Model
{
    // 2020-10-26 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chatlists";
    protected $fillable = [
        'owner_id', 'user_id', 'group_id', 'top'
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'id', 'user_id');
    }

    public function group(){
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    public function account(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
