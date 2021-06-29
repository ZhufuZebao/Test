<?php
/**
 * Created by PhpStorm.
 * User: P0123443
 * Date: 2019/07/04
 * Time: 19:01
 */

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ChatPerson extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes;//Turn on softDeletes
    protected $table = "chatpersons";

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
