<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/06/20
 * Time: 16:12
 */

namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Auth;
use App\Models\ScheduleParticipant;
class Schedule extends Model
{
    // 2020-10-26 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "schedules";
    protected $guarded = ['id'];
    protected $appends = ['invite'];

    public function scheduleParticipants()
    {
        return $this->hasMany('App\Models\ScheduleParticipant', 'schedule_id', 'id');
    }

    public function scheduleSubs()
    {
        return $this->hasMany('App\Models\ScheduleSub', 'relation_id', 'id');
    }

    public function getInviteAttribute()
    {
        $enterpriseId = EnterpriseParticipant::where('user_id',Auth::id())
            ->pluck('enterprise_id')->toArray();
        $invite = User::whereIn('enterprise_id',$enterpriseId)->where('id',$this->created_user_id)->count('id');

        $fromUsers = ChatContact::where('to_user_id',Auth::id())
            ->pluck('from_user_id')->toArray();
        $enterprise = User::whereIn('id',$fromUsers)->pluck('enterprise_id')->toArray();
        $chatContact = User::whereIn('enterprise_id',$enterprise)->where('enterprise_id','!=',Auth::user()->enterprise_id)->where('id',$this->created_user_id)->count('id');
        $invite = $invite + $chatContact;
        return $invite;
    }

    public static function boot()
    {
        parent::boot();
        static::deleted(function ($schedule) {
            error_log(print_r('-----schedule_deleted-----', true));
            foreach ($schedule->scheduleParticipants as $scheduleParticipants) {
                $scheduleParticipants->delete();
            }
            foreach ($schedule->scheduleSubs as $scheduleSubs) {
                $scheduleSubs->delete();
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'scheduleparticipants', 'schedule_id', 'user_id');
    }

    public function createBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_user_id');
    }

    public function updateBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_user_id');
    }

    /**
     * ??????????????????
     * @param   string  $st_datetime    ????????????
     * @param   string  $ed_datetime    ????????????
     * @param   string  $type_id    ??????ID
     * @param   string  $subject    ?????????
     * @param   string  $comment    ??????
     * @param   string  $open_range ????????????
     * @param   string  $notify     ????????????
     * @param   int     $notify_min_ago ???????????????
     * @param   array   $participants ?????????
     * @param   int     $updated_user_id ????????????ID
     * @param   string  $idea       ?????????????????????????????????id
     */
    static function setOneDay($st_datetime, $ed_datetime, $type_id, $subject, $comment
        , $open_range, $notify, $notify_min_ago, $all_day,$participants, $updated_user_id, $location, $id = null) {
        if($id) {
            // update
            DB::table('schedules')
                ->where('id', $id)
                ->update([
                    'st_datetime'=> $st_datetime,
                    'ed_datetime'=> $ed_datetime,
                    'type'       => $type_id,
                    'subject'    => $subject,
                    'comment'    => $comment,
                    'repeat_kbn' => '0',
                    'open_range' => $open_range,
                    'notify'     => $notify,
                    'notify_min_ago'=> $notify_min_ago,
                    'updated_at' => date('Y/m/d H:i:s'),
                    'updated_user_id' => $updated_user_id,
                    'all_day'=>$all_day,
                    'location'=>$location,
                    'week1'=>null,
                    'week2'=>null,
                    'week3'=>null,
                    'week4'=>null,
                    'week5'=>null,
                    'week6'=>null,
                    'week7'=>null,
                ]);
        } else {
            $id = DB::table('schedules')->insertGetId([
                'st_datetime'=> $st_datetime,
                'ed_datetime'=> $ed_datetime,
                'type'       => $type_id,
                'subject'    => $subject,
                'comment'    => $comment,
                'repeat_kbn' => '0',
                'open_range' => $open_range,
                'notify'     => $notify,
                'notify_min_ago'=> $notify_min_ago,
                'created_at' => date('Y/m/d H:i:s'),
                'created_user_id' => $updated_user_id,
                'all_day'=>$all_day,
                'location'=>$location,
            ]);
        }
        //#4865
        Schedule::updateScheduleParticipants($participants, $id);
        return $id;
    }


    /**
     * ??????????????????????????????
     * @param   string  $st_datetime    ????????????
     * @param   string  $ed_datetime    ????????????
     * @param   string  $type_id    ??????ID
     * @param   string  $subject    ?????????
     * @param   string  $comment    ??????
     * @param   string  $st_span    ???????????????????????????
     * @param   string  $ed_span    ???????????????????????????
     * @param   string  $daydiff    ????????????
     * @param   string  $open_range ????????????
     * @param   string  $notify     ????????????
     * @param   int     $notify_min_ago ???????????????
     * @param   array   $participants ?????????
     * @param   int     $updated_user_id ????????????ID
     * @param   string  $idea       ?????????????????????????????????id
     */
    static function setEveryDay($st_datetime, $ed_datetime, $type_id, $subject, $comment
        , $st_span, $ed_span, $daydiff, $open_range, $notify, $notify_min_ago
        , $all_day,$participants, $updated_user_id, $location,$id=null) {
        if($id) {
            // update
            DB::table('schedules')
                ->where('id', $id)
                ->update([
                    'st_datetime'=> $st_datetime,
                    'ed_datetime'=> $ed_datetime,
                    'type'       => $type_id,
                    'subject'    => $subject,
                    'comment'    => $comment,
                    'repeat_kbn' => '1',
                    'st_span'    => $st_span,
                    'ed_span'    => $ed_span,
                    'open_range' => $open_range,
                    'notify'     => $notify,
                    'notify_min_ago'=> $notify_min_ago,
                    'updated_at' => date('Y/m/d H:i:s'),
                    'updated_user_id' => $updated_user_id,
                    'all_day'=>$all_day,
                    'location'=>$location,
                    'week1'=>null,
                    'week2'=>null,
                    'week3'=>null,
                    'week4'=>null,
                    'week5'=>null,
                    'week6'=>null,
                    'week7'=>null,

                ]);
        } else {
            $id = DB::table('schedules')->insertGetId([
                'st_datetime'=> $st_datetime,
                'ed_datetime'=> $ed_datetime,
                'type'       => $type_id,
                'subject'    => $subject,
                'comment'    => $comment,
                'repeat_kbn' => '1',
                'st_span'    => $st_span,
                'ed_span'    => $ed_span,
                'open_range' => $open_range,
                'notify'     => $notify,
                'notify_min_ago'=> $notify_min_ago,
                'created_at' => date('Y/m/d H:i:s'),
                'created_user_id' => $updated_user_id,
                'all_day'=>$all_day,
                'location'=>$location,
            ]);
        }
        $st_span_ts = strtotime($st_span);
        for ($i = 0; $i <= $daydiff; $i++) {
            $day = "+{$i} day";
            DB::table('schedulesubs')->insert([
                'relation_id'=> $id,
                's_date'     => date("Y-m-d", strtotime($day, $st_span_ts)),
                'created_at' => date('Y/m/d H:i:s'),
                'updated_at' => date('Y/m/d H:i:s')
            ]);
        }
        // #4865
        Schedule::updateScheduleParticipants($participants, $id);
        return $id;
    }


    /**
     * ??????????????????????????????
     * @param   string  $st_datetime    ????????????
     * @param   string  $ed_datetime    ????????????
     * @param   string  $type_id    ??????ID
     * @param   string  $subject    ?????????
     * @param   string  $comment    ??????
     * @param   string  $st_span    ???????????????????????????
     * @param   string  $ed_span    ???????????????????????????
     * @param   string  $monthdiff    ????????????
     * @param   string  $open_range ????????????
     * @param   string  $notify     ????????????
     * @param   int     $notify_min_ago ???????????????
     * @param   array   $participants ?????????
     * @param   int     $updated_user_id ????????????ID
     * @param   string  $id       ?????????????????????????????????id
     */
    static function setEveryMonth($st_datetime, $ed_datetime, $type_id, $subject, $comment
        , $st_span, $ed_span, $monthdiff, $open_range, $notify, $notify_min_ago
        , $all_day, $participants, $updated_user_id, $location,$id=null) {
        if($id) {
            // update
            DB::table('schedules')
                ->where('id', $id)
                ->update([
                    'st_datetime'=> $st_datetime,
                    'ed_datetime'=> $ed_datetime,
                    'type'       => $type_id,
                    'subject'    => $subject,
                    'comment'    => $comment,
                    'repeat_kbn' => '3',
                    'st_span'    => $st_span,
                    'ed_span'    => $ed_span,
                    'open_range' => $open_range,
                    'notify'     => $notify,
                    'notify_min_ago'=> $notify_min_ago,
                    'updated_at' => date('Y/m/d H:i:s'),
                    'updated_user_id' => $updated_user_id,
                    'all_day'=>$all_day,
                    'location'=>$location,
                    'week1'=>null,
                    'week2'=>null,
                    'week3'=>null,
                    'week4'=>null,
                    'week5'=>null,
                    'week6'=>null,
                    'week7'=>null,
                ]);
        } else {
            $id = DB::table('schedules')->insertGetId([
                'st_datetime'=> $st_datetime,
                'ed_datetime'=> $ed_datetime,
                'type'       => $type_id,
                'subject'    => $subject,
                'comment'    => $comment,
                'repeat_kbn' => '3',
                'st_span'    => $st_span,
                'ed_span'    => $ed_span,
                'open_range' => $open_range,
                'notify'     => $notify,
                'notify_min_ago'=> $notify_min_ago,
                'created_at' => date('Y/m/d H:i:s'),
                'created_user_id' => $updated_user_id,
                'all_day'=>$all_day,
                'location'=>$location,

            ]);
        }
        $st_span_ts = strtotime($st_span);
        $first_ts = strtotime('first day of ' . $st_span);
        for ($i = 0; $i <= $monthdiff; $i++) {
            $month = "+{$i} month";
            $sDate = date("Y-m-d", strtotime($month, $st_span_ts));
            $first = date("Y-m-d", strtotime($month, $first_ts));
            $sDateMonth = date("Y-m", strtotime($month, $st_span_ts));
            $firstMonth = date("Y-m", strtotime($month, $first_ts));
            if ($sDateMonth === $firstMonth) {
                DB::table('schedulesubs')->insert([
                    'relation_id'=> $id,
                    's_date'     => $sDate,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
                ]);
            }
        }
        //#4865
        Schedule::updateScheduleParticipants($participants, $id);
        return $id;
    }

    /**
     * ?????????????????????????????????????????????
     * @param   string  $st_datetime    ????????????
     * @param   string  $ed_datetime    ????????????
     * @param   string  $type_id    ??????ID
     * @param   string  $subject    ?????????
     * @param   string  $comment    ??????
     * @param   string  $st_span    ???????????????????????????
     * @param   string  $ed_span    ???????????????????????????
     * @param   string  $daydiff    ????????????
     * @param   string  $weekArray  ??????????????????
     * @param   string  $open_range ????????????
     * @param   string  $notify     ????????????
     * @param   int     $notify_min_ago ???????????????
     * @param   array   $participants ?????????
     * @param   int     $updated_user_id ????????????ID
     * @param   string  $id       ?????????????????????????????????id
     */
    static function setWeek($st_datetime, $ed_datetime, $type_id, $subject, $comment
        , $st_span, $ed_span, $daydiff, $weekArray, $open_range, $notify, $notify_min_ago
        ,$all_day, $participants, $updated_user_id, $location, $id=null) {
        if (!$st_span || !$ed_span) {
            return;
        }
        if ($id) {
            // update
            DB::table('schedules')
                ->where('id', $id)
                ->update([
                    'st_datetime'=> $st_datetime,
                    'ed_datetime'=> $ed_datetime,
                    'type'       => $type_id,
                    'subject'    => $subject,
                    'comment'    => $comment,
                    'repeat_kbn' => '4',
                    'st_span'    => $st_span,
                    'ed_span'    => $ed_span,
                    'week1'      => $weekArray[1],
                    'week2'      => $weekArray[2],
                    'week3'      => $weekArray[3],
                    'week4'      => $weekArray[4],
                    'week5'      => $weekArray[5],
                    'week6'      => $weekArray[6],
                    'week7'      => $weekArray[7],
                    'open_range' => $open_range,
                    'notify'     => $notify,
                    'notify_min_ago'=> $notify_min_ago,
                    'updated_at' => date('Y/m/d H:i:s'),
                    'updated_user_id' => $updated_user_id,
                    'all_day'=>$all_day,
                    'location'=>$location,
                ]);
        } else {
            $id = DB::table('schedules')->insertGetId([
                'st_datetime'=> $st_datetime,
                'ed_datetime'=> $ed_datetime,
                'type'       => $type_id,
                'subject'    => $subject,
                'comment'    => $comment,
                'repeat_kbn' => '4',
                'st_span'    => $st_span,
                'ed_span'    => $ed_span,
                'week1'      => $weekArray[1],
                'week2'      => $weekArray[2],
                'week3'      => $weekArray[3],
                'week4'      => $weekArray[4],
                'week5'      => $weekArray[5],
                'week6'      => $weekArray[6],
                'week7'      => $weekArray[7],
                'open_range' => $open_range,
                'notify'     => $notify,
                'notify_min_ago'=> $notify_min_ago,
                'created_at' => date('Y/m/d H:i:s'),
                'created_user_id' => $updated_user_id,
                'all_day'=>$all_day,
                'location'=>$location,
            ]);
        }
        $min = strtotime($st_span);
        $max = strtotime($ed_span);

        for ($i = $min; $i <= $max; $i = strtotime('1 day', $i)) {
            $date = date('Y/m/d', $i);
            $week = date('w', $i) + 1;

            if ($weekArray[$week] == 1) {
                DB::table('schedulesubs')->insert([
                    'relation_id'=> $id,
                    's_date'     => $date,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
                ]);
            }
        }
        //#4865
        Schedule::updateScheduleParticipants($participants, $id);
        return $id;
    }
    /**
     * ??????????????????????????????
     * @param   int     $id        ??????????????????ID
     * @param   int     $user_id
     * @param   string  $edSpan    ????????????
     */
    static function updateEdSpan($id, $user_id, $edSpan) {
        $ed_datetime = DB::table('schedules')
            ->where('id', $id)->whereNull('deleted_at')->value('ed_datetime');
        DB::table('schedules')
            ->where('id', $id)
            ->update(['ed_span'=> $edSpan
                ,'updated_user_id'=> $user_id
                , 'updated_at' => date('Y/m/d H:i:s')
                , 'ed_datetime' => $edSpan . ' ' . date('H:i:s', strtotime($ed_datetime))]);
    }

    /**
     * @param $participants
     * @param $db_st_span
     * @param $st_span
     * @param $ed_span
     * @param $id
     */
    static function updateScheduleParticipants($participants, $id)
    {
        ScheduleParticipant::where('schedule_id', '=', $id)->delete();
        foreach ($participants as $user_id) {
            //????????????
            DB::table('scheduleparticipants')->insert([
                'user_id' => $user_id,
                'schedule_id' => $id,
                'created_at' => date('Y/m/d H:i:s'),
                'updated_at' => date('Y/m/d H:i:s')
            ]);

        }
    }


}
