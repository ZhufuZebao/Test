<?php
/**
 * Created by PhpStorm.
 * User: P0123443
 * Date: 2019/07/04
 * Time: 19:20
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatGroup extends Model
{

    // 2020-10-26 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chatgroups";
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne('App\Models\Account', 'id', 'user_id');
    }

    public function mine()
    {
        return $this->hasOne('App\Models\ChatGroup', 'group_id', 'group_id');
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    public function account()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function messages()
    {
        return $this->belongsTo('App\Models\ChatMessage', 'group_id', 'group_id');
    }
}
