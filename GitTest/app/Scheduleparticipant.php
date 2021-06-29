<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Scheduleparticipant extends Model
{

    /**
     * 参加者のスケジュール削除
     */
    static function deleteFromId($id, $userId) {
        DB::table('scheduleparticipants')->where([
            ['schedule_id', '=', $id],
            ['user_id', '=', $userId],])->delete();
    }
    /**
     * 参加者のスケジュール削除
     */
    static function deleteByScheduleId($id) {
        DB::table('scheduleparticipants')->where('schedule_id', '=', $id)->delete();
    }
}
