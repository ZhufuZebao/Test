<?php
/**
 * スケジュール管理テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class Schedule extends Model
{
    /**
     * 年月指定で全てのスケジュールデータ取得
     *
     * @param   int     $user_id    ユーザーID
     * @param   int     $target_y   対象年
     * @param   int     $target_m   対象月
     * @return array
     */
    static function getSchedules($user_id, $target_y, $target_m, $target_d='')
    {
        $params = array(
            $user_id
        );

        if ($target_d == '') {
            $where = "and     date_format(ifnull(ss.s_date, s.s_date), '%Y%m') = ?";
            $params[] = sprintf('%04d%02d', $target_y, $target_m);

        } else {
            $where  = "and     date_format(ifnull(ss.s_date, s.s_date), '%Y%m%d') >= ?\n";
            $where .= "and     date_format(ifnull(ss.s_date, s.s_date), '%Y%m%d') <= ?";

            $day  = sprintf('%04d%02d%02d', $target_y, $target_m, $target_d);
            $day2 = date('Ymd', strtotime($day. ' +7 day'));
            $params[] = $day;
            $params[] = $day2;
        }

        $sql = <<<EOF
select  ifnull(ss.id, s.id) as id, s.user_id, ifnull(ss.s_date, s.s_date) as s_date,
        s.st_time, s.ed_time, s.subject, s.comment, s.repeat_kbn,
        s.week1, s.week2, s.week3, s.week4, s.week5, s.week6, s.week7,
        s.st_span, s.ed_span, s.created_at, s.updated_at, ss.relation_id,
        date_format(ifnull(ss.s_date, s.s_date), '%d') as day,
        t.id as type_id, t.name as type_name
from    schedules s
        left join schedulesubs ss
            on  s.id = ss.relation_id
            and s.user_id = ss.user_id
        inner join types t
            on s.type = t.id
where   s.user_id = ?
$where
order by s_date
EOF;

        $schedule = DB::select($sql, $params);

        $rows = array();
        foreach ($schedule as $key => $item) {
            $rows[(int)$item->day][] = [
                    'id'            => $item->id,
                    'relation_id'   => $item->relation_id,
                    'subject'       => $item->subject,
                    'comment'       => $item->comment,
                    'type_name'     => $item->type_name,
                    'st_time'       => $item->st_time,
                    'ed_time'       => $item->ed_time,
            ];
        }

        return $rows;
    }

    /**
     * ID指定でスケジュールのデータ取得
     *
     * @param   int     $user_id    ユーザーID
     * @param   int     $id         スケジュールID
     * @return スケジュール情報
     */
    static function getOneDayScheduleDetail($user_id, $id, $sub_id)
    {
        $params = array($id, $user_id);

        $where = '';
        $group_by = '';
        if ($sub_id != '' && $sub_id != '0') {
            $where = "AND ss.id = ?";
            $params[] = $sub_id;
            $group_by = ', ss.id';
        }

        $sql  = <<< EOF
SELECT s.id
     , IFNULL(ss.id, "") AS sub_id
     , IFNULL(s.subject, "") AS subject
     , IFNULL(s.comment, "") AS comment
     , CONCAT(DATE_FORMAT(IFNULL(ss.s_date, s.st_datetime),'%Y-%m-%d'), " ",
       DATE_FORMAT(s.st_datetime,'%H:%i')) AS start_at
     , CONCAT(DATE_FORMAT(IFNULL(ss.s_date, s.ed_datetime),'%Y-%m-%d'), " ",
       DATE_FORMAT(s.ed_datetime, '%H:%i')) AS end_at
     , s.repeat_kbn
     , IFNULL(s.week1, "") AS week1
     , IFNULL(s.week2, "") AS week2
     , IFNULL(s.week3, "") AS week3
     , IFNULL(s.week4, "") AS week4
     , IFNULL(s.week5, "") AS week5
     , IFNULL(s.week6, "") AS week6
     , IFNULL(s.week7, "") AS week7
     , IF(s.st_span, CONCAT(DATE_FORMAT(s.st_span, '%Y-%m-%d'), " 00:00"), "") AS st_span
     , IF(s.ed_span, CONCAT(DATE_FORMAT(s.ed_span, '%Y-%m-%d'), " 00:00"), "") AS ed_span
     , IFNULL(t.id, 0) AS type
     , IFNULL(t.name, "") AS type_name
     , IFNULL(s.open_range, "0") AS open_range
  FROM schedules s
  LEFT JOIN schedulesubs ss
    ON s.id = ss.relation_id
  LEFT JOIN types t
    ON s.type = t.id
  LEFT JOIN scheduleparticipants sp
 	ON s.id = sp.schedule_id
 WHERE s.id = ?
   AND sp.user_id = ?
  {$where}
EOF;

        $schedule = DB::select($sql, $params);

        $rows = array();
        $item;
        if(isset($schedule) && !empty($schedule)) {
            $item = $schedule[0];
            // 参加者配列取得
            $participants = DB::table('scheduleparticipants')
                            ->select('user_id as id', 'users.name as name')
                            ->leftJoin('users', 'scheduleparticipants.user_id', '=', 'users.id')
                            ->where([
                                ['schedule_id', $id],
                                ['user_id', '<>', $user_id],
                            ])->get();
            $row = [
                    'id'            => $item->id,
                    'subId'         => $item->sub_id,
                    'subject'       => $item->subject,
                    'comment'       => $item->comment,
                    'type'          => $item->type,
                    'typeStr'       => $item->type_name,
                    'mStartAt'      => $item->start_at,
                    'mEndAt'        => $item->end_at,
                    'repeatKbn'     => $item->repeat_kbn,
                    'week1'         => $item->week1,
                    'week2'         => $item->week2,
                    'week3'         => $item->week3,
                    'week4'         => $item->week4,
                    'week5'         => $item->week5,
                    'week6'         => $item->week6,
                    'week7'         => $item->week7,
                    'stSpan'        => $item->st_span,
                    'edSpan'        => $item->ed_span,
                    'openRange'     => $item->open_range,
                    'participants'  => isset($participants) ? $participants :null,
            ];
        }

        return isset($item) ? $row : null;
    }

    /**
     * ID指定でスケジュールのデータ取得
     *
     * @param   int     $user_id    ユーザーID
     * @param   int     $id         スケジュールID
     * @return array
     */
    static function getOneDaySchedules($user_id, $id, $sub_id)
    {
        $params = array($user_id, $id);

        $where = '';
        if ($sub_id != '' && $sub_id != '0') {
            $where = "and  ss.id = ?";
            $params[] = $sub_id;
        }

        $sql  = <<< EOF
select  s.*, ss.relation_id,
        date_format(ifnull(ss.s_date, s.s_date), '%Y') as year,
        date_format(ifnull(ss.s_date, s.s_date), '%m') as month,
        date_format(ifnull(ss.s_date, s.s_date), '%d') as day,
        date_format(s.st_span, '%Y') as st_year,
        date_format(s.st_span, '%m') as st_month,
        date_format(s.st_span, '%d') as st_day,
        date_format(s.ed_span, '%Y') as ed_year,
        date_format(s.ed_span, '%m') as ed_month,
        date_format(s.ed_span, '%d') as ed_day,
        t.id as type_id, t.name as type_name
from    schedules s
        left join schedulesubs ss
            on  s.id = ss.relation_id
            and s.user_id = ss.user_id
        inner join types t
            on s.type = t.id
where   s.user_id = ?
and     s.id = ?
{$where}
EOF;

        $schedule = DB::select($sql, $params);

        return isset($schedule[0]) ? $schedule[0] : null;
    }

    /**
     * ID指定で関連する親のスケジュールデータ取得
     *
     * @param   int     $user_id    ユーザーID
     * @param   int     $id         スケジュールID
     * @return array
     */
    static function getParentSchedules($user_id, $id)
    {
        $sql  = <<< EOF
select  *,
        date_format(s_date, '%Y') as year,
        date_format(s_date, '%m') as month,
        date_format(s_date, '%d') as day,
        date_format(st_span, '%Y') as st_year,
        date_format(st_span, '%m') as st_month,
        date_format(st_span, '%d') as st_day,
        date_format(ed_span, '%Y') as ed_year,
        date_format(ed_span, '%m') as ed_month,
        date_format(ed_span, '%d') as ed_day
from    schedules
where   user_id = ?
and     id = ?
EOF;

        $schedule = DB::select($sql, [
                $user_id,
                $id
        ]);

        return isset($schedule[0]) ? $schedule[0] : null;
    }

    /**
     * １日だけ登録
     * @param   string  $st_datetime    開始日時
     * @param   string  $ed_datetime    終了日時
     * @param   string  $type_id    種別ID
     * @param   string  $subject    見出し
     * @param   string  $comment    内容
     * @param   string  $open_range 公開区分
     * @param   array   $participants 参加者
     * @param   string  $idea       更新時使用スケジュールid
     */
    static function setOneDay($st_datetime, $ed_datetime, $type_id, $subject, $comment
            , $open_range, $participants, $id=null) {
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
                        'created_at' => date('Y/m/d H:i:s'),
                        'updated_at' => date('Y/m/d H:i:s')
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
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
            ]);
        }
        foreach ($participants as $user_id) {
            DB::table('scheduleparticipants')->insert([
                    'user_id'    => $user_id,
                    'schedule_id'=> $id,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
                ]);
        }
        return $id;
    }

    /**
     * 繰り返し（毎日）登録
     * @param   string  $st_datetime    開始日時
     * @param   string  $ed_datetime    終了日時
     * @param   string  $type_id    種別ID
     * @param   string  $subject    見出し
     * @param   string  $comment    内容
     * @param   string  $st_span    繰り返し開始日付け
     * @param   string  $ed_span    繰り返し終了日付け
     * @param   string  $daydiff    差分日数
     * @param   string  $open_range 公開区分
     * @param   array   $participants 参加者
     * @param   string  $idea       更新時使用スケジュールid
     */
    static function setEveryDay($st_datetime, $ed_datetime, $type_id, $subject, $comment
            , $st_span, $ed_span, $daydiff, $open_range
            , $participants, $id=null) {
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
                        'created_at' => date('Y/m/d H:i:s'),
                        'updated_at' => date('Y/m/d H:i:s')
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
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
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
        foreach ($participants as $user_id) {
            DB::table('scheduleparticipants')->insert([
                    'user_id'    => $user_id,
                    'schedule_id'=> $id,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
                ]);
        }
        return $id;
    }

    /**
     * 繰り返し（毎月）登録
     * @param   string  $st_datetime    開始日時
     * @param   string  $ed_datetime    終了日時
     * @param   string  $type_id    種別ID
     * @param   string  $subject    見出し
     * @param   string  $comment    内容
     * @param   string  $st_span    繰り返し開始日付け
     * @param   string  $ed_span    繰り返し終了日付け
     * @param   string  $monthdiff    差分月数
     * @param   string  $open_range 公開区分
     * @param   array   $participants 参加者
     * @param   string  $idea       更新時使用スケジュールid
     */
    static function setEveryMonth($st_datetime, $ed_datetime, $type_id, $subject, $comment
            , $st_span, $ed_span, $monthdiff, $open_range
            , $participants, $id=null) {
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
                        'created_at' => date('Y/m/d H:i:s'),
                        'updated_at' => date('Y/m/d H:i:s')
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
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
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
        foreach ($participants as $user_id) {
            DB::table('scheduleparticipants')->insert([
                    'user_id'    => $user_id,
                    'schedule_id'=> $id,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
                ]);
        }
        return $id;
    }

    /**
     * 繰り返し（毎週　曜日指定）登録
     * @param   string  $st_datetime    開始日時
     * @param   string  $ed_datetime    終了日時
     * @param   string  $type_id    種別ID
     * @param   string  $subject    見出し
     * @param   string  $comment    内容
     * @param   string  $st_span    繰り返し開始日付け
     * @param   string  $ed_span    繰り返し終了日付け
     * @param   string  $daydiff    差分日数
     * @param   string  $weekArray  対象曜日配列
     * @param   string  $open_range 公開区分
     * @param   array   $participants 参加者
     * @param   string  $idea       更新時使用スケジュールid
     */
    static function setWeek($st_datetime, $ed_datetime, $type_id, $subject, $comment
            , $st_span, $ed_span, $daydiff, $weekArray, $open_range, $participants, $id=null) {
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
                        'created_at' => date('Y/m/d H:i:s'),
                        'updated_at' => date('Y/m/d H:i:s')
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
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
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
        foreach ($participants as $user_id) {
            DB::table('scheduleparticipants')->insert([
                    'user_id'    => $user_id,
                    'schedule_id'=> $id,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s')
                ]);
        }
        return $id;
    }

    /**
     * 期間の終了日時の更新
     * @param   int     $id        スケジュールID
     * @param   string  $edSpan    終了日時
     */
    static function updateEdSpan($id, $edSpan) {
        DB::table('schedules')
            ->where('id', $id)
            ->update(['ed_span'=> $edSpan]);
    }

    /**
     * スケジュールデータを更新する
     *
     * @param   int     $id         スケジュールID
     * @param   string  $s_date     日付
     * @param   string  $st_time    開始時間
     * @param   string  $ed_time    終了時間
     * @param   string  $subject    見出し
     * @param   string  $comment    内容
     */
    static function updateSchedule($id, $s_date, $st_time, $ed_time, $subject, $comment)
    {
        $sql = "update schedules\n";
        $sql .= "set s_date = ?,\n";
        $sql .= " st_time = ?,\n";
        $sql .= " ed_time = ?,\n";
        $sql .= " subject = ?,\n";
        $sql .= " comment = ?\n";
        $sql .= " updated_at = sysdate()\n";
        $sql .= "where id = ?\n";

        $ret = DB::update($sql, [$s_date, $st_time, $ed_time, $subject, $comment, $id]);
    }

    /**
     * 全てのスケジュールデータ取得
     *
     * @param   int     $user_id    ユーザーID
     * @return array
     */
    static function getSchedulesTerm($user_id, $start, $end) {
        $sql = <<<EOF
SELECT s.id
     , IFNULL(ss.id, "") AS sub_id
     , sp.user_id
     , IFNULL(s.subject, "") AS subject
     , CONCAT(DATE_FORMAT(IFNULL(ss.s_date, s.st_datetime),'%Y-%m-%d'), " ",
        DATE_FORMAT(s.st_datetime,'%H:%i')) AS start_at
     , CONCAT(DATE_FORMAT(IFNULL(ss.s_date, s.ed_datetime),'%Y-%m-%d'), " ",
        DATE_FORMAT(s.ed_datetime, '%H:%i')) AS end_at
     , IFNULL(t.id, 0) AS type
     , IFNULL(t.name, "") AS type_name
     , DATEDIFF(DATE_FORMAT(IFNULL(ss.s_date, s.ed_datetime),'%Y-%m-%d')
			, DATE_FORMAT(IFNULL(ss.s_date, s.st_datetime),'%Y-%m-%d')) AS diff_days
  FROM schedules s
 INNER JOIN scheduleparticipants sp
    ON s.id = sp.schedule_id
  LEFT JOIN schedulesubs ss
    ON s.id = ss.relation_id
 LEFT JOIN types t
    ON s.type = t.id
 WHERE sp.user_id = ?
   AND (IFNULL(ss.s_date, s.st_datetime) BETWEEN ? AND ?
      OR (s.ed_datetime is not null AND ( ? <= s.ed_datetime AND ? >= s.st_datetime)))
 ORDER BY start_at
EOF;
        $params = array(
            $user_id,
            $start,
            $end,
            $start." 00:00",
            $end." 23:59"
        );

        $schedule = DB::select($sql, $params);

        $rows = array();
        foreach ($schedule as $key => $item) {
            $diff_days = $item->diff_days;
            for ($i=0; $i <= $diff_days ; $i++) {
                $day = "+{$i} day";
                $rows[] = [
                        'id'            => $item->id,
                        'subId'         => $item->sub_id,
                        'subject'       => $item->subject,
                        'mStartAt'      => $item->start_at,
                        'mEndAt'        => $item->end_at,
                        'type'          => $item->type,
                        'typeStr'       => $item->type_name,
                        'targetDate'    => date("Y-m-d 00:00", strtotime($day, strtotime($item->start_at))),
                ];
            }
        }

        return $rows;
    }

    /**
     * スケジュール削除
     */
    static function deleteAll($id) {
        DB::table('schedulesubs')->where('relation_id', '=', $id)->delete();
        DB::table('scheduleparticipants')->where('schedule_id', '=', $id)->delete();
        DB::table('schedules')->where('id', '=', $id)->delete();
    }
}
