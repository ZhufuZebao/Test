<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/07/05
 * Time: 17:28
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class ChatMessage extends Model
{
    // 2020-10-26 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chatmessages";
    protected $guarded = ['id'];
    protected $primaryKey = "id";
    protected $fillable = [
        'group_id', 'from_user_id', 'message'
    ];
    public function user()
    {
        return $this->hasOne('App\Models\Account', 'id', 'from_user_id');
    }
    public function userDeleted()
    {
        return $this->hasOne('App\Models\Account', 'id', 'from_user_id')->withTrashed();
    }
    public function lastRead()
    {
        return $this->hasMany('App\Models\ChatLastRead', 'group_id', 'group_id');
    }
    public function project(){
        return $this->hasOne('App\Models\Project', 'group_id', 'group_id');
    }

    public function validate()
    {
        $rules = [
            'message'=> ['max:65535'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
}
