<?php
/**
 * アカウント管理テーブル
 *
 * @author  WuJi
 */

namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;


class Dashboard extends Model
{
    use SoftDeletes; // Turn on soft delete
    protected $table = "dashboards";
    protected $primaryKey = "id";
    protected $fillable = [
        'title','from_user_id', 'to_user_id','related_id','type','content'
    ];
    protected $dates = ['deleted_at'];

    public function fromUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'from_user_id')
            ->select('id', 'name');
    }

    public function toUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'to_user_id')
            ->select('id', 'name');
    }

    public function chatMsg()
    {
        return $this->hasOne('App\Models\ChatMessage', 'id', 'related_id')
            ->select('id','message','file_name');
    }

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'related_id')
            ->select('id','place_name');
    }

    public function schedule()
    {
        return $this->hasOne('App\Models\Schedule', 'id', 'related_id')
            ->select('id','subject');
    }

    public function dashboardValidate()
    {
        $rules = [
            'title' => ['required', 'max:60'],
            'related_id' => ['required'],
            'type' => ['required'],
            'from_user_id' => ['required'],
            'to_user_id' => ['required']
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
}
