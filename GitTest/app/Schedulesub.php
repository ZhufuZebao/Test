<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class Schedulesub extends Model {

    /**
     * スケジュール削除
     */
    static function deleteSub($id, $subId) {
        DB::table('schedulesubs')->where([
            ['id', '=', $subId],
            ['relation_id', '=', $id],])->delete();
    }

    /**
     * スケジュール削除
     */
    static function deleteByScheduleId($id) {
        DB::table('schedulesubs')->where('relation_id', '=', $id)->delete();
    }

    /**
     * スケジュール削除
     */
    static function deleteAfter($id, $subId) {
         DB::table('schedulesubs')->where([
            ['id', '>=', $subId],
            ['relation_id', '=', $id],])->delete();
    }

    /**
     * 日付けからスケジュール取得
     */
    static function getSubId($id, $targetDate) {
        $subId = DB::table('schedulesubs')->select('id')
            ->where([
                ['relation_id', '=', $id],
                ['s_date', '=', $targetDate],])
                    ->get();
        return isset($subId[0]) ? $subId[0]->id : '0';
    }

    /**
     * スケジュールIDからデータ数を取得
     */
    static function getCount($id) {
        $subCount = DB::table('schedulesubs')
            ->where(['relation_id', '=', $id])
                    ->count();
        return isset($subCount) ? $subCount : 0;
    }
}
