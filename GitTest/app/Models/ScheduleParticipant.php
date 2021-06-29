<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/06/20
 * Time: 16:12
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleParticipant extends Model
{
    // 2020-10-26 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "scheduleparticipants";
    protected $guarded = ['id'];

    public function schedules()
    {
        return $this->belongsTo('App\Models\Schedule', 'schedule_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\Account', 'id', 'user_id');
    }

    /**
     *  #1992 users ユーザを削除するデータを含む
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userInfo()
    {
        return $this->hasOne('App\Models\Account', 'id', 'user_id')->withTrashed();
    }
}
