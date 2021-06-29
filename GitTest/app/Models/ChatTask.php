<?php
/**
 * @author goki
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class ChatTask extends Model
{
    // 2020-10-22 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chattasks";
    protected $primaryKey = "id";
    protected $guarded = 'id';

    protected $fillable = [
        'group_id', 'create_user_id', 'limit_date', 'note', 'complete_date', 'id'
    ];

    public function chattaskcharges()
    {
        return $this->hasMany('App\Models\ChatTaskCharge', 'task_id', 'id');

    }
    public function users()
    {
        return $this->hasMany('App\Models\User', 'id', 'create_user_id');

    }


    public function groups()
    {
        return $this->hasMany('App\Models\Group', 'id', 'group_id');

    }

    public function chatmessages()
    {
        return $this->hasOne('App\Models\ChatMessage', 'id', 'message_id');
    }

    public function taskValidate()
    {
        $rules = [
            'limit_date' => ['required'],
            ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
