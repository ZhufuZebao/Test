<?php
/**
 * Created by PhpStorm.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatFile extends Model
{

    // 2020-10-26 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chat_files";
    protected $fillable = [
        'id', 'upload_user_id', 'group_id', 'file_name', 'file_size'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'upload_user_id');
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    public function userDeleted()
    {
        return $this->hasOne('App\Models\Account', 'id', 'upload_user_id')->withTrashed();
    }
}
