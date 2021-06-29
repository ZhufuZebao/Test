<?php
/**
 * Created by PhpStorm.
 * User: P0123443
 * Date: 2019/07/09
 * Time: 14:25
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatLastRead extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes;//Turn on softDeletes
    protected $table = "chatlastreads";

    public function chatMsgs()
    {
        return $this->hasOne('App\Models\ChatMessage', 'id', 'message_id');
    }

    public function users()
    {
        return $this->hasOne('App\Models\Users', 'id', 'user_id');
    }

    public function groups()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }
}
