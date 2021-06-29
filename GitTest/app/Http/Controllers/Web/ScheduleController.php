<?php
/**
 * 工程管理のコントローラー
 *
 * @author LiYanlin
 */

namespace App\Http\Controllers\Web;

use App\Http\Facades\Common;
use App\Http\Services\FirebaseService;
use App\Models\Account;
use App\Models\ChatGroup;
use App\Models\EnterpriseParticipant;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\Schedule;
use App\Models\ScheduleParticipant;
use App\Models\ScheduleSub;
use App\Models\User;
use \Datetime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleController extends \App\Http\Controllers\Controller
{
    // スケジュー追加
    public function save(Request $request)
    {
        DB::beginTransaction();
        try {
            $partnerArr = [];
            $id = $request->post('id');
            $participantsArr = $request->post('participantsCheckArr');
            $scheduleResult = $request->post('schedule');
            if ($id) {
                $updateType = (int)$request->post('updateType');//0 simple; 1 その日のみ; 2 その日以後すべて; 3 all
                $updateDate = $request->post('updateDate');
                $result = '';
                switch ($updateType) {
                    case 0:
                    case 3:
                        $result = $this->updateAll($scheduleResult, $participantsArr, $id, $updateDate);
                        break;
                    default:
                        //get the count of sub schedule
                        $count = ScheduleSub::where('relation_id', $id)->count();
                        if ($count <= 1) {
                            $result = $this->updateAll($scheduleResult, $participantsArr, $id, $updateDate);
                        } else {
                            //その日のみ
                            if ($updateType == 1) {
                                $this->updateOne($scheduleResult, $participantsArr, $id, $updateDate);
                            } elseif ($updateType == 2) {
                                //その日以後すべて
                                $result = $this->updateAfter($scheduleResult, $participantsArr, $id, $updateDate);
                            } else {
                                $result = $this->updateAll($scheduleResult, $participantsArr, $id, $updateDate);
                            }
                        }
                        break;
                }

                // #4873 「組織」で見ると表示されていない予定が、「個人」の週に表示されています。
                if ($result == trans('messages.error.week')) {
                    throw new \PDOException(trans('messages.error.week'));
                }
            } else {

                $schedule = new Schedule();
                $part = '予定登録 ';
                $schedule->created_user_id = Auth::id();

                // schedules
                if ($scheduleResult) {
                    $schedule->type = $scheduleResult['type'];
                    $schedule->subject = $scheduleResult['subject'];
                    $schedule->st_span = $scheduleResult['st_span'];
                    $schedule->st_datetime = $scheduleResult['st_datetime'];
                    $schedule->repeat_kbn = $scheduleResult['repeat_kbn'];
                    $schedule->ed_span = $scheduleResult['ed_span'];
                    $schedule->ed_datetime = $scheduleResult['ed_datetime'];
                    $schedule->comment = $scheduleResult['comment'];
                    $schedule->location = $scheduleResult['location'];
                    $schedule->all_day = $scheduleResult['allDay'];
                    if (strlen($scheduleResult['notify_min_ago']) > 0) {
                        $schedule->notify = 1;
                    } else {
                        $schedule->notify = null;
                    }
                    $schedule->notify_min_ago = $scheduleResult['notify_min_ago'];
                    $repeat_type = $scheduleResult['repeatType'];
                    if ($schedule->repeat_kbn === '4') {
                        $schedule->week1 = $scheduleResult['week1'];
                        $schedule->week2 = $scheduleResult['week2'];
                        $schedule->week3 = $scheduleResult['week3'];
                        $schedule->week4 = $scheduleResult['week4'];
                        $schedule->week5 = $scheduleResult['week5'];
                        $schedule->week6 = $scheduleResult['week6'];
                        $schedule->week7 = $scheduleResult['week7'];
                    }
                    $schedule->save();

                    // schedule_subs
                    if ($repeat_type != 0) {
                        $stSpan = $schedule->st_span;
                        $edSpan = $schedule->ed_span;
                        $diffDay = $this->diffDayBetweenTwoDays($stSpan, $edSpan);
                        $diffMonth = $this->diffMonthBetweenTwoDays($stSpan, $edSpan);
                    }
                    $subId = 0;
                    // 間隔の設定
                    switch ($repeat_type) {
                        //繰り返しない
                        case 0:
                            break;
                        // 毎日
                        case 1:
                            $st_span_ts = strtotime($stSpan);
                            for ($i = 0; $i <= $diffDay; $i++) {
                                $day = "+{$i} day";
                                $scheduleSub = new ScheduleSub();
                                $scheduleSub->relation_id = $schedule->id;
                                $scheduleSub->s_date = date("Y-m-d", strtotime($day, $st_span_ts));
                                $scheduleSub->save();
                                $subId = $scheduleSub->id;
                            }
                            break;
                        //毎週 平日(月～金)
                        //毎週 はん曜日
                        case 2:
                        case 3:
                            $min = strtotime($stSpan);
                            $max = strtotime($edSpan);
                            $weekArray = array();
                            for ($i = 1; $i <= 7; $i++) {
                                $weekArray[$i] = ($scheduleResult['week' . $i] == 1) ? 1 : null;
                            }
                            $subNum = 0;
                            for ($i = $min; $i <= $max; $i = strtotime('1 day', $i)) {
                                $date = date('Y-m-d', $i);
                                $week = date('w', $i) + 1;
                                if ($weekArray[$week] == 1) {
                                    $scheduleSub = new ScheduleSub();
                                    $scheduleSub->relation_id = $schedule->id;
                                    $scheduleSub->s_date = $date;
                                    $scheduleSub->save();
                                    $subNum++;
                                    $subId = $scheduleSub->id;
                                }
                            }
                            if (!$subNum) {
                                throw new \PDOException(trans('messages.error.week'));
                            }
                            break;
                        // 毎月
                        case 4:
                            $st_span_ts = strtotime($stSpan);
                            $spanYear = date('Y', $st_span_ts);
                            $spanMonth = date('m', $st_span_ts);
                            $spanDay = date('d', $st_span_ts);
                            for ($i = 0; $i <= $diffMonth; $i++) {
                                $monthTmp = ($spanMonth + $i) % 12;
                                if ($monthTmp === 0) {
                                    $monthTmp = 12;
                                }
                                $base_time = strtotime("{$spanYear}-{$monthTmp}-{$spanDay}");
                                $date1 = strtotime("{$spanYear}-{$monthTmp}-01");
                                if ($monthTmp == 12) {
                                    $monthTmp1 = 1;
                                    $spanYear = $spanYear + 1;
                                } else {
                                    $monthTmp1 = $monthTmp + 1;
                                }
                                $date2 = strtotime("{$spanYear}-{$monthTmp1}-01");
                                $days = ($date2 - $date1) / (60 * 60 * 24);
                                if ($spanDay > $days) {
                                    continue;
                                }
                                $second1 = strtotime($edSpan);
                                $second3 = strtotime($stSpan);
                                if ($second1 >= $base_time && $base_time >= $second3) {
                                    $scheduleSub = new ScheduleSub();
                                    $scheduleSub->relation_id = $schedule->id;
                                    $scheduleSub->s_date = date("Y-m-d", $base_time);
                                    $scheduleSub->save();
                                    $subId = $scheduleSub->id;
                                }
                            }
                            break;
                        // カスタマイズ
                        case 5:
                            if ($schedule->repeat_interval_type == '1') {
                                $type = $schedule->repeat_interval_value . ' week';
                            } else {
                                $type = $schedule->repeat_interval_value . ' month';
                            }
                            $min = strtotime($stSpan);
                            $max = strtotime($edSpan);
                            $weekArray = array();
                            for ($i = 1; $i <= 7; $i++) {
                                $weekArray[$i] = ($scheduleResult['week' . $i] == 1) ? 1 : null;
                            }
                            for ($i = $min, $j = 1; $i <= $max; $i = strtotime('1 day', $i), $j++) {
                                $date = date('Y-m-d', $i);
                                $week = date('w', $i) + 1;
                                if ($weekArray[$week] == 1) {
                                    $scheduleSub = new ScheduleSub();
                                    $scheduleSub->relation_id = $schedule->id;
                                    $scheduleSub->s_date = $date;
                                    $scheduleSub->save();
                                    $subId = $scheduleSub->id;
                                }
                                if ($j == 7) {
                                    $j = 0;
                                    $i = strtotime($type, $i);
                                }
                            }
                            break;
                    }
                    //schedule_participants
                    if ($participantsArr) {
                        $itemArr1 = $partnerArr;//$itemArr1 元のスケジュールの人々
                        $itemArr2 = [];//$itemArr2 スケジュールの増加
                        $itemArr3 = [];//$itemArr3 スケジュールを削除しました
                        $itemArr4 = [];//$itemArr4 現在予定されている人
                        foreach ($participantsArr as $participant) {
                            $scheduleParticipant = new ScheduleParticipant();
                            $scheduleParticipant->user_id = $participant['id'];
                            $scheduleParticipant->schedule_id = $schedule->id;
                            $scheduleParticipant->save();
                            $itemArr4[] = $participant['id'];
                            if (!in_array($participant['id'], $partnerArr)) {
                                $itemArr2[] = $participant['id'];
                                $partnerArr[] = $participant['id'];
                            }
                        }
                        foreach ($itemArr1 as $item) {
                            if (!in_array($item, $itemArr4)) {
                                $itemArr3[] = $item;
                            }
                        }
                        $dashboard = new DashboardController();
                        $date = date('m/d H:i');
                        //予定が変わった時（編集された時）
                        $itemArr5 = array_intersect($itemArr1, $itemArr4);//$itemArr5 スケジュールが変更されていないスタッフ
                        foreach ($itemArr5 as $item) {
                            if ($item != Auth::id()) {
                                $dashboard->addDashboard($schedule->id, 3,
                                    $schedule->subject . 'の予定が変更されました。（' . $date . '）', '', $item);
                            }
                        }
                        //自分が参加のスケジュールが登録された時
                        foreach ($itemArr2 as $item) {
                            if ($item != Auth::id()) {
                                $dashboard->addDashboard($schedule->id, 3,
                                    $schedule->subject . 'の参加者になりました。（' . $date . '）', '', $item);
                            }
                        }
                        //スケジュールから自分が外された時
                        foreach ($itemArr3 as $item) {
                            if ($item != Auth::id()) {
                                $dashboard->addDashboard($schedule->id, 3,
                                    $schedule->subject . 'の参加者から削除されました。（' . $date . '）', '', $item,1);
                            }
                        }
                        $fireBase = new FirebaseService();
                        $title = $part . $schedule->st_datetime . " " . $schedule->subject;
                        foreach ($partnerArr as $partner) {
                            if ($partner != Auth::id()) {
                                  $fireBase->pushSchedule($partner, $schedule->id, $subId, $title);
                            }
                        }
                    }
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            if ($e->getMessage() == trans('messages.error.week')) {
                return $this->json($e->getMessage());
            } else {
                return $this->json(trans('messages.error.insert'));
            }
        }
        return $this->json();
    }
    public function updateOne($scheduleResult,$participantsArr,$id,$updateDate) {
        $fireBase=new FirebaseService();
        $type_id = $scheduleResult['type'];
        $subject = $scheduleResult['subject'];
        $st_datetime = $scheduleResult['st_datetime'];
        $ed_datetime = $scheduleResult['ed_datetime'];
        $comment = $scheduleResult['comment'];
        $location = $scheduleResult['location'];
        $all_day = $scheduleResult['allDay'];
        $notify_min_ago = $scheduleResult['notify_min_ago'];
        $open_range= $scheduleResult['open_range'];
        $participants = array_column($participantsArr,'id');
        if (strlen($scheduleResult['notify_min_ago']) > 0) {
            $notify = 1;
        } else {
            $notify = null;
        }
        $status = 200;
        // 自分も参加者として追加
        // 他の人も設定された場合、配列に追加
        DB::beginTransaction();
        try {
            $oldSchedule = Schedule::where('id', $id)->first()->toArray();
            $oldParticipants = ScheduleParticipant::where('schedule_id', $id)->pluck('user_id')->toArray();
            $oldSubList = ScheduleSub::where('relation_id', $id)->orderBy('s_date', 'asc')->get()->toArray();
            $count = count($oldSubList);
            switch ($oldSchedule['repeat_kbn']) {
                case '1':
                    //每日
                    //check the update day's position in schedule
                    $firstSubDate = $oldSubList[0]['s_date'];
                    $lastSubDate = $oldSubList[$count - 1]['s_date'];
                    $firstDiff = date_diff(date_create($updateDate), date_create($firstSubDate), true)->format("%a");
                    $lastDiff = date_diff(date_create($updateDate), date_create($lastSubDate), true)->format("%a");
                    if ($firstDiff == 0) {
                        //first day in the schedule
                        ScheduleSub::where('relation_id', '=', $id)->where('s_date', '=', $updateDate)->delete();
                        Schedule::where('id', $id)->update([
                            'st_datetime' => $oldSubList[1]['s_date'] . ' ' . date('H:i:s', strtotime($oldSchedule['st_datetime'])),
                            'st_span' => $oldSubList[1]['s_date'],
                            'updated_at' => date('Y/m/d H:i:s'),
                            'updated_user_id' => Auth::id(),
                        ]);
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);
                    } elseif ($lastDiff == 0) {
                        //last day in the schedule
                        ScheduleSub::where('relation_id', '=', $id)->where('s_date', '=', $updateDate)->delete();
                        Schedule::where('id', $id)->update([
                            'ed_datetime' => $oldSubList[$count - 2]['s_date'] . ' ' . date('H:i:s', strtotime($oldSchedule['ed_datetime'])),
                            'ed_span' => $oldSubList[$count - 2]['s_date'],
                            'updated_at' => date('Y/m/d H:i:s'),
                            'updated_user_id' => Auth::id(),
                        ]);
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);
                    } else {
                        //mid day in schedule
                        ScheduleSub::where('relation_id', $id)->delete();

                        //create update-day's new schedule
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);

                        $dayIndex = $firstDiff;

                        //update old schedule's first day - update day
                        $firstEdDateTime = $oldSubList[$dayIndex - 1]['s_date'] . ' ' . date('H:i:s', strtotime($oldSchedule['ed_datetime']));
                        $firstToUpdateDiff = date_diff(date_create($oldSchedule['st_span']), date_create($oldSubList[$dayIndex - 1]['s_date']), true)->format("%a");
                        Schedule::setEveryDay($oldSchedule['st_datetime'], $firstEdDateTime, $oldSchedule['type'], $oldSchedule['subject'],
                            $oldSchedule['comment'], $oldSchedule['st_span'], $oldSubList[$dayIndex - 1]['s_date'], $firstToUpdateDiff, $oldSchedule['open_range'],
                            $oldSchedule['notify'], $oldSchedule['notify_min_ago'], $oldSchedule['all_day'], $oldParticipants, Auth::id(), $oldSchedule['location'],$id);

                        //create old schedule's last day - update day
                        $lastStDateTime = $oldSubList[$dayIndex + 1]['s_date'] . ' ' . date('H:i:s', strtotime($oldSchedule['st_datetime']));
                        $updateToLastDiff = date_diff(date_create($oldSubList[$dayIndex + 1]['s_date']), date_create($oldSchedule['ed_datetime']), true)->format("%a");
                        Schedule::setEveryDay($lastStDateTime, $oldSchedule['ed_datetime'], $oldSchedule['type'], $oldSchedule['subject'],
                            $oldSchedule['comment'], $oldSubList[$dayIndex + 1]['s_date'], $oldSchedule['ed_span'], $updateToLastDiff,
                            $oldSchedule['open_range'], $oldSchedule['notify'], $oldSchedule['notify_min_ago'], $oldSchedule['all_day'], $oldParticipants, Auth::id(), $oldSchedule['location']);
                    }
                    break;
                case '3':
                    //每月
                    $originalSubDateList = array_column($oldSubList,'s_date');
                    $length = count($originalSubDateList);
                    $updateDayIndex = array_search($updateDate,$originalSubDateList);
                    if ($updateDayIndex == 0){
                        //first
                        $newStDate = $originalSubDateList[$updateDayIndex + 1];
                        Schedule::where('id', $id)->update([
                            'st_datetime' => $newStDate . ' ' . date('H:i:s', strtotime($oldSchedule['st_datetime'])),
                            'st_span' => $newStDate,
                            'updated_at' => date('Y/m/d H:i:s'),
                            'updated_user_id' => Auth::id(),
                        ]);
                        ScheduleSub::where('relation_id', '=', $id)->where('s_date', '=', $updateDate)->delete();
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);
                    }elseif ($updateDayIndex == $length - 1){
                        //last
                        $newEdDate = date('Y-m-d',strtotime("$updateDate - 1 days"));
                        Schedule::where('id', $id)->update([
                            'ed_datetime' => $newEdDate . ' ' . date('H:i:s', strtotime($oldSchedule['ed_datetime'])),
                            'ed_span' => $newEdDate,
                            'updated_at' => date('Y/m/d H:i:s'),
                            'updated_user_id' => Auth::id(),
                        ]);
                        ScheduleSub::where('relation_id', '=', $id)->where('s_date', '=', $updateDate)->delete();
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);
                    }else{
                        //middle
                        ScheduleSub::where('relation_id', $id)->delete();
                        //before(update)
                        $beforeEdDate = date('Y-m-d', strtotime("$updateDate - 1 days"));
                        $interval = date_diff(date_create($oldSchedule['st_span']), date_create($beforeEdDate), true);
                        $monthdiff = $interval->format('%y') * 12 + $interval->format('%m');
                        Schedule::setEveryMonth($oldSchedule['st_datetime'], $beforeEdDate . ' ' . date('H:i:s', strtotime($oldSchedule['ed_datetime'])), $oldSchedule['type']
                            , $oldSchedule['subject'], $oldSchedule['comment'], $oldSchedule['st_span']
                            , $beforeEdDate, $monthdiff, $oldSchedule['open_range']
                            , $oldSchedule['notify'], $oldSchedule['notify_min_ago'], $oldSchedule['all_day'], $oldParticipants, Auth::id(), $oldSchedule['location'],$id);
                        //after(create)
                        $afterStDate = $originalSubDateList[$updateDayIndex + 1];
                        $interval = date_diff(date_create($afterStDate), date_create($oldSchedule['ed_span']), true);
                        $monthdiff = $interval->format('%y') * 12 + $interval->format('%m');
                        Schedule::setEveryMonth($afterStDate . ' ' . date('H:i:s', strtotime($oldSchedule['st_datetime'])), $oldSchedule['ed_datetime'], $oldSchedule['type']
                            , $oldSchedule['subject'], $oldSchedule['comment'], $afterStDate
                            , $oldSchedule['ed_span'], $monthdiff, $oldSchedule['open_range']
                            , $oldSchedule['notify'], $oldSchedule['notify_min_ago'], $oldSchedule['all_day'], $oldParticipants, Auth::id(), $oldSchedule['location']);

                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);
                    }
                    break;
                case '4':
                    //毎週
                    $stSpanToUpdateDateDiff = date_diff(date_create($updateDate), date_create($oldSchedule['st_span']), true)->format("%a");
                    $edSpanToUpdateDateDiff = date_diff(date_create($updateDate), date_create($oldSchedule['ed_span']), true)->format("%a");
                    if ($stSpanToUpdateDateDiff == 0) {
                        //first day
                        ScheduleSub::where('relation_id', '=', $id)->where('s_date', '=', $updateDate)->delete();
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);
                        $newStDate = date('Y-m-d', strtotime("$updateDate +1 day"));
                        Schedule::where('id', $id)->update([
                            'st_datetime' => $newStDate . ' ' . date('H:i:s', strtotime($oldSchedule['st_datetime'])),
                            'st_span' => $newStDate,
                            'updated_at' => date('Y/m/d H:i:s'),
                            'updated_user_id' => Auth::id(),
                        ]);
                    } elseif ($edSpanToUpdateDateDiff == 0) {
                        //last day
                        ScheduleSub::where('relation_id', '=', $id)->where('s_date', '=', $updateDate)->delete();
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);
                        $newEdDate = date('Y-m-d', strtotime("$updateDate -1 day"));
                        Schedule::where('id', $id)->update([
                            'ed_datetime' => $newEdDate . ' ' . date('H:i:s', strtotime($oldSchedule['ed_datetime'])),
                            'ed_span' => $newEdDate,
                            'updated_at' => date('Y/m/d H:i:s'),
                            'updated_user_id' => Auth::id(),
                        ]);
                    } else {
                        //mid day
                        ScheduleSub::where('relation_id', $id)->delete();

                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range, $notify, $notify_min_ago
                            , $all_day, $participants, Auth::id(), $location);

                        $weekArray = [
                            1 => $oldSchedule['week1'],
                            2 => $oldSchedule['week2'],
                            3 => $oldSchedule['week3'],
                            4 => $oldSchedule['week4'],
                            5 => $oldSchedule['week5'],
                            6 => $oldSchedule['week6'],
                            7 => $oldSchedule['week7'],
                        ];

                        //before update day
                        Schedule::setWeek($oldSchedule['st_datetime'],
                            date('Y-m-d', strtotime("$updateDate -1 day")) . ' ' . date('H:i:s', strtotime($oldSchedule['ed_datetime'])),
                            $oldSchedule['type'], $oldSchedule['subject'], $oldSchedule['comment'], $oldSchedule['st_span'],
                            date('Y-m-d', strtotime("$updateDate -1 day")), 0, $weekArray, $oldSchedule['open_range']
                            , $oldSchedule['notify'], $oldSchedule['notify_min_ago'], $oldSchedule['all_day'], $oldParticipants, Auth::id(), $oldSchedule['location'],$id);

                        //after update day
                        Schedule::setWeek(
                            date('Y-m-d', strtotime("$updateDate +1 day")) . ' ' . date('H:i:s', strtotime($oldSchedule['st_datetime'])),
                            $oldSchedule['ed_datetime'], $oldSchedule['type'], $oldSchedule['subject'],
                            $oldSchedule['comment'],
                            date('Y-m-d', strtotime("$updateDate +1 day")), $oldSchedule['ed_span'],
                            0, $weekArray,
                            $oldSchedule['open_range'], $oldSchedule['notify'], $oldSchedule['notify_min_ago'], $oldSchedule['all_day'], $oldParticipants, Auth::id(), $oldSchedule['location']);

                    }
                    break;
                default:
                    break;
            }
            DB::commit();
            $res = [
                'id'            => $newId,
                'subId'        => '0',
                'status'        => '0000',
                'message'       => ''
            ];

            $title = "予定更新 ".$st_datetime." ".$subject;
            // 参加者に通知
            $part_arr = Scheduleparticipant::where('schedule_id',
                $id)->whereNull('deleted_at')->pluck('user_id')->toArray();
            $itemArr1 = $part_arr;//$itemArr1 元のスケジュールの人々
            $itemArr2 = [];//$itemArr2 スケジュールの増加
            $itemArr3 = [];//$itemArr3 スケジュールを削除しました
            $itemArr4 = [];//$itemArr4 現在予定されている人
            foreach ($participants as $user_id) {
                $itemArr4[] = $user_id;
                if (!in_array($user_id, $part_arr)) {
                    $itemArr2[] = $user_id;
                    $part_arr[] = $user_id;
                }
                if($user_id!=Auth::id()){
                    $fireBase->pushSchedule($user_id, $id, 0, $title);
                }
            }
            foreach ($itemArr1 as $item) {
                if (!in_array($item,$itemArr4)) {
                    $itemArr3[] = $item;
                }
            }
            $dashboard = new DashboardController();
            $date = date('m/d H:i');
            //予定が変わった時（編集された時）
            $itemArr5 = array_intersect($itemArr1,$itemArr4);//$itemArr5 スケジュールが変更されていないスタッフ
            foreach ($itemArr5 as $item){
                if($item != Auth::id()){
                    $dashboard->addDashboard($id,3,
                        $subject.'の予定が変更されました。（'.$date.'）', '',$item);
                }
            }
            //自分が参加のスケジュールが登録された時
            foreach ($itemArr2 as $item){
                if($item!=Auth::id()) {
                    $dashboard->addDashboard($id, 3,
                        $subject . 'の参加者になりました。（' . $date . '）', '', $item);
                }
            }
            //スケジュールから自分が外された時
            $title="参加者から削除されました ".$st_datetime."・".$subject;
            foreach ($itemArr3 as $item){
                if($item!=Auth::id()){
                    $dashboard->addDashboard($id,3,
                    $subject.'の参加者から削除されました。（'.$date.'）', '',$item ,1);
                    $fireBase->pushSchedule($item, $id, 0, $title,'deleteScheduleUser');
                }
            }

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateAfter($scheduleResult,$participantsArr,$id,$updateDate) {
        $target_date = $updateDate;
        $date = new DateTime($target_date);
        $pre_date = $date->modify('-1 days')->format('Y-m-d');
        $st_span = null;
        $ed_span = null;
        $monthdiff = null;
        $daydiff = null;


        $fireBase=new FirebaseService();
        $dashboard = new DashboardController();

        $type_id = $scheduleResult['type'];
        $subject = $scheduleResult['subject'];
        $st_datetime = $scheduleResult['st_datetime'];
        $ed_datetime = $scheduleResult['ed_datetime'];
        $comment = $scheduleResult['comment'];
        $location = $scheduleResult['location'];
        $all_day = $scheduleResult['allDay'];
        $notify_min_ago = $scheduleResult['notify_min_ago'];
        $open_range= $scheduleResult['open_range'];
        $participants = array_column($participantsArr,'id');
        $st_span = $scheduleResult['st_span'];
        $ed_span = $scheduleResult['ed_span'];
        $repeat_kbn = $scheduleResult['repeat_kbn'];


        if (strlen($scheduleResult['notify_min_ago']) > 0) {
            $notify = 1;
        } else {
            $notify = null;
        }
        if ($st_span && $ed_span) {
            $datetime1 = date_create($st_span);
            $datetime2 = date_create($ed_span);
            $interval = date_diff($datetime1, $datetime2);
            $monthdiff =$interval->format('%y')*12+$interval->format('%m');
            $daydiff = $interval->format('%a');
        }
        $status = 200;
        // 他の人も設定された場合、配列に追加
        DB::beginTransaction();
        try {

            ScheduleSub::where('relation_id', '=', $id)->where('s_date', '>=', $updateDate)->delete();
            //Is the currently selected date the first day of the cycle schedule
            $firstDay = ScheduleSub::where('relation_id', '=', $id)->whereNull('deleted_at')->orderBy('s_date','asc')->first();
            if ($firstDay['s_date'] != $updateDate){
                //not first day
                // 前日日付までにSchedule繰り返し終了日時を更新
                Schedule::updateEdSpan($id, Auth::id(), $pre_date);
            }else{
                //first day
                Schedule::updateEdSpan($id, Auth::id(), $target_date);
            }

            // 新規登録
            $db_st_span= Schedule::where('id',$id)->value('st_span');

            switch($repeat_kbn) {
                // 1日だけ
                case '0':
                    if ($db_st_span == $st_span && $st_span !== $ed_span) {
                        //編集
                        Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range
                            , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location, $id);
                    } else {
                        // 新規登録
                        $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $open_range
                            , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location);
                    }
                    break;

                // 毎日
                case '1':
                    if ($db_st_span == $st_span && $st_span !== $ed_span) {
                        //編集
                        Schedule::setEveryDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $st_span
                            , $ed_span, $daydiff, $open_range
                            , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location, $id);
                        $newId = $id;
                    } else {
                        // 新規登録
                        $newId = Schedule::setEveryDay($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $st_span
                            , $ed_span, $daydiff, $open_range
                            , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location);
                    }
                    break;

                // 毎月
                case '3':
                    if ($db_st_span == $st_span && $st_span !== $ed_span) {
                        //編集
                        Schedule::setEveryMonth($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $st_span
                            , $ed_span, $monthdiff, $open_range
                            , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location, $id);
                        $newId = $id;
                    } else {
                        // 新規登録
                        $newId = Schedule::setEveryMonth($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $st_span
                            , $ed_span, $monthdiff, $open_range
                            , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location);
                    }
                    break;

                // 曜日指定（毎週）
                case '4':
                    //#4873「組織」で見ると表示されていない予定が、「個人」の週に表示されています。
                    $result = self::dealWeekIsDate($scheduleResult, $st_span, $ed_span);
                    $weekArray = $result['weekArray'];
                    if (!$result['subNum']) {
                        throw new \PDOException(trans('messages.error.week'));
                    } else {
                        if ($db_st_span == $st_span && $st_span !== $ed_span) {
                            //編集
                            Schedule::setWeek($st_datetime, $ed_datetime, $type_id
                                , $subject, $comment, $st_span
                                , $ed_span, $daydiff, $weekArray, $open_range
                                , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location, $id);
                            $newId = $id;
                        } else {
                            // 新規登録
                            $newId = Schedule::setWeek($st_datetime, $ed_datetime, $type_id
                                , $subject, $comment, $st_span
                                , $ed_span, $daydiff, $weekArray, $open_range
                                , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location);
                        }

                    }
            }

            DB::commit();
            // 該当日付のデータ呼び出し
            $newSubId = Schedulesub::getSubId($newId, $target_date);

            $res = [
                'id'            => $newId,
                'subId'         => $newSubId,
                'status'        => '0000',
                'message'       => ''
            ];
            $title = "予定更新 ".$st_datetime." ".$subject;
            // 参加者に通知
            $part_arr=Scheduleparticipant::where('schedule_id',$id)->pluck('user_id')->toArray();
            $itemArr1 = $part_arr;//$itemArr1 元のスケジュールの人々
            $itemArr2 = [];//$itemArr2 スケジュールの増加
            $itemArr3 = [];//$itemArr3 スケジュールを削除しました
            $itemArr4 = [];//$itemArr4 現在予定されている人
            foreach ($participants as $user_id) {
                $itemArr4[] = $user_id;
                if (!in_array($user_id, $part_arr)) {
                    $itemArr2[] = $user_id;
                    $part_arr[] = $user_id;
                }
                if($user_id!=Auth::id()){
                    $fireBase->pushSchedule($user_id, $newId, $newSubId, $title);
                }
            }
            foreach ($itemArr1 as $item) {
                if (!in_array($item,$itemArr4)) {
                    $itemArr3[] = $item;
                }
            }
            $date = date('m/d H:i');
            //予定が変わった時（編集された時）
            $itemArr5 = array_intersect($itemArr1,$itemArr4);//$itemArr5 スケジュールが変更されていないスタッフ
            foreach ($itemArr5 as $item){
                if($item != Auth::id()){
                    $dashboard->addDashboard($id,3,
                        $subject.'の予定が変更されました。（'.$date.'）', '',$item);
                }
            }
            //自分が参加のスケジュールが登録された時
            foreach ($itemArr2 as $item){
                if($item != Auth::id()) {
                    $dashboard->addDashboard($id, 3,
                        $subject . 'の参加者になりました。（' . $date . '）', '', $item);
                }
            }
            //スケジュールから自分が外された時
            $title="参加者から削除されました ".$st_datetime."・".$subject;
            foreach ($itemArr3 as $item){
                if($item!=Auth::id()){
                    $dashboard->addDashboard($id,3,
                        $subject.'の参加者から削除されました。（'.$date.'）', '',$item,1);
                    $fireBase->pushSchedule($item, $newId, $newSubId, $title,'deleteScheduleUser');
                }
            }

        } catch (\PDOException $e){

            DB::rollBack();
            if ($e->getMessage() == trans('messages.error.week')) {
                return $e->getMessage();
            } else {
                $res = [
                    'status' => '0101',
                    'message' => $e->getMessage()
                ];
            }

        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * スケジュール更新
     * スケジュール情報を更新、繰り返し日、参加者をdelete&insert
     */
    public function updateAll($scheduleResult,$participantsArr,$id,$updateDate) {
        $target_date = $updateDate;
        $date = new DateTime($target_date);
        $pre_date = $date->modify('-1 days')->format('Y-m-d');
        $st_span = null;
        $ed_span = null;
        $monthdiff = null;
        $daydiff = null;


        $fireBase=new FirebaseService();
        $dashboard = new DashboardController();

        $type_id = $scheduleResult['type'];
        $subject = $scheduleResult['subject'];
        $st_datetime = $scheduleResult['st_datetime'];
        $ed_datetime = $scheduleResult['ed_datetime'];
        $comment = $scheduleResult['comment'];
        $location = $scheduleResult['location'];
        $all_day = $scheduleResult['allDay'];
        $notify_min_ago = $scheduleResult['notify_min_ago'];
        $open_range= $scheduleResult['open_range'];
        $participants = array_column($participantsArr,'id');
        $st_span = $scheduleResult['st_span'];
        $ed_span = $scheduleResult['ed_span'];
        $repeat_kbn = $scheduleResult['repeat_kbn'];
        if (strlen($scheduleResult['notify_min_ago']) > 0) {
            $notify = 1;
        } else {
            $notify = null;
        }
        if ($st_span && $ed_span) {
            $datetime1 = date_create($st_span);
            $datetime2 = date_create($ed_span);
            $interval = date_diff($datetime1, $datetime2);
            $monthdiff = $interval->format('%y')*12+$interval->format('%m');
            $daydiff = $interval->format('%a');
        }
        $status = 200;

        DB::beginTransaction();
        try {
            $part_arr=Scheduleparticipant::where('schedule_id',$id)->pluck('user_id')->toArray();
            Schedulesub::where('relation_id', '=', $id)->delete();
            Scheduleparticipant::where('schedule_id', '=', $id)->delete();

            // 自分も参加者として追加
            $user_delete=array_diff($part_arr,$participants);
            foreach ($user_delete as $key=>$val){
                DB::table('schedule_changes')->insert(['schedule_id'=>$id,'user_id'=>$val]);
            }
            // 他の人も設定された場合、配列に追加
            // 新規登録
            switch($repeat_kbn) {
                // 1日だけ
                case '0':
                    $id=Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $open_range
                        , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location, $id);
                    break;

                // 毎日
                case '1':
                    $id=Schedule::setEveryDay($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $daydiff, $open_range
                        , $notify, $notify_min_ago, $all_day, $participants, Auth::id(),$location, $id);
                    break;

                // 毎月
                case '3':
                    $id=Schedule::setEveryMonth($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $monthdiff, $open_range
                        , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location, $id);
                    break;

                // 曜日指定
                case '4':
                    //#4873「組織」で見ると表示されていない予定が、「個人」の週に表示されています。
                    $result = self::dealWeekIsDate($scheduleResult, $st_span, $ed_span);
                    $weekArray = $result['weekArray'];
                    if (!$result['subNum']) {
                        throw new \PDOException(trans('messages.error.week'));

                    } else {
                        $id = Schedule::setWeek($st_datetime, $ed_datetime, $type_id
                            , $subject, $comment, $st_span
                            , $ed_span, $daydiff, $weekArray, $open_range
                            , $notify, $notify_min_ago, $all_day, $participants, Auth::id(), $location, $id);
                    }

            }

            DB::commit();
            // 該当日付のデータ呼び出し
            $date = new DateTime($st_datetime);
            $target_date = $date->format('Y-m-d');
            $newSubId = Schedulesub::getSubId($id, $target_date);
            $res = [
                'id'            => $id,
                'subId'         => $newSubId,
                'status'        => '0000',
                'message'       => ''
            ];

            $title = "予定更新 ".$st_datetime." ".$subject;
            // 参加者に通知
            $itemArr1 = $part_arr;//$itemArr1 元のスケジュールの人々
            $itemArr2 = [];//$itemArr2 スケジュールの増加
            $itemArr3 = [];//$itemArr3 スケジュールを削除しました
            $itemArr4 = [];//$itemArr4 現在予定されている人
            foreach ($participants as $user_id) {
                $itemArr4[] = $user_id;
                if (!in_array($user_id, $part_arr)) {
                    $itemArr2[] = $user_id;
                    $part_arr[] = $user_id;
                }
                if($user_id!=Auth::id()){
                    $fireBase->pushSchedule($user_id, $id, $newSubId, $title);
                }
            }
            foreach ($itemArr1 as $item) {
                if (!in_array($item,$itemArr4)) {
                    $itemArr3[] = $item;
                }
            }
            $date = date('m/d H:i');
            //予定が変わった時（編集された時）
            $itemArr5 = array_intersect($itemArr1,$itemArr4);//$itemArr5 スケジュールが変更されていないスタッフ
            foreach ($itemArr5 as $item){
                if($item != Auth::id()){
                    $dashboard->addDashboard($id,3,
                        $subject.'の予定が変更されました。（'.$date.'）', '',$item);
                }
            }
            //自分が参加のスケジュールが登録された時
            foreach ($itemArr2 as $item){
                if($item!=Auth::id()) {
                    $dashboard->addDashboard($id, 3,
                        $subject . 'の参加者になりました。（' . $date . '）', '', $item);
                }
            }
            //スケジュールから自分が外された時
            $title="参加者から削除されました ".$st_datetime."・".$subject;
            foreach ($itemArr3 as $item){
                if($item!=Auth::id()){
                    $dashboard->addDashboard($id,3,
                        $subject.'の参加者から削除されました。（'.$date.'）', '',$item,1);
                    $fireBase->pushSchedule($item, $id, $newSubId, $title,'deleteScheduleUser');
                }
            }

        } catch (\PDOException $e) {

            DB::rollBack();
            if ($e->getMessage() == trans('messages.error.week')) {
                return $e->getMessage();
            } else {
                $res = [
                    'status' => '0101',
                    'message' => $e->getMessage()
                ];
            }
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }
    public function searchUser(Request $request)
    {
        try {
            $users = Account::query()->where('name', 'LIKE', "%{$request->get('words')}%")->get(['id', 'name']);
            return $this->json("", $users);
        } catch (\PDOException $e) {
            Log::error($e);
            return $this->json($e);
        }
    }

    public function diffDayBetweenTwoDays($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

    public function diffMonthBetweenTwoDays($start_m, $end_m)
    {
        $date1 = explode('-', $start_m);
        $date2 = explode('-', $end_m);

        if ($date1[1] < $date2[1]) {
            return abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
        } else {
            return abs($date1[0] - $date2[0]) * 12 - abs($date1[1] - $date2[1]);
        }
    }

    public function getScheduleById(Request $request)
    {
        try {
            $model = Schedule::with(['scheduleParticipants.userInfo', 'createBy', 'updateBy'])->where('id', '=',
                $request->get('scheduleId'))->first();
            if ($model['st_span'] && $model['ed_span']){
                $model['ed_datetime'] = $model['ed_span'].' '.date('H:i:s',strtotime($model['ed_datetime']));
            }
            return $this->json('', $model);
        } catch (\PDOException $e) {
            Log::error($e);
            return $this->json(trans('messages.error.system'));
        }
    }

    public function deleteScheduleById(Request $request)
    {
        try {
            $model = null;
            $id = $request->get('id');
            $updateType = $request->post('updateType');
            $updateDate = $request->post('updateDate');
            if ($updateType == '1') {
                $model = ScheduleSub::where('relation_id', '=', $id)->where('s_date', '=', $updateDate)->delete();
            } else {
                if ($updateType == '2') {
                    $model = ScheduleSub::where('relation_id', '=', $id)->where('s_date', '>=', $updateDate)->delete();
                } else {
                    if ($updateType == '3') {
                        $model = Schedule::destroy($id);
                        DB::table('schedule_changes')->insert(['schedule_id'=>$id]);
                    }
                }
            }
            return $this->json('', $model);
        } catch (\PDOException $e) {
            Log::error($e);
            return $this->json(trans('messages.error.delete'));
        }
    }

    /**
     * スケジュール一覧
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $stDate = $request->post('stDate');
        $edDate = $request->post('edDate');
        $edDate=date('Y/m/d H:i',strtotime("$edDate -1 Minute"));
        $scheduleIdArr = ScheduleParticipant::where('user_id',Auth::id())->pluck('schedule_id');
        $ownEnterpriseArr = User::where('enterprise_id',Auth::user()->enterprise_id)->pluck('id');
        $schedule = Schedule::where(function ($q2) use ($stDate, $edDate){
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->where(function ($q1) use ($scheduleIdArr,$ownEnterpriseArr){
            $q1->whereIn('id',$scheduleIdArr);
            $q1->orWhereIn('created_user_id',$ownEnterpriseArr);
            $q1->orWhereHas('users', function ($q){
                $q->where('enterprise_id', Auth::user()->enterprise_id);
                $q->whereNotNull('enterprise_id');
            });
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->withTrashed();
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')
            ->orderBy('repeat_kbn')
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->orderBy('st_datetime')->get()->toArray();
        $schedule=$this->getSchFilter($schedule);
        $userRule = User::where(function ($q) {
            $enterpriseParticipant = EnterpriseParticipant::where('user_id', Auth::id())->get()->toArray();
            $res = [];
            foreach ($enterpriseParticipant as $item) {
                $res[] = $item['enterprise_id'];
            }
            $q->whereIn('enterprise_id', $res)->pluck('id')->toArray();
        });
        if (Auth::user()->enterprise_id) {
            $userRule->orWhere('enterprise_id', Auth::user()->enterprise_id)
                ->orWhereHas('enterpriseParticipants', function ($q) {
                    $q->where('enterprise_id', Auth::user()->enterprise_id)->where('agree', '=', '1');
                });
        }
        $userArr = $userRule->orderBy('id')->get(['id', 'name']);
        foreach ($schedule as $item){
            if ($item['st_span'] && $item['ed_span']){
                $item['ed_datetime'] = $item['ed_span'].' '.date('H:i:s',strtotime($item['ed_datetime']));
            }
        }
        $schItem = $this->getSchList($stDate, $edDate, $schedule, 7);
        $id = Auth::id();
        $name = Auth::user()->name;

        $proList = $this->addProjectSchedules($stDate, $edDate, $userArr);

        // 六曜、祝日取得
        $japaneseWeek = Common::getJapaneseMonthWithOthers($stDate);
        return [
            "schedule" => $schItem,
            "user" => $userArr,
            "userId" => $id,
            "userName" => $name,
            "japaneseWeek" => $japaneseWeek,
            'proSch' => $proList
        ];
    }
    /**
     * スケジュール参加者の論理的削除
     * */
    private function getSchFilter($schedule)
    {
        foreach ($schedule as $key =>$value){
            $schedule[$key]['users']=array_unique($schedule[$key]['users'], SORT_REGULAR);
            $participants = array_column($schedule[$key]['schedule_participants'],'user_id');
            $userArr=[];
            foreach ($schedule[$key]['users'] as $k =>$v){
                if(in_array($v['id'],$participants)){
                    $userArr[]=$v;
                }
            }
            $schedule[$key]['users']=$userArr;
        }
        return $schedule;
    }

    /**
     *  Array 繰り返しを削除
     * @param $arr
     * @param $key
     *
     * @return mixed
     */
    private function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach($arr as $k => $v) {
            if(in_array($v[$key], $tmp_arr)) {

                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr);
        return $arr;
    }

    private function getSchList($stDate, $edDate, $sch, $type)
    {
        if (!count($sch)) {
            return [];
        }
        $schedule = $sch;
        //サブイベントを追加
        $schedule = $this->addSubSch($stDate, $edDate, $schedule,$type);
        $sch = [];
        foreach ($schedule as $item) {
            $res = $this->getSch($item);
            array_push($sch, $res);
        }
        $arr = [];

        //[スケジュール]ソートをかけた際にスケジュールが二重で表示される　＃3011
        foreach ($sch as $schFilterIndex => $schFilterArrTmp) {
            foreach ($schFilterArrTmp as $schItemIndex => $schItemTmp) {
                $sch[$schFilterIndex][$schItemIndex] = $this->assoc_unique( $schItemTmp, 'id');
            }
        }

        foreach ($sch as $schItem) {
            foreach ($schItem as $index => $res) {
                if (!array_key_exists($index, $arr)) {
                    $arr[$index] = [];
                }
                foreach ($res as $v) {
                    $arr[$index] = $this->putSchEvent($v, $arr[$index], $type);
                }
            }
        }
        $schArr = [];
        foreach ($arr as $index => $sch) {
            $midItem = array();
            if (!array_key_exists($index, $schArr)) {
                $res[] = $index;
            }
            if (!array_key_exists($index, $midItem)) {
                $res[] = $index;
            }
            foreach ($sch as $k => $res) {
                //すべての状況イベントを中断し、1日のイベントにします
                $res = $this->getOrgScheduleDays($res, $k);
                $schArr[$index][] = $res;
            }
        }
        return $schArr;
    }

    /**
     * 既存のイベントと競合するかどうかを確認します
     * @param $data
     * @param $arr
     * @return bool
     */
    private function checkSchConflict($data, $arr)
    {
        if ($data['stDate'] >= $arr['stDate']
            && $data['stDate'] <= $arr['edDate']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 衝突仕様を使用して値を挿入する
     * @param $data
     * @param $arr
     * @param $type
     * @return mixed
     */
    private function putSchEvent($data, $arr, $type)
    {

        $num = count($arr);
        if ($num) {
            for ($i = 0; $i < $num; $i++) {
                //num=1
                $num_row = count($arr[$i]);
                for ($l = 0; $l < $num_row; $l++) {
                    if (!$this->checkSchConflict($data, $arr[$i][$l])) {
                        if ($l == $num_row - 1) {
                            if (count($arr[$i]) < $type) {
                                $arr[$i][] = $data;//次のマスに自働充填する
                                return $arr;
                            }
                        }
                    } else {
                        break 1;
                    }
                }
            }
            $arr[$num][0] = $data;
            //適当な位置を見つけることができないまま、索引を新たに作って1位に置く
            return $arr;
        } else {
            $arr[0][0] = $data;
            return $arr;
        }
    }

    /**
     * 获取userId
     * @param $item
     * @return mixed
     */
    private function getSch($item)
    {
        $res = [];
        foreach ($item['users'] as $arrV) {
            $key = $arrV['id'];
            $res[$key][] = $item;
        }
        return $res;
    }

    /**
     * 連続デイイベント整理する
     * @param $schedule
     * @param $index
     * @return array
     */
    private function getOrgScheduleDays($schedule, $index)
    {
        $crossDaysSchedule = [];
        foreach ($schedule as $key => $item) {
            if ($item['id'] < 0){
                $item['count'] = $this->getNowDateRang($item['stDate'], $item['edDate']);
                $item['flag'] = 1;
                $crossDaysSchedule[$item['stDate']][] = $item;
            } elseif ($item['stDate'] != $item['edDate'] && $item['id'] >= 0) {
                if ($index < 0) {
                    $stDateTime = date_create($item['stDate'] . $item['startTime']);
                    $edDateTime = date_create($item['edDate'] . $item['finishTime']);
                    $diffDay = date_diff($edDateTime, $stDateTime, true)->format("%a");
                    for ($i = 0; $i <= intval($diffDay); $i++) {
                        $sch = [];
                        $sch['id'] = $item['id'];
                        $sch['subject'] = $item['subject'];
                        $sch['stDate'] = date("Y/m/d", strtotime("$item[stDate] +$i day"));
                        $sch['oldStDate'] = $item['stDate'];
                        $sch['edDate'] = $item['edDate'];
                        $sch['startTime'] = $item['startTime'];
                        $sch['finishTime'] = $item['finishTime'];
                        $sch['content'] = $item['content'];
                        $sch['type'] = $item['type'];
                        $sch['repeat_kbn'] = $item['repeat_kbn'];
                        $sch['address'] = $item['address'];
                        $item['count'] = 1;
                        $sch['flag'] = 1;
                        //データを入力する
                        $sch['index'] = $i;
                        //subId
                        $sch['subId'] = 0;
                        //登録者
                        $sch['createUser'] = $item['createUser'];
                        $sch['createDate'] = $item['createDate'];
                        //更新者
                        $sch['updateUser'] = $item['updateUser'];
                        $sch['updateDate'] = $item['updateDate'];
                        $sch['participantUsers'] = $item['participantUsers'];
                        $sch['color'] = $item['color'];
                        $sch['all_day'] = $item['all_day'];
                        $crossDaysSchedule[$sch['stDate']][] = $sch;
                    }
                } else {
                    $item['count'] = $this->getNowDateRang($item['stDate'], $item['edDate']);
                    $item['flag'] = 1;
                    $crossDaysSchedule[$item['stDate']][] = $item;
                }
            } else {
                if (strtotime($item['stDate'] . $item['finishTime']) <
                    strtotime($item['stDate'] . $item['startTime'])) {
                    $day = 1;
                    $item['edDate'] = date("Y/m/d", strtotime("$item[edDate] +$day day"));
                }
                $item['count'] = 1;
                $crossDaysSchedule[$item['stDate']][] = $item;
            }
        }
        return $crossDaysSchedule;
    }


    private function isAcrossWeek($stDate, $edDate)
    {
        $nowDateArray = [];
        $sDate = date_create($stDate);
        $eDate = date_create($edDate);
        $a = date_diff($sDate, $eDate, true)->format("%a") + 1;
        for ($i = 0; $i < $a; $i++) {
            $midDate = date("w", strtotime("$stDate   +$i   day"));
            if ($midDate == 0 || $midDate == 6) {
                $nowDateArray[] = $midDate;
                if ((in_array(0, $nowDateArray) && in_array(6, $nowDateArray) && $a < 7) || $a > 7) {
                    return true;
                } elseif ($a == 7 && $nowDateArray[0] != 0) {
                    return true;
                }
            }
        }
        return false;
    }

    private function addSubSch($stDate, $edDate, $schedule,$type)
    {
        $crossDaysSchedule = [];
        foreach ($schedule as $index => $item) {
            $st_datetime=$item['st_datetime'];
            $ed_datetime=$item['ed_datetime'];
            $schSt = date("Y/m/d", strtotime("$item[st_datetime]"));
            $schEd = date("Y/m/d", strtotime("$item[ed_datetime]"));
            //サブイベント
            if ($item['repeat_kbn'] != '0') {
                foreach ($item['schedule_subs'] as $arr) {
                    if (date_create($arr['s_date']) >= date_create($stDate)
                        && date_create($arr['s_date']) <= date_create($edDate)) {
                        $sch = [];
                        $sch['id'] = $item['id'];
                        $sch['invite'] = $item['invite'];
                        $sch['subject'] = $item['subject'];
                        $sch['stDate'] = date("Y/m/d", strtotime("$arr[s_date]"));
                        $sch['edDate'] = date("Y/m/d", strtotime("$arr[s_date]"));
                        $sch['startTime'] = date("H:i", strtotime("$item[st_datetime]"));
                        $sch['finishTime'] = date("H:i", strtotime("$item[ed_datetime]"));
                        $sch['st_datetime'] = date('Y/m/d',strtotime($st_datetime));
                        $sch['ed_datetime'] = date('Y/m/d',strtotime($ed_datetime));
                        $sch['st_time'] = date('H:i',strtotime($st_datetime));
                        $sch['ed_time'] = date('H:i',strtotime($ed_datetime));
                        $sch['content'] = $item['comment'];
                        $sch['type'] = $item['type'];
                        $sch['repeat_kbn'] = $item['repeat_kbn'];
                        $sch['address'] = $item['location'];
                        $sch['users'] = $item['users'];
                        $item['count'] = 1;
                        $sch['flag'] = 0;
                        //データを入力する
                        $sch['index'] = 0;
                        //subId
                        $sch['subId'] = $arr['id'];
                        //登録者
                        $uc = $this->get_user($item['created_user_id']);
                        $sch['createUser'] = $uc;
                        $sch['createDate'] = $item['created_at'];
                        //更新者
                        if ($item['updated_at']) {
                            $uu = $this->get_user($item['updated_user_id']);
                            $sch['updateUser'] = $uu;
                            $sch['updateDate'] = $item['updated_at'];
                        } else {
                            $sch['updateUser'] = '';
                            $sch['updateDate'] = '';
                        }
                        $sch['participantUsers'] = $item['users'];
                        $sch['color'] = $this->modify_color($item['type'], $item['repeat_kbn']);
                        $sch['all_day'] = $item['all_day'];
                        $crossDaysSchedule[] = $sch;
                    }
                }
            } elseif ($item['repeat_kbn'] == '0' && $schSt != $schEd) {
                //サイクルなし、週にまたがるイベント/月にまたがるイベント
                if ($this->getNowDateRang($stDate, $edDate) > 7) {
                    //月の半月にまたがるイベント
                    if (date("Y/m/d", strtotime($item['st_datetime']))
                        < date("Y/m/d", strtotime($stDate))) {
                        $item['st_datetime'] = date("Y/m/d H:i", strtotime($stDate));
                    }
                    if (date("Y/m/d", strtotime($item['ed_datetime'])) >
                        date("Y/m/d", strtotime($edDate))) {
                        $item['ed_datetime'] = date("Y/m/d H:i", strtotime($edDate));
                    }
                    //半月のクロスウィークイベント
                    if ($this->isAcrossWeek($item['st_datetime'], $item['ed_datetime'])) {

                        $startSchDay = date('w', strtotime($item['st_datetime']));
                        $day = $this->getNowDateRang($item['st_datetime'], $item['ed_datetime']);
                        $midDay = intval(($day + $startSchDay - 7) / 7);

                        if ($midDay > 0) {
                            $ed = intval(7 - $startSchDay - 1);
                            $item['edDate'] = date("Y/m/d", strtotime("$item[st_datetime] +$ed day"));
                            //個人月
                            if($item['id'] == -1){
                                $crossDaysSchedule[] = $this->normal($item, null, $item['edDate'],$st_datetime,$ed_datetime);
                            }else{
                                if($type >7){
                                    $crossDaysSchedule[] = $this->normal($item,null,$item['edDate'],$st_datetime,$ed_datetime);
                                }else{
                                    $arr=$this->repeat($item,$item['st_datetime'],$item['edDate'],$st_datetime,$ed_datetime);
                                    $crossDaysSchedule=array_merge($crossDaysSchedule,$arr);
                                }
                            }
                            $st = 1;
                            $ed = 6;
                            //個人月
                            if($type >7){
                                for ($n = 0; $n < $midDay; $n++) {
                                    $item['stDate'] = date("Y/m/d", strtotime("$item[edDate] +$st day"));
                                    $item['edDate'] = date("Y/m/d", strtotime("$item[stDate] +$ed day"));
                                    $crossDaysSchedule[] = $this->normal($item,$item['stDate'], $item['edDate'],$st_datetime,$ed_datetime);
                                }
                            }else{
                                for ($n = 0; $n < $midDay; $n++) {
                                    $item['stDate'] = date("Y/m/d", strtotime("$item[edDate] +$st day"));
                                    $item['edDate'] = date("Y/m/d", strtotime("$item[stDate] +$ed day"));
                                    if($item['id'] == -1){
                                        $crossDaysSchedule[] = $this->normal($item, $item['stDate'], $item['edDate'],$st_datetime,$ed_datetime);
                                    }else{
                                        $arr=$this->repeat($item,$item['stDate'],$item['edDate'],$st_datetime,$ed_datetime);
                                        $crossDaysSchedule=array_merge($crossDaysSchedule,$arr);
                                    }
                                }
                            }
                            $item['stDate'] = date("Y/m/d", strtotime("$item[edDate] +$st day"));
                            $item['edDate'] = date("Y/m/d", strtotime($item['ed_datetime']));
                            if($item['id'] == -1){
                                $crossDaysSchedule[] = $this->normal($item, $item['stDate'], $item['edDate'],$st_datetime,$ed_datetime);
                            }else{
                                if($type >7){
                                    $crossDaysSchedule[] = $this->normal($item, $item['stDate'], $item['edDate'],$st_datetime,$ed_datetime);
                                }else{
                                    $arr=$this->repeat($item,$item['stDate'],$item['edDate'],$st_datetime,$ed_datetime);
                                    $crossDaysSchedule=array_merge($crossDaysSchedule,$arr);
                                }
                            }
                        } else {
                            $ed = intval(7 - $startSchDay - 1);
                            $item['edDate'] = date("Y/m/d", strtotime("$item[st_datetime] +$ed day"));
                            $crossDaysSchedule[] = $this->normal($item, null, $item['edDate'],$st_datetime,$ed_datetime);
                            $st = (7 - $startSchDay);
                            $item['stDate'] = date("Y/m/d", strtotime("$item[st_datetime] +$st day"));
                            $item['edDate'] = date("Y/m/d", strtotime($item['ed_datetime']));

                            $crossDaysSchedule[] = $this->normal($item, $item['stDate'], $item['edDate'],$st_datetime,$ed_datetime);
                        }
                    } else {
                        //週を超えず、月を超えない
                        $crossDaysSchedule[] = $this->normal($item,null,null,$st_datetime,$ed_datetime);
                    }
                } else {
                    //週半ばのクロスウィークイベント
                    if ($this->isAcrossWeek($item['st_datetime'], $item['ed_datetime'])) {
                        if (date("Y/m/d", strtotime($item['st_datetime']))
                            < date("Y/m/d", strtotime($stDate))) {
                            $item['st_datetime'] = date("Y/m/d H:i", strtotime($stDate));
                        }
                        if (date("Y/m/d", strtotime($item['ed_datetime'])) >
                            date("Y/m/d", strtotime($edDate))) {
                            $item['ed_datetime'] = date("Y/m/d H:i", strtotime($edDate));
                        }
                        if($item['id'] == -1) {
                            $crossDaysSchedule[] = $this->normal($item,null,null,$st_datetime,$ed_datetime);
                        }
                    }elseif($item['id'] == -1) {
                        //週中半の通常のイベント
                        $crossDaysSchedule[] = $this->normal($item,null,null,$st_datetime,$ed_datetime);
                    }
                    if($item['id'] != -1){
                        $arr=$this->repeat($item,$item['st_datetime'],$item['ed_datetime'],$st_datetime,$ed_datetime);
                        $crossDaysSchedule=array_merge($crossDaysSchedule,$arr);
                    }
                }
            } else {
                //循環なし、誇張なし
                $crossDaysSchedule[] = $this->normal($item,null,null,$st_datetime,$ed_datetime);
            }
        }
        return $crossDaysSchedule;
    }

    public function repeat($item,$stDate,$edDate,$st_datetime,$ed_datetime){
        $crossDaysSchedule=[];
        for ($i = strtotime(explode(' ',$stDate)[0]);$i <= strtotime(explode(' ',$edDate)[0]);$i+=86400){
            $sch = [];
            $sch['id'] = $item['id'];
            $sch['invite'] = $item['invite'];
            $sch['subject'] = $item['subject'];
            $sch['stDate'] = date("Y/m/d", $i);
            $sch['edDate'] = date("Y/m/d", $i);
            $sch['startTime'] = date("H:i", strtotime("$stDate"));
            $sch['finishTime'] = date("H:i", strtotime("$edDate"));
            $sch['st_datetime'] = date('Y/m/d',strtotime($st_datetime));
            $sch['ed_datetime'] = date('Y/m/d',strtotime($ed_datetime));
            $sch['st_time'] = date('H:i',strtotime($st_datetime));
            $sch['ed_time'] = date('H:i',strtotime($ed_datetime));
            $sch['content'] = $item['comment'];
            $sch['type'] = $item['type'];
            $sch['repeat_kbn'] = $item['repeat_kbn'];
            $sch['address'] = $item['location'];
            $sch['users'] = $item['users'];
            $item['count'] = 1;
            $sch['flag'] = 0;
            //データを入力する
            $sch['index'] = 0;
            //subId
            $sch['subId'] = $item['id'];
            //登録者
            $uc = $this->get_user($item['created_user_id']);
            $sch['createUser'] = $uc;
            $sch['createDate'] = $item['created_at'];
            //更新者
            if ($item['updated_at']) {
                $uu = $this->get_user($item['updated_user_id']);
                $sch['updateUser'] = $uu;
                $sch['updateDate'] = $item['updated_at'];
            } else {
                $sch['updateUser'] = '';
                $sch['updateDate'] = '';
            }
            $sch['participantUsers'] = $item['users'];
            $sch['color'] = $this->modify_color($item['type'], $item['repeat_kbn']);
            $sch['all_day'] = $item['all_day'];
            $crossDaysSchedule[] = $sch;
        }
        return $crossDaysSchedule;
    }
    private function normal($item, $schSt = null, $schEd = null,$st_datetime,$ed_datetime)
    {
        $sch = [];
        $sch['id'] = $item['id'];
        $sch['invite'] = $item['invite'];
        $sch['subject'] = $item['subject'];
        if ($schSt) {
            $sch['stDate'] = $schSt;
        } else {
            $sch['stDate'] = date("Y/m/d", strtotime("$item[st_datetime]"));
        }
        if ($schEd) {
            $sch['edDate'] = $schEd;
        } else {
            $sch['edDate'] = date("Y/m/d", strtotime("$item[ed_datetime]"));
        }
        $sch['startTime'] = date("H:i", strtotime("$item[st_datetime]"));
        $sch['finishTime'] = date("H:i", strtotime("$item[ed_datetime]"));
        $sch['st_datetime'] = date('Y/m/d',strtotime($st_datetime));
        $sch['ed_datetime'] = date('Y/m/d',strtotime($ed_datetime));
        $sch['st_time'] = date('H:i',strtotime($st_datetime));
        $sch['ed_time'] = date('H:i',strtotime($ed_datetime));
        $sch['content'] = $item['comment'];
        $sch['type'] = $item['type'];
        $sch['repeat_kbn'] = $item['repeat_kbn'];
        $sch['address'] = $item['location'];
        $sch['users'] = $item['users'];
        $sch['all_day'] = $item['all_day'];
        $item['count'] = 1;
        $sch['flag'] = 0;
        //データを入力する
        $sch['index'] = 0;
        //subId
        $sch['subId'] = 0;
        //登録者
        $uc = $this->get_user($item['created_user_id']);
        $sch['createUser'] = $uc;
        $sch['createDate'] = $item['created_at'];
        //更新者
        if ($item['updated_at']) {
            $uu = $this->get_user($item['updated_user_id']);
            $sch['updateUser'] = $uu;
            $sch['updateDate'] = $item['updated_at'];
        } else {
            $sch['updateUser'] = '';
            $sch['updateDate'] = '';
        }
        $sch['participantUsers'] = $item['users'];
        $sch['color'] = $this->modify_color($item['type'], $item['repeat_kbn']);
        return $sch;
    }

    /**
     * 時間範囲の日を取得する
     * @param $stDate
     * @param $edDate
     * @return int|string
     */
    private function getNowDateRang($stDate, $edDate)
    {
        $sDate = date_create($stDate);
        $eDate = date_create($edDate);
        return date_diff($sDate, $eDate, true)->format("%a") + 1;
    }


    /**
     * 追加案件のスケジュール
     * @param $stDate
     * @param $edDate
     * @param $userArr
     * @return array
     */
    private function addProjectSchedules($stDate, $edDate, $userArr)
    {
        $schedule = [];
        // 案件スケジュール取得
        $projectSchedules = Project::where(function ($q) use ($stDate, $edDate) {
            $q->whereBetween('st_date', [$stDate, $edDate])
                ->orWhereBetween('ed_date', [$stDate, $edDate])
                ->orwhere('st_date', '<=', $stDate)
                ->where('ed_date', '>=', $edDate);
        })->whereHas('projectParticipant',function ($q){
            $q->where('user_id',Auth::id());
        })->whereNotNull('st_date')->whereNotNull('ed_date')->with('projectParticipant.user')
            ->orderBy('st_date')->get()->toArray();
        if (!count($projectSchedules)) {
            return [];
        }
        $idArr = [];
        foreach ($userArr as $u) {
            $idArr[] = $u['id'];
        }
        $idArr[] = Auth::id();
        foreach ($projectSchedules as $project) {
            $sch = [];
            $sch['id'] = -1;
            $sch['invite'] = 1;
            $sch['st_datetime'] = date("Y/m/d H:i", strtotime($project['st_date']));
            $sch['ed_datetime'] = date("Y/m/d 23:59", strtotime($project['ed_date']));
            $sch['subject'] = $project['place_name'];
            $sch['type'] = 0;
            $sch['comment'] = '';
            $sch['location'] = '';
            $sch['created_user_id'] = $project['created_by'];
            $sch['updated_user_id'] = '';
            $sch['created_at'] = $project['created_at'];
            $sch['updated_at'] = $project['updated_at'];
            $sch['repeat_kbn'] = '0';
            $sch['users'] = [];
            $sch['all_day'] = 0;
            if (count($project['project_participant'])) {
                foreach ($project['project_participant'] as $p) {
                    if (in_array($p['user']['id'], $idArr)) {
                        $sch['users'][] = $p['user'];
                    }
                }
            }
            // 案件協力会社と作成者
            $schedule[] = $sch;
        }
        $schItem = $this->getSchList($stDate, $edDate, $schedule, 7);
        return $schItem;
    }


    /**
     * スケジュール 個人週間画面　一覧
     * @param Request $request
     * @return mixed
     */
    public function indexSchWeek(Request $request)
    {
        $stDate = $request->post('stDate');
        $edDate = $request->post('edDate');
        $edDate=date('Y/m/d H:i',strtotime("$edDate -1 Minute"));
        $schedule = Schedule::where(function ($q) use ($stDate, $edDate) {
            $q->whereBetween('st_span', [$stDate, $edDate])
                ->orWhereBetween('ed_span', [$stDate, $edDate])
                ->orwhere('st_span', '<=', $stDate)
                ->where('ed_span', '>=', $edDate);
        })
            ->whereHas('users', function ($q) {
                $q->where('user_id', Auth::id());
            })->with('users')
            ->with('scheduleSubs')
            ->get();

        $id = Auth::id();
        return ["schedule" => $schedule, 'userId' => $id,];
    }


    /**
     * アスケジュール テーブルschedule削除
     * @param Request $request
     * @return string
     */
    public function deleteSchScheduleWeek(Request $request)
    {
        $userId = $request->post('userId');
        $scheduleId = $request->post('id');
        $subId = $request->post('subId');
        $updateType = $request->post('updateType');
        $updateDate = $request->post('updateDate');
        $old_title = $request->post('title');
        DB::beginTransaction();
        try {
            $participantArr = ScheduleParticipant::where('schedule_id', $scheduleId)->get('user_id')->toArray();
            $schedule=Schedule::where('id',$scheduleId)->get()->toArray();
            $fireBase = new FirebaseService();
            $dashboard = new DashboardController();
            $title= '予定削除 '.$schedule[0]['st_datetime']." ".$old_title;
            $date = date('m/d H:i');
            foreach ($participantArr as $participant) {
                if ($participant['user_id'] != Auth::id()) {
                    //3：スケジュール
                    $dashboard->addDashboard(
                        $scheduleId,3, $old_title.'の予定が削除されました。（'.$date.'）','',$participant['user_id']
                    );
                    $fireBase->pushSchedule($participant['user_id'], $scheduleId, $subId, $title,'deleteSchedule');
                }
            }
            //重複:毎日 / 毎週
            if ($subId) {
                $updateDate = $this->checkCrossDay($scheduleId,$schedule[0]['st_datetime'],$schedule[0]['ed_datetime'],$schedule[0]['all_day'],$schedule[0]['repeat_kbn'],$subId,$updateDate);
                //1:その日のみ
                if ($updateType == '1') {
                    $ed_time = date('H:i:s', strtotime($schedule[0]['ed_datetime'])); //ed time H:i:s
                    $ed_datetime = date('Y-m-d', strtotime($updateDate) - 1); //ed day Y-m-d
                    $st_time = date('H:i:s', strtotime($schedule[0]['st_datetime'])); //st time H:i:s
                    $st_datetime = date('Y-m-d', strtotime($updateDate) + 86400);//st day Y-m-d
                    $oldParticipants = ScheduleParticipant::where('schedule_id', $scheduleId)->pluck('user_id')->toArray();
                    switch ($schedule[0]['repeat_kbn']) {
                        case '1':
                            //每日
                            //現在の日付を削除する
                            ScheduleSub::where('relation_id', '=', $scheduleId)->where('s_date', '=', $updateDate)->delete();
                            Schedule::where('id', $scheduleId)->update(['updated_at' => date('Y/m/d H:i:s')]);

                            if ($schedule[0]['st_span'] == $updateDate) {
                                Schedule::where('id', $scheduleId)->update(['st_datetime' => $st_datetime . ' ' . $st_time, 'st_span' => $st_datetime, 'updated_at' => date('Y/m/d H:i:s')]);
                            } elseif ($schedule[0]['ed_span'] == $updateDate) {
                                Schedule::where('id', $scheduleId)->update(['ed_datetime' => $ed_datetime . ' ' . $ed_time, 'ed_span' => $ed_datetime, 'updated_at' => date('Y/m/d H:i:s')]);
                            } else {
                                //delete middle day in the schedule
                                //delete schedule
                                ScheduleSub::where('relation_id', $scheduleId)->delete();
                                //before update day
                                $firstEdDateTime = $ed_datetime . ' ' . $ed_time;
                                $firstToUpdateDiff = date_diff(date_create($schedule[0]['st_span']), date_create($ed_datetime), true)->format("%a");
                                Schedule::setEveryDay($schedule[0]['st_datetime'], $firstEdDateTime, $schedule[0]['type'], $schedule[0]['subject'],
                                    $schedule[0]['comment'], $schedule[0]['st_span'], $ed_datetime, $firstToUpdateDiff, $schedule[0]['open_range'],
                                    $schedule[0]['notify'], $schedule[0]['notify_min_ago'], $schedule[0]['all_day'], $oldParticipants, Auth::id(), $schedule[0]['location'],$scheduleId);
                                //after update day
                                $lastStDateTime = $st_datetime . ' ' . $st_time;
                                $updateToLastDiff = date_diff(date_create($st_datetime), date_create($schedule[0]['ed_datetime']), true)->format("%a");
                                Schedule::setEveryDay($lastStDateTime, $schedule[0]['ed_datetime'], $schedule[0]['type'], $schedule[0]['subject'],
                                    $schedule[0]['comment'], $st_datetime, $schedule[0]['ed_span'], $updateToLastDiff,
                                    $schedule[0]['open_range'], $schedule[0]['notify'], $schedule[0]['notify_min_ago'], $schedule[0]['all_day'], $oldParticipants, Auth::id(), $schedule[0]['location']);
                            }

                            break;
                        case '3':
                            //每月
                            $originalSubDateList = ScheduleSub::where('relation_id', $scheduleId)->orderBy('s_date','asc')->pluck('s_date')->toArray();
                            $length = count($originalSubDateList);
                            if ($length <= 1){
                                ScheduleSub::where('relation_id', $scheduleId)->delete();
                                Schedule::where('id', $scheduleId)->delete();
                            }else{
                                //check the day's index (which waiting for deleting) in "length" array
                                $deleteDayIndex = array_search($updateDate,$originalSubDateList);
                                if ($deleteDayIndex == 0) {
                                    //is first sub
                                    ScheduleSub::where('relation_id', '=', $scheduleId)->where('s_date', '=', $updateDate)->delete();
                                    $newStDate = $originalSubDateList[$deleteDayIndex + 1];
                                    Schedule::where('id', $scheduleId)->update([
                                        'st_datetime' => $newStDate . ' ' . date('H:i:s', strtotime($schedule[0]['st_datetime'])),
                                        'st_span' => $newStDate,
                                        'updated_at' => date('Y/m/d H:i:s'),
                                        'updated_user_id' => Auth::id(),
                                    ]);
                                }elseif ($deleteDayIndex == $length - 1){
                                    //is last sub
                                    ScheduleSub::where('relation_id', '=', $scheduleId)->where('s_date', '=', $updateDate)->delete();
                                    $newEdDate = date('Y-m-d',strtotime("$updateDate - 1 days"));
                                    Schedule::where('id', $scheduleId)->update([
                                        'ed_datetime' => $newEdDate . ' ' . date('H:i:s', strtotime($schedule[0]['ed_datetime'])),
                                        'ed_span' => $newEdDate,
                                        'updated_at' => date('Y/m/d H:i:s'),
                                        'updated_user_id' => Auth::id(),
                                    ]);
                                }else{
                                    //is middle sub
                                    ScheduleSub::where('relation_id', $scheduleId)->delete();
                                    //before
                                    $beforeEdDate = date('Y-m-d', strtotime("$updateDate - 1 days"));
                                    $interval = date_diff(date_create($schedule[0]['st_span']), date_create($beforeEdDate), true);
                                    $monthdiff = $interval->format('%y') * 12 + $interval->format('%m');
                                    Schedule::setEveryMonth($schedule[0]['st_datetime'], $beforeEdDate . ' ' . date('H:i:s', strtotime($schedule[0]['ed_datetime'])), $schedule[0]['type']
                                        , $schedule[0]['subject'], $schedule[0]['comment'], $schedule[0]['st_span']
                                        , $beforeEdDate, $monthdiff, $schedule[0]['open_range']
                                        , $schedule[0]['notify'], $schedule[0]['notify_min_ago'], $schedule[0]['all_day'], $oldParticipants, Auth::id(), $schedule[0]['location'],$scheduleId);
                                    //after
                                    $afterStDate = $originalSubDateList[$deleteDayIndex + 1];
                                    $interval = date_diff(date_create($afterStDate), date_create($schedule[0]['ed_span']), true);
                                    $monthdiff = $interval->format('%y') * 12 + $interval->format('%m');
                                    Schedule::setEveryMonth($afterStDate . ' ' . date('H:i:s', strtotime($schedule[0]['st_datetime'])), $schedule[0]['ed_datetime'], $schedule[0]['type']
                                        , $schedule[0]['subject'], $schedule[0]['comment'], $afterStDate
                                        , $schedule[0]['ed_span'], $monthdiff, $schedule[0]['open_range']
                                        , $schedule[0]['notify'], $schedule[0]['notify_min_ago'], $schedule[0]['all_day'], $oldParticipants, Auth::id(), $schedule[0]['location']);
                                }
                            }
                            break;
                        case '4':
                            //毎週
                            ScheduleSub::where('relation_id', $scheduleId)->delete();
                            Schedule::where('id', $scheduleId)->delete();
                            $weekArray = [
                                1 => $schedule[0]['week1'],
                                2 => $schedule[0]['week2'],
                                3 => $schedule[0]['week3'],
                                4 => $schedule[0]['week4'],
                                5 => $schedule[0]['week5'],
                                6 => $schedule[0]['week6'],
                                7 => $schedule[0]['week7'],
                            ];
                            //before update day
                            Schedule::setWeek($schedule[0]['st_datetime'],
                                $ed_datetime . ' ' . $ed_time,
                                $schedule[0]['type'], $schedule[0]['subject'], $schedule[0]['comment'], $schedule[0]['st_span'],
                                $ed_datetime, 0, $weekArray, $schedule[0]['open_range']
                                , $schedule[0]['notify'], $schedule[0]['notify_min_ago'], $schedule[0]['all_day'], $oldParticipants, Auth::id(), $schedule[0]['location']);
                            //after update day
                            Schedule::setWeek(
                                $st_datetime . ' ' . $st_time,
                                $schedule[0]['ed_datetime'], $schedule[0]['type'], $schedule[0]['subject'],
                                $schedule[0]['comment'],
                                $st_datetime, $schedule[0]['ed_span'],
                                0, $weekArray,
                                $schedule[0]['open_range'], $schedule[0]['notify'], $schedule[0]['notify_min_ago'], $schedule[0]['all_day'], $oldParticipants, Auth::id(), $schedule[0]['location']);
                            break;
                        default:
                            break;
                    }
                } else {
                    //2:その日以後すべて
                    if ($updateType == '2') {
                        //每日 & 每周 & 每月
                        ScheduleSub::where('relation_id', '=', $scheduleId)->where('s_date', '>=',
                            $updateDate)->delete();
                        $remainSubCount = ScheduleSub::where('relation_id', '=', $scheduleId)->count();
                        if ($remainSubCount > 0) {
                            $ed_time = date('H:i:s', strtotime($schedule[0]['ed_datetime']));
                            $ed_datetime = date('Y-m-d', strtotime($updateDate) - 1);
                            Schedule::where('id', $scheduleId)->update(['ed_datetime' => $ed_datetime . ' ' . $ed_time, 'ed_span' => $ed_datetime, 'updated_at' => date('Y/m/d H:i:s')]);
                        }
                    } else {
                        //3:すべて
                        if ($updateType == '3') {
                            Schedule::destroy($scheduleId);
                            DB::table('schedule_changes')->insert(['schedule_id'=>$scheduleId]);
                        }
                    }
                }
                if ($updateType == '1' || $updateType == '2') {
                    $subLength = ScheduleSub::where('relation_id', $scheduleId)->count();
                    if ($subLength == 0) {
                        $this->deleteCommon($userId, $scheduleId);
                    }
                }
            } else {
                //重複なし
                $this->deleteScheduleNoRepeat($scheduleId);
            }
            DB::commit();
        } catch (\PDOException $e) {
            //データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, $error);
        }
        return $this->json();
    }

    /**
     * 重複なしのscheduleの削除
     * @param $userId
     * @param $id
     */
    private function deleteScheduleNoRepeat($id)
    {
        ScheduleParticipant::where('schedule_id', $id)->delete();
        Schedule::where('id', $id)->delete();
        DB::table('schedule_changes')->insert(['schedule_id'=>$id]);
    }

    /**
     * スケジュール個人・月間一覧
     * @param Request $request
     * @return mixed
     */
    public function getScheduleSubjects(Request $request)
    {
        $stDate = $request->post('stDate');
        $edDate = $request->post('edDate');
        $edDate=date('Y/m/d H:i',strtotime("$edDate -1 Minute"));
        // 六曜、祝日取得
        $japaneseDate = Common::getJapaneseMonthWithOthers($stDate);
        //時間の範囲を広げる
        $stDateWeekSort = date('w',strtotime($stDate));
        $edDateWeekSort = date('w',strtotime($edDate));
        $stDate = date('Y/m/d', strtotime('-'.($stDateWeekSort+1).' days', strtotime($stDate)));
        //#2567 繰り返し なし　cannot display schedule at the end in this month
        $edDate = date('Y/m/d', strtotime('+'.(7-$edDateWeekSort).' days', strtotime($edDate)));
        //なし
        $schedule = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
            $q->whereNull('scheduleparticipants.deleted_at');
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->whereNull('scheduleparticipants.deleted_at');
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')->where('repeat_kbn',0)
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->get()->toArray();

        //繰り返す
        $schedule_rep = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
            $q->whereNull('scheduleparticipants.deleted_at');
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->whereNull('scheduleparticipants.deleted_at');
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')->where('repeat_kbn','>',0)
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->get()->toArray();
        $schedule=$this->getSchFilter($schedule);
        $schedule_rep=$this->getSchFilter($schedule_rep);//スケジュール個人・月間参加者の論理的削除
        $id = Auth::id();
        //get nothing
        if (empty($schedule) && empty($schedule_rep)){
            return ["schedule" => $schedule, 'userId' => $id, 'japaneseDate' => $japaneseDate, 'scheduleSelf' => [], 'scheduleSelfCount' => 0];
        }

        foreach ($schedule as $key =>$value){
            if(explode(' ',$value['ed_datetime'])[0]==explode(' ',$value['st_datetime'])[0]){
                $schedule_rep[]=$value;
                unset($schedule[$key]);
            }
        }
        $schedule=array_values($schedule);
        foreach ($schedule_rep as $key =>$value){
            $value['time']=strtotime($value['st_datetime'])-strtotime(explode(' ',$value['st_datetime'])[0]);
            $schedule_rep[$key]=$value;
        }
        $last = array_column($schedule_rep, 'time');
        array_multisort($last, SORT_ASC, $schedule_rep);
        $schedule=array_merge($schedule,$schedule_rep);
        foreach ($schedule as $item){
            //対応アプリ
            if ($item['st_span'] && $item['ed_span']){
                $item['ed_datetime'] = $item['ed_span'].' '.date('H:i:s',strtotime($item['ed_datetime']));
            }
        }
        $range = $this->getNowDateRang($stDate, $edDate);
        $schedule = $this->getSchList($stDate, $edDate, $schedule, $range);
        //nothing after process data
        if (empty($schedule)){
            return ["schedule" => $schedule, 'userId' => $id, 'japaneseDate' => $japaneseDate, 'scheduleSelf' => [], 'scheduleSelfCount' => 0];
        }

        //process count
        $dateStReform=date_create($stDate);
        $dateEdReform=date_create($edDate);
        $daysCountObj = date_diff($dateStReform, $dateEdReform);
        $daysCount = $daysCountObj->days;

        $scheduleSelf = $schedule[$id];
        $scheduleSelfResort = [];
        $scheduleSelfResortCount = [];
        for ($i = 0; $i < $daysCount; $i++) {
            $dayCheck = date('Y/m/d',strtotime($stDate . ' +' . $i . 'days'));
            $scheduleSelfResort[$dayCheck] = $this->getSelfDaySchedule($dayCheck, $scheduleSelf);
            $scheduleSelfResortCount[$dayCheck] = count($scheduleSelfResort[$dayCheck]);
        }

        return ["schedule" => $schedule, 'userId' => $id, 'japaneseDate' => $japaneseDate, 'scheduleSelf' => $scheduleSelfResort, 'scheduleSelfCount' => $scheduleSelfResortCount];
        // return ['scheduleSelf' => $scheduleSelfResort];
    }

    private function getSelfDaySchedule($date, $schedules) {
        $scheduleArr = [];
        for($i = 0; $i < count($schedules); $i++) {
            if(array_key_exists($date, $schedules[$i])) {
                $scheduleArr[] = $schedules[$i][$date];
            }

        }
        return $scheduleArr;
    }

    /**
     *イベント調整の共通方法の削除
     * @param $userId
     * @param $id
     */
    private function deleteCommon($userId, $id)
    {
        ScheduleParticipant::where('user_id', $userId)->where('schedule_id', $id)->delete();
        Schedule::where('id', $id)->delete();
        DB::table('schedule_changes')->insert(['schedule_id'=>$id]);
    }

    public function getUser(Request $request)
    {
        $id = $request->get('id');
        if ($id == 0) {
            return Account::with('enterprise', 'coopEnterprise')->where('id', '=', Auth::id())->first();
        } else {
            return Account::with('enterprise', 'coopEnterprise')->where('id', '=', $id)->first();
        }
    }

    public function getOtherUser(Request $request)
    {
        return Account::with('enterprise')->where('id', '=', $request->get('id'))->first();
    }

    public function checkUserClearGroup(Request $request)
    {
        $flag = 2;
        $groupId = $request->get('groupId');
        $model = Project::where('group_id', '=', $groupId)->first();
        if (!$model) {
            $chatGroup = ChatGroup::where('group_id', '=', $groupId)->where('user_id', '=', Auth::id())->first();
            if ($chatGroup->admin == 1) {
                $flag = 1;
            }
        }
        return $this->json('', $flag);
    }

    //#3031 [スケジュール]「日」の項目の追加について
    public function indexToday(Request $request)
    {
        $stDate=$request->input('stDate');
        $stDate=date("Y-m-d",strtotime($stDate));
        $edDate=date("Y-m-d",strtotime("$stDate +1 day"));
        $edDate=date('Y-m-d H:i',strtotime("$edDate -1 Minute"));
        $scheduleIdArr = ScheduleParticipant::where('user_id',Auth::id())->pluck('schedule_id');
        $ownEnterpriseArr = User::where('enterprise_id',Auth::user()->enterprise_id)->pluck('id');
        //#5256,Expand start date forward ==> Cross-day recurring schedule (e.g. 1st-5th 繰り返し:每日 21:00-07:00)
        $stDateTmp=date('Y-m-d H:i',strtotime("$stDate -1 day"));
        $schedule = Schedule::where(function ($q2) use ($stDateTmp, $edDate){
            $q2->where(function ($q) use ($stDateTmp, $edDate) {
                $q->whereBetween('st_datetime', [$stDateTmp, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDateTmp, $edDate])
                    ->orwhere('st_datetime', '<=', $stDateTmp)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDateTmp, $edDate) {
                $q->whereBetween('st_span', [$stDateTmp, $edDate])
                    ->orWhereBetween('ed_span', [$stDateTmp, $edDate])
                    ->orwhere('st_span', '<=', $stDateTmp)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->where(function ($q1) use ($scheduleIdArr,$ownEnterpriseArr){
            $q1->whereIn('id',$scheduleIdArr);
            $q1->orWhereIn('created_user_id',$ownEnterpriseArr);
            $q1->orWhereHas('users', function ($q){
                $q->where('enterprise_id', Auth::user()->enterprise_id);
                $q->whereNotNull('enterprise_id');
            });
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->withTrashed();
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')
            ->orderBy('repeat_kbn')
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->orderBy('st_datetime')->get();
        $scheduleAllDay = $this->AllDaySchPart($stDate, $edDate);
        $id = Auth::id();
        $schedule_arr = $schedule->toArray();
        //data filter
        $scheduleTmp = [];
        foreach ($schedule_arr as $k => $v){
            if ($v['repeat_kbn'] != 0 && count($v['schedule_subs']) == 0){
                continue;
            }else{
                array_push($scheduleTmp,$v);
            }
        }
        $schedule_arr = $scheduleTmp;

        $schedule_arr=$this->getSchFilter($schedule_arr);
        foreach ($schedule as $item){
            if ($item['st_span'] && $item['ed_span']){
                $item['ed_datetime'] = $item['ed_span'].' '.date('H:i:s',strtotime($item['ed_datetime']));
            }
        }
        //find the special schedule
        $specialSchId = array();
        //check if this schedule item is special
        //add schId to specialSchId array
        foreach ($schedule_arr as $k => $v) {
            $schId = $this->isSpecial($v['id'], $v['st_datetime'], $v['ed_datetime']);
            if ($schId) {
                $specialSchId[] = $schId;
            }
        }

        $arr_filter = array();

        for ($i = 0; $i < count($schedule_arr); $i++) {
            $arr_filter[$i]['sch_id'] = $schedule_arr[$i]['id'];
            $arr_filter[$i]['st_datetime'] = $schedule_arr[$i]['st_datetime'];
            $arr_filter[$i]['ed_datetime'] = $schedule_arr[$i]['ed_datetime'];
            $arr_filter[$i]['type'] = $schedule_arr[$i]['type'];
            $arr_filter[$i]['open_range'] = $schedule_arr[$i]['open_range'];
            $arr_filter[$i]['subject'] = $schedule_arr[$i]['subject'];
            $arr_filter[$i]['comment'] = $schedule_arr[$i]['comment'];
            $arr_filter[$i]['repeat_kbn'] = $schedule_arr[$i]['repeat_kbn'];
            $arr_filter[$i]['st_span'] = $schedule_arr[$i]['st_span'];
            $arr_filter[$i]['ed_span'] = $schedule_arr[$i]['ed_span'];
            $arr_filter[$i]['notify'] = $schedule_arr[$i]['notify'];
            $arr_filter[$i]['notify_min_ago'] = $schedule_arr[$i]['notify_min_ago'];
            $arr_filter[$i]['location'] = $schedule_arr[$i]['location'];
            $arr_filter[$i]['created_at'] = $schedule_arr[$i]['created_at'];
            $arr_filter[$i]['updated_at'] = $schedule_arr[$i]['updated_at'];
            $arr_filter[$i]['created_by'] = $schedule_arr[$i]['created_user_id'];
            $arr_filter[$i]['updated_by'] = $schedule_arr[$i]['updated_user_id'];
            $arr_filter[$i]['users'] = $this->user_filter($schedule_arr[$i]['users']);
            $arr_filter[$i]['schedule_subs'] = $schedule_arr[$i]['schedule_subs'];
            $arr_filter[$i]['all_day'] = $schedule_arr[$i]['all_day'];
        }
        $arr_filter_space = $this->filter_space($arr_filter, $specialSchId);

        for ($i = 0; $i < count($arr_filter_space); $i++) {
            $arr_filter_space[$i]['schedule_subs'] = $this->in_span($arr_filter_space[$i]['schedule_subs'], $stDate,
                $edDate);
        }

        $arr_item = array();
        for ($i = 0; $i < count($arr_filter_space); $i++) {
            if (count($arr_filter_space[$i]['schedule_subs'])) {
                $arr_item[] = $arr_filter_space[$i];
            }
        }

        $arr_item_r = array();
        $arr_item_r['date_str'] = $stDate;
//        dd($this->get_items_date($arr_item, $stDate));
        //$arr_item_r['sch_items_sorted'] = $this->cal_table($this->get_items_date($arr_item, $stDate));
        $arr_item_r['sch_items_sorted'][0] = array_values($this->get_items_date($arr_item, $stDate));
        $arr_item_r['cols_nums'] = count($arr_item_r['sch_items_sorted']);

        $userRule = User::where(function ($q) {
            $enterpriseParticipant = EnterpriseParticipant::where('user_id', Auth::id())->get()->toArray();
            $res = [];
            foreach ($enterpriseParticipant as $item) {
                $res[] = $item['enterprise_id'];
            }
            $q->whereIn('enterprise_id', $res)->pluck('id')->toArray();
        });
        if (Auth::user()->enterprise_id) {
            $userRule->orWhere('enterprise_id', Auth::user()->enterprise_id)
                ->orWhereHas('enterpriseParticipants', function ($q) {
                    $q->where('enterprise_id', Auth::user()->enterprise_id)->where('agree', '=', '1');
                });
        }
        $userArr = $userRule->orderBy('id')->get(['id', 'name'])->toArray();
        //一番左の行は、ログインしたのアカウントの当日のスケジュール
        foreach($userArr as $key=>$user){
            if($user['id'] == $id){
                unset($userArr[$key]);
                array_unshift($userArr,$user);
                break;
            }
        }
        // 六曜、祝日取得
        $japaneseDate = Common::getJapaneseMonthWithOthers($stDate);
        return [ "sorted" => $arr_item_r, 'userId' => $id, 'japaneseDate' => $japaneseDate,'scheduleAllDay'=>$scheduleAllDay,'userArr'=>$userArr];
    }
    public function indexSchWeekTest(Request $request)
    {
        $stDate = $request->post('stDate');
        $edDate = $request->post('edDate');
        $edDate=date('Y/m/d',strtotime("$edDate +1 day"));
        $edDate=date('Y/m/d H:i',strtotime("$edDate -1 Minute"));
        //-1 day
        $stDateTmp = date('Y/m/d',strtotime("$stDate -1 day"));
        $schedule = Schedule::where(function ($q2) use ($stDateTmp, $edDate) {
            $q2->where(function ($q) use ($stDateTmp, $edDate) {
                $q->whereBetween('st_datetime', [$stDateTmp, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDateTmp, $edDate])
                    ->orwhere('st_datetime', '<=', $stDateTmp)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDateTmp, $edDate) {
                $q->whereBetween('st_span', [$stDateTmp, $edDate])
                    ->orWhereBetween('ed_span', [$stDateTmp, $edDate])
                    ->orwhere('st_span', '<=', $stDateTmp)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->where('all_day', 0)
        ->whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
            //＃3125【スケジュール】アプリからスケジュールのメンバーを削除した場合、表示が残る　
            $q->where('scheduleparticipants.deleted_at', null);
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->withTrashed();
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')
            ->get();
        $scheduleAllDay = $this->AllDaySchedule($stDate, $edDate);

        $id = Auth::id();
        $schedule_arr = $schedule->toArray();

        //data filter
        $scheduleTmp = [];
        foreach ($schedule_arr as $k => $v){
            if ($v['repeat_kbn'] != 0 && count($v['schedule_subs']) == 0){
                continue;
            }else{
                array_push($scheduleTmp,$v);
            }
        }
        $schedule_arr = $scheduleTmp;

        $schedule_arr=$this->getSchFilter($schedule_arr);
        foreach ($schedule as $item){
            if ($item['st_span'] && $item['ed_span']){
                $item['ed_datetime'] = $item['ed_span'].' '.date('H:i:s',strtotime($item['ed_datetime']));
            }
        }
        //find the special schedule
        $specialSchId = array();
        //check if this schedule item is special
        //add schId to specialSchId array
        foreach ($schedule_arr as $k => $v) {
            $schId = $this->isSpecial($v['id'], $v['st_datetime'], $v['ed_datetime']);
            if ($schId) {
                $specialSchId[] = $schId;
            }
        }

        $arr_filter = array();

        for ($i = 0; $i < count($schedule_arr); $i++) {
            $arr_filter[$i]['sch_id'] = $schedule_arr[$i]['id'];
            $arr_filter[$i]['st_datetime'] = $schedule_arr[$i]['st_datetime'];
            $arr_filter[$i]['ed_datetime'] = $schedule_arr[$i]['ed_datetime'];
            $arr_filter[$i]['type'] = $schedule_arr[$i]['type'];
            $arr_filter[$i]['open_range'] = $schedule_arr[$i]['open_range'];
            $arr_filter[$i]['subject'] = $schedule_arr[$i]['subject'];
            $arr_filter[$i]['comment'] = $schedule_arr[$i]['comment'];
            $arr_filter[$i]['repeat_kbn'] = $schedule_arr[$i]['repeat_kbn'];
            $arr_filter[$i]['st_span'] = $schedule_arr[$i]['st_span'];
            $arr_filter[$i]['ed_span'] = $schedule_arr[$i]['ed_span'];
            $arr_filter[$i]['notify'] = $schedule_arr[$i]['notify'];
            $arr_filter[$i]['notify_min_ago'] = $schedule_arr[$i]['notify_min_ago'];
            $arr_filter[$i]['location'] = $schedule_arr[$i]['location'];
            $arr_filter[$i]['created_at'] = $schedule_arr[$i]['created_at'];
            $arr_filter[$i]['updated_at'] = $schedule_arr[$i]['updated_at'];
            $arr_filter[$i]['created_by'] = $schedule_arr[$i]['created_user_id'];
            $arr_filter[$i]['updated_by'] = $schedule_arr[$i]['updated_user_id'];
            $arr_filter[$i]['users'] = $this->user_filter($schedule_arr[$i]['users']);
            $arr_filter[$i]['schedule_subs'] = $schedule_arr[$i]['schedule_subs'];
            $arr_filter[$i]['all_day'] = $schedule_arr[$i]['all_day'];
        }
        //1週間=>index
        $date_index = $this->init_date_index($stDate);

        $arr_filter_space = $this->filter_space($arr_filter, $specialSchId);

        for ($i = 0; $i < count($arr_filter_space); $i++) {
            $arr_filter_space[$i]['schedule_subs'] = $this->in_span($arr_filter_space[$i]['schedule_subs'], $stDate,
                $edDate);
        }

        $arr_item = array();
        for ($i = 0; $i < count($arr_filter_space); $i++) {
            if (count($arr_filter_space[$i]['schedule_subs'])) {
                $arr_item[] = $arr_filter_space[$i];
            }
        }

        $arr_item_r = array();
        for ($l = 0; $l < 7; $l++) {
            $arr_item_r[$l]['date_str'] = $date_index[$l];
            $arr_item_r[$l]['sch_items_sorted'] = $this->cal_table($this->get_items_date($arr_item, $date_index[$l]));
            $arr_item_r[$l]['cols_nums'] = count($arr_item_r[$l]['sch_items_sorted']);
        }

        // 六曜、祝日取得
        $japaneseDate = Common::getJapaneseMonthWithOthers($stDate);
        return ["schedule" => $schedule, "sorted" => $arr_item_r, 'userId' => $id, 'japaneseDate' => $japaneseDate,'scheduleAllDay'=>$scheduleAllDay];
    }

    public function AllDaySchedule($stDate,$edDate)
    {
        //時間の範囲を広げる
        $stDateWeekSort = date('w',strtotime($stDate));
        $edDateWeekSort = date('w',strtotime($edDate));
        $stDate = date('Y/m/d', strtotime('-'.($stDateWeekSort+1).' days', strtotime($stDate)));
        //#2567 繰り返し なし　cannot display schedule at the end in this month
        $edDate = date('Y/m/d', strtotime('+'.(7-$edDateWeekSort).' days', strtotime($edDate)));
        //なし
        $schedule = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->withTrashed();
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')->where('repeat_kbn',0)->where('all_day',1)
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->get()->toArray();

        //繰り返す
        $schedule_rep = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->withTrashed();
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')->where('repeat_kbn','>',0)->where('all_day',1)
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->get()->toArray();
        foreach ($schedule as $key =>$value){
            if(explode(' ',$value['ed_datetime'])[0]==explode(' ',$value['st_datetime'])[0]){
                $schedule_rep[]=$value;
                unset($schedule[$key]);
            }
        }
        $schedule=array_values($schedule);
        foreach ($schedule_rep as $key =>$value){
            $value['time']=strtotime($value['st_datetime'])-strtotime(explode(' ',$value['st_datetime'])[0]);
            $schedule_rep[$key]=$value;
        }
        $last = array_column($schedule_rep, 'time');
        array_multisort($last, SORT_ASC, $schedule_rep);
        $schedule=array_merge($schedule,$schedule_rep);
        $schedule=$this->getSchFilter($schedule);
        foreach ($schedule as $item){
            //対応アプリ
            if ($item['st_span'] && $item['ed_span']){
                $item['ed_datetime'] = $item['ed_span'].' '.date('H:i:s',strtotime($item['ed_datetime']));
            }
        }
        $range = $this->getNowDateRang($stDate, $edDate);
        $schedule = $this->getSchList($stDate, $edDate, $schedule, $range);

        return  $schedule ;
    }
    //#3031[スケジュール]「日」の項目の追加について
    private function AllDaySchPart($stDate,$edDate)
    {
        $schedule = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })
            ->whereHas('users', function ($q) {
                $q->where('enterprise_id', Auth::user()->enterprise_id);
                $q->whereNotNull('enterprise_id');
            })->with(['scheduleParticipants'])->with([
                'users' => function ($q) {
                    $q->withTrashed();
                    $q->select(['users.id', 'users.name']);
                }
            ])->with([
                'createBy' => function ($q) {
                    $q->select(['users.id', 'users.name']);
                }
            ])->with([
                'updateBy' => function ($q) {
                    $q->select(['users.id', 'users.name']);
                }
            ])->with('scheduleSubs')->where('repeat_kbn',0)->where('all_day',1)
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->get()->toArray();

        //繰り返す
        $schedule_rep = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->where('users.id',Auth::id());
                $q->withTrashed();
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')->where('repeat_kbn','>',0)->where('all_day',1)
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->get()->toArray();
        foreach ($schedule as $key =>$value){
            if(explode(' ',$value['ed_datetime'])[0]==explode(' ',$value['st_datetime'])[0]){
                $schedule_rep[]=$value;
                unset($schedule[$key]);
            }
        }
        $schedule=array_values($schedule);
        foreach ($schedule_rep as $key =>$value){
            $value['time']=strtotime($value['st_datetime'])-strtotime(explode(' ',$value['st_datetime'])[0]);
            $schedule_rep[$key]=$value;
        }
        $last = array_column($schedule_rep, 'time');
        array_multisort($last, SORT_ASC, $schedule_rep);
        $schedule=array_merge($schedule,$schedule_rep);
        $schedule=$this->getSchFilter($schedule);
        foreach ($schedule as $item){
            //対応アプリ
            if ($item['st_span'] && $item['ed_span']){
                $item['ed_datetime'] = $item['ed_span'].' '.date('H:i:s',strtotime($item['ed_datetime']));
            }
        }
        
        // 参加者の名前を追加します。
        if($schedule){
            foreach($schedule as $scheduleKey=>$scheduleTmp){
                if($scheduleTmp['schedule_participants']){
                    foreach($scheduleTmp['schedule_participants'] as $participantKey=>$participants){
                        $userNameTmp = DB::table('users')->where('id',$participants['user_id'])->value('name');
                        $schedule[$scheduleKey]['schedule_participants'][$participantKey]['name'] = $userNameTmp;
                    }
                }
            }
        }
        return  $schedule ;
    }
    //ユーザ情報の簡略化
    private function user_filter($users)
    {
        $users_r = array();
        for ($i = 0; $i < count($users); $i++) {
            $users_r[$i]['user_id'] = $users[$i]['id'];
            $users_r[$i]['name'] = $users[$i]['name'];
//            $users_r[$i]['enterprise_id'] = $users[$i]['enterprise_id'];
//            $users_r[$i]['file'] = $users[$i]['file'];
//            $users_r[$i]['first_name'] = $users[$i]['first_name'];
//            $users_r[$i]['last_name'] = $users[$i]['last_name'];
        }
        return $users_r;
    }

    private function isSpecial($schId, $stTime, $edTime)
    {
        //get last time string
        $stTimeStr = substr($stTime, 10, 6);
        $edTimeStr = substr($edTime, 10, 6);

        $stTimeArr = explode(':', $stTimeStr);
        $edTimeArr = explode(':', $edTimeStr);

        $stTimeHour = $stTimeArr[0];
        $stTimeMinute = $stTimeArr[1];
        $edTimeHour = $edTimeArr[0];
        $edTimeMinute = $edTimeArr[1];

        //check special schedule item
        if ($stTimeHour > $edTimeHour) {
            return $schId;
        }
        if ($stTimeHour == $edTimeHour) {
            if ($stTimeMinute > $edTimeMinute) {
                return $schId;
            }
        }
        return 0;
    }

    //スケジュール時間衝突検出
    private function in_span($datas, $st, $ed)
    {
        $st_ts = strtotime($st);
        $ed_ts = strtotime($ed);
        $arr = array();
        for ($i = 0; $i < count($datas); $i++) {
            $item_ts = strtotime($datas[$i]['s_date']);
            if ($item_ts <= $ed_ts && $item_ts >= $st_ts) {
                $arr[] = $datas[$i];
            }
        }
        return $arr;
    }

    //sub無しのスケジュール情報を充填する
    private function filter_space($sch_arr, $specialSchIdArr)
    {
        $arr = array();
        for ($i = 0; $i < count($sch_arr); $i++) {
            if (count($sch_arr[$i]['schedule_subs'])) {
//                if (in_array($sch_arr[$i]['sch_id'], $specialSchIdArr)) {
//                    $loopNum = count($sch_arr[$i]['schedule_subs']);
//                    for ($l = 0; $l < $loopNum; $l++) {
//                        $sch_arr[$i]['schedule_subs'][] = $this->makeNextDaySchedule($sch_arr[$i]['schedule_subs'][$l]);
//                    }
//                }
                //各イベントには、開始タイムスタンプ及び終了タイムスタンプ及び継続時間が追加される
                $sch_arr[$i] = $this->modify_time_span($sch_arr[$i], $specialSchIdArr);
                $arr[] = $sch_arr[$i];
            } else {
                //ここには2つのケース1、1日のスケジュール2、2日連続のスケジュールが含まれている
                if (substr($sch_arr[$i]['st_datetime'], 0, 10) === substr($sch_arr[$i]['ed_datetime'], 0, 10)) {
                    //単天事件-s
                    $single_day_st_ts = strtotime($sch_arr[$i]['st_datetime']);
                    $single_day_ed_ts = strtotime($sch_arr[$i]['ed_datetime']);
                    //10分未満のスケジュールを10分補足する
                    $single_day_ed_ts_tmp = $single_day_ed_ts;
                    if ($single_day_ed_ts - $single_day_st_ts < 1800) {
                        $single_day_ed_ts = $single_day_st_ts + 1800;
                    }
                    $sch_arr[$i]['schedule_subs'][] = array(
                        'id' => 0,
                        'relation_id' => $sch_arr[$i]['sch_id'],
                        'created_by' => $sch_arr[$i]['created_by'],
                        'updated_by' => $sch_arr[$i]['updated_by'],
                        'type' => $sch_arr[$i]['type'],
                        'repeat_kbn' => $sch_arr[$i]['repeat_kbn'],
                        'background_color' => $this->modify_color($sch_arr[$i]['type'], $sch_arr[$i]['repeat_kbn']),
                        's_date' => date('Y-m-d', $single_day_st_ts),
                        'cross_st' => date('Y-m-d H:i:s', $single_day_st_ts),
                        'st_ts_int' => $single_day_st_ts,
                        'cross_ed' => date('Y-m-d H:i:s', $single_day_ed_ts),
                        'ed_ts_int' => $single_day_ed_ts_tmp,
                        'cross_span' => $single_day_ed_ts - $single_day_st_ts,
                        'created_at' => $sch_arr[$i]['created_at'],
                        'updated_at' => $sch_arr[$i]['updated_at'],
                        'all_day' => $sch_arr[$i]['all_day']
                    );
                    $arr[] = $sch_arr[$i];
                } else {
                    //天をまたぐ事件
                    //初日を判定し、初日の時間を生成する
                    $date_first_day = $sch_arr[$i]['st_datetime'];
                    $date_first_day_ts = strtotime($date_first_day);
                    $date_first_day_0 = strtotime(substr($date_first_day, 0, 10));
                    $sch_arr[$i]['schedule_subs'][] = array(
                        'id' => 0,
                        'relation_id' => $sch_arr[$i]['sch_id'],
                        'created_by' => $sch_arr[$i]['created_by'],
                        'updated_by' => $sch_arr[$i]['updated_by'],
                        'type' => $sch_arr[$i]['type'],
                        'repeat_kbn' => $sch_arr[$i]['repeat_kbn'],
                        'background_color' => $this->modify_color($sch_arr[$i]['type'], $sch_arr[$i]['repeat_kbn']),
                        's_date' => date('Y-m-d', $date_first_day_0),
                        'cross_st' => $date_first_day,
                        'st_ts_int' => $date_first_day_ts,
                        'cross_ed' => date('Y-m-d H:i:s', ($date_first_day_0 + 3600 * 24 - 1)),
                        'ed_ts_int' => $date_first_day_0 + 3600 * 24 - 1,
                        'cross_span' => $date_first_day_0 + 3600 * 24 - 1 - $date_first_day_ts,
                        'created_at' => $sch_arr[$i]['created_at'],
                        'updated_at' => $sch_arr[$i]['updated_at'],
                        'all_day' => $sch_arr[$i]['all_day']
                    );
                    //最終日を判定し、最終日の時間を生成する
                    $date_last_day = $sch_arr[$i]['ed_datetime'];
                    $date_last_day_ts = strtotime($date_last_day);
                    $date_last_day_0 = strtotime(substr($date_last_day, 0, 10));
                    $date_last_day_remain = $date_last_day_ts - $date_last_day_0;
                    $last_item = array(
                        'id' => 0,
                        'relation_id' => $sch_arr[$i]['sch_id'],
                        'created_by' => $sch_arr[$i]['created_by'],
                        'updated_by' => $sch_arr[$i]['updated_by'],
                        'type' => $sch_arr[$i]['type'],
                        'repeat_kbn' => $sch_arr[$i]['repeat_kbn'],
                        'background_color' => $this->modify_color($sch_arr[$i]['type'], $sch_arr[$i]['repeat_kbn']),
                        's_date' => date('Y-m-d', $date_last_day_0),
                        'cross_st' => date('Y-m-d H:i:s', $date_last_day_0),
                        'st_ts_int' => $date_last_day_0,
                        'cross_ed' => $date_last_day,
                        'ed_ts_int' => strtotime($date_last_day),
                        'cross_span' => $date_last_day_remain,
                        'created_at' => $sch_arr[$i]['created_at'],
                        'updated_at' => $sch_arr[$i]['updated_at'],
                        'all_day' => $sch_arr[$i]['all_day']
                    );
                    //ループは天をまたぐイベント時間を生成する
                    $nums_day_loop = ($date_last_day_0 - $date_first_day_0) / 86400 - 1;
                    $next_day_0 = $date_first_day_0 + 3600 * 24;
                    for ($l = 0; $l < $nums_day_loop; $l++) {
                        $sch_arr[$i]['schedule_subs'][] = array(
                            'id' => 0,
                            'relation_id' => $sch_arr[$i]['sch_id'],
                            'created_by' => $sch_arr[$i]['created_by'],
                            'updated_by' => $sch_arr[$i]['updated_by'],
                            'type' => $sch_arr[$i]['type'],
                            'repeat_kbn' => $sch_arr[$i]['repeat_kbn'],
                            'background_color' => $this->modify_color($sch_arr[$i]['type'], $sch_arr[$i]['repeat_kbn']),
                            's_date' => date('Y-m-d', $next_day_0),
                            'cross_st' => date('Y-m-d H:i:s', $next_day_0),
                            'st_ts_int' => $next_day_0,
                            'cross_ed' => date('Y-m-d H:i:s', $next_day_0 + 3600 * 24 - 1),
                            'ed_ts_int' => $next_day_0 + 3600 * 24 - 1,
                            'cross_span' => $next_day_0 + 3600 * 24 - 1 - $next_day_0,
                            'created_at' => $sch_arr[$i]['created_at'],
                            'updated_at' => $sch_arr[$i]['updated_at'],
                            'all_day' => $sch_arr[$i]['all_day']
                        );
                        $next_day_0 += 3600 * 24;
                    }
                    //最終日を追加する
                    $sch_arr[$i]['schedule_subs'][] = $last_item;
                    $arr[] = $sch_arr[$i];
                }
            }
        }
        return $arr;
    }

    //make virtual schedule item
    private function makeNextDaySchedule($subItem)
    {
        $arr = array();
        $arr['id'] = 0;
        $arr['relation_id'] = $subItem['relation_id'];
        $nextDate = strtotime('+1 day', strtotime($subItem['s_date']));
        $arr['s_date'] = date('Y-m-d', $nextDate);
        $arr['created_at'] = $subItem['created_at'];
        $arr['updated_at'] = $subItem['updated_at'];
        return $arr;
    }

    //索引を作成する
    private function init_date_index($st)
    {
        $st_ts = strtotime($st);
        $arr = array();
        for ($i = 0; $i < 7; $i++) {
            $arr[$i] = date('Y-m-d', $st_ts);
            $st_ts += 3600 * 24;
        }
        return $arr;
    }

    //スケジュール情報を天ごとに取得する
    private function get_items_date($arr_datas, $date_str)
    {
        $arr = array();
        for ($i = 0; $i < count($arr_datas); $i++) {
            for ($l = 0; $l < count($arr_datas[$i]['schedule_subs']); $l++) {
                if ($arr_datas[$i]['schedule_subs'][$l]['s_date'] == $date_str) {
                    $arr_tmp = array();
                    $arr_tmp['id'] = $arr_datas[$i]['schedule_subs'][$l]['relation_id'];
                    $arr_tmp['subId'] = $arr_datas[$i]['schedule_subs'][$l]['id'];
                    $arr_tmp['relation_id'] = $arr_datas[$i]['schedule_subs'][$l]['relation_id'];
                    $arr_tmp['created_by'] = $arr_datas[$i]['schedule_subs'][$l]['created_by'];
                    $arr_tmp['updated_by'] = $arr_datas[$i]['schedule_subs'][$l]['updated_by'];
                    $arr_tmp['type'] = $arr_datas[$i]['schedule_subs'][$l]['type'];
                    $arr_tmp['repeat_kbn'] = $arr_datas[$i]['schedule_subs'][$l]['repeat_kbn'];
                    $arr_tmp['background_color'] = $this->modify_color($arr_tmp['type'],
                        $arr_datas[$i]['schedule_subs'][$l]['repeat_kbn']);
                    $arr_tmp['cross_st'] = $arr_datas[$i]['schedule_subs'][$l]['cross_st'];
                    $arr_tmp['st_ts_int'] = $arr_datas[$i]['schedule_subs'][$l]['st_ts_int'];
                    $arr_tmp['stDate'] = date('Y/m/d', $arr_datas[$i]['schedule_subs'][$l]['st_ts_int']);
                    $arr_tmp['startTime'] = date('H:i', $arr_datas[$i]['schedule_subs'][$l]['st_ts_int']);
                    $arr_tmp['cross_ed'] = $arr_datas[$i]['schedule_subs'][$l]['cross_ed'];
                    $arr_tmp['ed_ts_int'] = $arr_datas[$i]['schedule_subs'][$l]['ed_ts_int'];
                    $arr_tmp['edDate'] = date('Y/m/d', $arr_datas[$i]['schedule_subs'][$l]['ed_ts_int']);
                    $arr_tmp['finishTime'] = date('H:i', $arr_datas[$i]['schedule_subs'][$l]['ed_ts_int']);
                    $arr_tmp['cross_span'] = $arr_datas[$i]['schedule_subs'][$l]['cross_span'];
                    if ($arr_tmp['cross_span'] >= 1800) {
                        $arr_tmp['span_rate'] = number_format($arr_datas[$i]['schedule_subs'][$l]['cross_span'] / 86400,
                                4) * 100;
                    } else {
                        $arr_tmp['span_rate'] = number_format(1800 / 86400, 4) * 100;
                    }
                    $arr_tmp['s_date'] = $arr_datas[$i]['schedule_subs'][$l]['s_date'];
                    $arr_tmp['st_datetime'] = $arr_datas[$i]['st_datetime'];
                    $arr_tmp['st_ts'] = strtotime($arr_datas[$i]['st_datetime']);
                    $arr_tmp['escape_rate'] = number_format(($arr_tmp['st_ts_int'] - strtotime($arr_tmp['s_date'])) / 86400,
                            4) * 100;
                    if ($arr_tmp['escape_rate'] >97.9){
                        $arr_tmp['escape_rate'] = 97.9;
                    }
                    if ($arr_tmp['escape_rate'] == 0) {
                        $arr_tmp['escape_rate'] = 0.1;
                    }
                    $day_st_str = substr_replace($arr_datas[$i]['st_datetime'], $date_str . ' ', 0, 11);
                    $arr_tmp['day_start'] = $day_st_str;
                    $arr_tmp['day_start_ts'] = strtotime($day_st_str);
                    if ($arr_datas[$i]['ed_datetime'] === $arr_datas[$i]['st_datetime']) {
                        $arr_tmp['ed_ts'] = strtotime($arr_datas[$i]['st_datetime']) + 1800;
                        $arr_tmp['ed_datetime'] = date('Y-m-d H:i:s', $arr_tmp['ed_ts']);
                    } else {
                        $arr_tmp['ed_datetime'] = $arr_datas[$i]['ed_datetime'];
                        $arr_tmp['ed_ts'] = strtotime($arr_datas[$i]['ed_datetime']);
                    }
                    $arr_tmp['subject'] = $arr_datas[$i]['subject'];
                    $arr_tmp['content'] = $arr_datas[$i]['comment'];
                    $arr_tmp['st_span'] = $arr_datas[$i]['st_span'];
                    $arr_tmp['ed_span'] = $arr_datas[$i]['ed_span'];
                    $arr_tmp['address'] = $arr_datas[$i]['location'];
                    $arr_tmp['all_day'] = $arr_datas[$i]['all_day'];
                    $arr_tmp['participantUsers'] = $arr_datas[$i]['users'];
                    $uc = $this->get_user($arr_tmp['created_by']);
                    $arr_tmp['createUser'] = $uc;
                    $arr_tmp['createDate'] = $arr_datas[$i]['created_at'];

                    if ($arr_datas[$i]['updated_at']) {
                        $uu = $this->get_user($arr_tmp['updated_by']);
                        $arr_tmp['updateUser'] = $uu;
                        $arr_tmp['updateDate'] = $arr_datas[$i]['updated_at'];
                    } else {
                        $arr_tmp['updateUser'] = '';
                        $arr_tmp['updateDate'] = '';
                    }
                    $arr[] = $arr_tmp;
                }
            }
        }
        $start_time_ts = array_column($arr, 'st_ts_int');//索引付け
        array_multisort($start_time_ts, SORT_ASC, $arr);//結果セットのランキング
        return $arr;
    }

    //スケジュール情報処理
    private function modify_time_span($sch, $specialItemArr = array())
    {
        $time_st = substr($sch['st_datetime'], 11, 8);
        $time_ed = substr($sch['ed_datetime'], 11, 8);

        $firstDay = date('Y-m-d', strtotime($sch['st_datetime']));
        $lastDay = date('Y-m-d', strtotime('+1 day', strtotime($sch['ed_datetime'])));

        $stHourMinute = substr($sch['st_datetime'], 11, 5);
        $edHourMinute = substr($sch['ed_datetime'], 11, 5);

        if (in_array($sch['sch_id'], $specialItemArr)) {
            //need to expend schedule items
            $createdByTmp = $sch['created_by'];
            $updatedByTmp = $sch['updated_by'];
            $createdAtTmp = $sch['created_at'];
            $updatedAtTmp = $sch['updated_at'];
            $typeTmp = $sch['type'];
            $repeatKbnTmp = $sch['repeat_kbn'];

            $subExpendArr = array();

            for ($i = 0; $i < count($sch['schedule_subs']); $i++) {
                $schTmpItem = array();

                //day only has bottom span
                $time_ed_first = '23:59:59';
                $schTmpItem['id'] = $sch['schedule_subs'][$i]['id'];
                $schTmpItem['s_date'] = $sch['schedule_subs'][$i]['s_date'];
                $schTmpItem['relation_id'] = $sch['sch_id'];
                $schTmpItem['created_by'] = $createdByTmp;
                $schTmpItem['updated_by'] = $updatedByTmp;
                $schTmpItem['created_at'] = $createdAtTmp;
                $schTmpItem['updated_at'] = $updatedAtTmp;
                $schTmpItem['type'] = $typeTmp;
                $schTmpItem['repeat_kbn'] = $repeatKbnTmp;
                $schTmpItem['cross_st'] = $sch['schedule_subs'][$i]['s_date'] . ' ' . $time_st;
                $schTmpItem['st_ts_int'] = strtotime($schTmpItem['cross_st']);
                $schTmpItem['cross_ed'] = $sch['schedule_subs'][$i]['s_date'] . ' ' . $time_ed_first;
                $schTmpItem['ed_ts_int'] = strtotime($schTmpItem['cross_ed']);
                if ($schTmpItem['ed_ts_int'] - $schTmpItem['st_ts_int'] < 1800) {
                    $tmp = $schTmpItem['ed_ts_int'];
                    $schTmpItem['ed_ts_int'] = $schTmpItem['st_ts_int'] + 1800;
                    $schTmpItem['cross_ed'] = date('Y-m-d H:i:s', $schTmpItem['ed_ts_int']);
                    $schTmpItem['ed_ts_int'] = $tmp;
                }
                $schTmpItem['cross_span'] = $schTmpItem['ed_ts_int'] - $schTmpItem['st_ts_int'];
                $subExpendArr[] = $schTmpItem;


                //+1day only has top span
                $time_st_last = '00:00:00';
                $dayPlus = date('Y-m-d',
                    strtotime('+1 day', strtotime($sch['schedule_subs'][$i]['s_date'])));
                $schTmpItem['id'] = $sch['schedule_subs'][$i]['id'];
                $schTmpItem['s_date'] = $dayPlus;
                $schTmpItem['relation_id'] = $sch['sch_id'];
                $schTmpItem['created_by'] = $createdByTmp;
                $schTmpItem['updated_by'] = $updatedByTmp;
                $schTmpItem['created_at'] = $createdAtTmp;
                $schTmpItem['updated_at'] = $updatedAtTmp;
                $schTmpItem['type'] = $typeTmp;
                $schTmpItem['repeat_kbn'] = $repeatKbnTmp;
                $schTmpItem['cross_st'] = $dayPlus . ' ' . $time_st_last;
                $schTmpItem['st_ts_int'] = strtotime($schTmpItem['cross_st']);
                $schTmpItem['cross_ed'] = $dayPlus . ' ' . $time_ed;
                $schTmpItem['ed_ts_int'] = strtotime($schTmpItem['cross_ed']);
                if ($schTmpItem['ed_ts_int'] - $schTmpItem['st_ts_int'] < 1800) {
                    $tmp = $schTmpItem['ed_ts_int'];
                    $schTmpItem['ed_ts_int'] = $schTmpItem['st_ts_int'] + 1800;
                    $schTmpItem['cross_ed'] = date('Y-m-d H:i:s', $schTmpItem['ed_ts_int']);
                    $schTmpItem['ed_ts_int'] = $tmp;
                }
                $schTmpItem['cross_span'] = $schTmpItem['ed_ts_int'] - $schTmpItem['st_ts_int'];
                $subExpendArr[] = $schTmpItem;
            }

            $sch['schedule_subs'] = $subExpendArr;
            $sch['background_color'] = $this->modify_color($typeTmp, $repeatKbnTmp);
            return $sch;
        } else {
            for ($i = 0; $i < count($sch['schedule_subs']); $i++) {
                $sch['schedule_subs'][$i]['created_by'] = $sch['created_by'];
                $sch['schedule_subs'][$i]['updated_by'] = $sch['updated_by'];
                $sch['schedule_subs'][$i]['created_at'] = $sch['created_at'];
                $sch['schedule_subs'][$i]['updated_at'] = $sch['updated_at'];
                $sch['schedule_subs'][$i]['type'] = $sch['type'];
                $sch['schedule_subs'][$i]['repeat_kbn'] = $sch['repeat_kbn'];
                $sch['background_color'] = $this->modify_color($sch['type'], $sch['repeat_kbn']);
                $sch['schedule_subs'][$i]['cross_st'] = $sch['schedule_subs'][$i]['s_date'] . ' ' . $time_st;
                $sch['schedule_subs'][$i]['st_ts_int'] = strtotime($sch['schedule_subs'][$i]['cross_st']);
                $sch['schedule_subs'][$i]['cross_ed'] = $sch['schedule_subs'][$i]['s_date'] . ' ' . $time_ed;
                $sch['schedule_subs'][$i]['ed_ts_int'] = strtotime($sch['schedule_subs'][$i]['cross_ed']);
                if ($sch['schedule_subs'][$i]['ed_ts_int'] - $sch['schedule_subs'][$i]['st_ts_int'] < 1800) {
                    $tmp = $sch['schedule_subs'][$i]['ed_ts_int'];
                    $sch['schedule_subs'][$i]['ed_ts_int'] = $sch['schedule_subs'][$i]['st_ts_int'] + 1800;
                    $sch['schedule_subs'][$i]['cross_ed'] = date('Y-m-d H:i:s', $sch['schedule_subs'][$i]['ed_ts_int']);
                    $sch['schedule_subs'][$i]['ed_ts_int'] = $tmp;
                }
                $sch['schedule_subs'][$i]['cross_span'] = $sch['schedule_subs'][$i]['ed_ts_int'] - $sch['schedule_subs'][$i]['st_ts_int'];
            }
        }

        return $sch;
    }

    //衝突仕様を定義し、衝突仕様に従った順序付けを行う
    private function cal_table($arr)
    {
        $cal_r = array();
        $num_need_sort = count($arr);
        if ($num_need_sort) {
            for ($i = 0; $i < $num_need_sort; $i++) {
                $cal_r = $this->set_order($cal_r, $arr[$i]);
            }
        }
        return $cal_r;
    }

    //2つのイベントのスケジュールに衝突がないか判断する
    //冲突返回true
    //衝突せずfalseに戻る
    private function check_conflict($sch_a, $sch_b)
    {
        if ($sch_b['st_ts_int'] < $sch_a['ed_ts_int'] && $sch_b['st_ts_int'] >= $sch_a['st_ts_int']) {
            return true;
        } else {
            return false;
        }
    }

    //衝突仕様を使用して値を挿入する
    private function set_order($arr, $data)
    {
        $num = count($arr);
        if ($num) {
            for ($i = 0; $i < $num; $i++) {
                $num_row = count($arr[$i]);

                //The number of data bars in the current column that have no conflicts
                $noConflictTagNum = 0;
                for ($l = 0; $l < $num_row; $l++) {
                    if (!$this->check_conflict($arr[$i][$l], $data)) {
                        $noConflictTagNum = $noConflictTagNum + 1;
                    }else{
                        break;
                    }
                }
                //All data for the current column is free of conflicts
                if ($noConflictTagNum == $num_row){
                    $arr[$i][] = $data;//次のマスに自働充填する
                    return $arr;
                }
            }
            //適当な位置を見つけることができないまま、索引を新たに作って1位に置く
            $arr[$num][0] = $data;
            return $arr;
        } else {
            $arr[0][0] = $data;
            return $arr;
        }

    }

    //色を定義する
    private function modify_color($type, $repeat_kbn)
    {
        $color = '#48C279';
        if ($repeat_kbn !== '0') {
            $color = '#30A5E9';
        } else {
            if ($type === 7) {
                $color = '#E546BB';
            }
        }
        return $color;
    }

    //ユーザー情報を取得する
    private function get_user($uid)
    {
        $user = User::withTrashed()->where('id', $uid)->first();
        return $user;
    }
    public function VerifyParticipant(){
        $participantUsers=request('participantUsers');
        $users=[];
        foreach ($participantUsers as $k => $v){
            $users[]=$v['id'];
        }
        if(in_array(Auth::id(),$users)){
            $res=1;
        }else{
            $val=EnterpriseParticipant::where('user_id',Auth::id())->pluck('enterprise_id')->toArray();
            $val=array_filter($val);
            $enterpriseUsers=[];
            if(count($val)>0){
                $enterpriseUsers=User::whereIn('enterprise_id',$val)->pluck('id')->toArray();
            }
            $value=array_intersect($users,$enterpriseUsers);
            if(count($value)>0){
                $res=0;
            }else{
                $res=1;
            }
        }
        return $this->json('',$res);
    }
    public function getJapaneseDate(Request $request){
        $stDate = $request->get('stDate');
        // 六曜、祝日取得
        $japaneseDate = Common::getJapaneseThreeMonthWithOthers($stDate);
        return $this->json('',['japaneseDate'=>$japaneseDate]);
    }

    public function getCaseList(Request $request)
    {
        $proModel = Project::where(function ($q) {
            $project_ids = ProjectParticipant::where('user_id', Auth::id())->get()->toArray();
            $res = [];
            foreach ($project_ids as $item) {
                $res[] = $item['project_id'];
            }
            $q->whereIn('id', $res)->pluck('id')->toArray();
        });
        $proArr = $proModel->orderBy('id')->get(['id', 'place_name']);
        return $this->json('', $proArr);
    }


    public function getScheduleListSelect(Request $request)
    {
        $stDate = $request->post('stDate');
        $edDate = $request->post('edDate');
        $project_id = (int)$request->post('progress_status');
        $edDate = date('Y/m/d H:i', strtotime("$edDate -1 Minute"));
        $scheduleIdArr = ScheduleParticipant::where('user_id', Auth::id())->pluck('schedule_id');
        //事件の参加者
        $ProjectUsersArray = ProjectParticipant::where('project_id', $project_id)->get('user_id')->toArray();
        $ProjectUsers = [];
        foreach ($ProjectUsersArray as $item) {
            $ProjectUsers[] = $item['user_id'];
        }
        //メンバー
        $ownEnterpriseArr = User::where('enterprise_id', Auth::user()->enterprise_id)->pluck('id');
        //協同組合クラブのすべての企業として自分を楽しませる
        $enterpriseParticipantArray = EnterpriseParticipant::where('user_id', Auth::id())->get('enterprise_id')->toArray();
        $enterpriseParticipant = [];
        foreach ($enterpriseParticipantArray as $item) {
            $enterpriseParticipant[] = $item['enterprise_id'];
        }
        $schedule = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })->where(function ($q1) use ($scheduleIdArr, $ownEnterpriseArr) {
            $q1->whereIn('id', $scheduleIdArr);
            $q1->orWhereIn('created_user_id', $ownEnterpriseArr);
            $q1->orWhereHas('users', function ($q) {
                $q->where('enterprise_id', Auth::user()->enterprise_id);
                $q->whereNotNull('enterprise_id');
            });
        })->with(['scheduleParticipants'])->with([
            'users' => function ($q) {
                $q->withTrashed();
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'createBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with([
            'updateBy' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }
        ])->with('scheduleSubs')
            ->orderBy('repeat_kbn')
            ->orderBy('st_datetime')
            ->orderByRaw('ed_datetime - st_datetime', 'desc')
            ->orderBy('st_datetime')->get()->toArray();

        //#5280,Forget to revise here while fix the bug #2298(スケジュール参加者の論理的削除)
        $schedule=$this->getSchFilter($schedule);

        $userRule = User::where(function ($q) use ($enterpriseParticipant, $project_id) {
            if ($project_id > 0) {
                //協同組合クラブのメンバーとして自分を楽しませる
                $q->whereIn('enterprise_id', $enterpriseParticipant);
            }
        });
        if (Auth::user()->enterprise_id && $project_id > 0) {
            //社内会員および協同組合クラブ
            $userRule->orWhere('enterprise_id', Auth::user()->enterprise_id)
                ->orWhereHas('enterpriseParticipants', function ($q) {
                    $q->where('enterprise_id', Auth::user()->enterprise_id)->where('agree', '=', '1');
                });
        }
        if (Auth::user()->enterprise_id !== null && $project_id == -1) {
            //メンバー
            $userRule->Where('enterprise_id', Auth::user()->enterprise_id);
        }
        if (Auth::user()->enterprise_id === null && $project_id == -1) {
            //自分
            $userRule->Where('id', Auth::user()->id);
        }
        if (Auth::user()->enterprise_id && $project_id == -2) {
            //協同組合クラブのメンバー
            $userRule->orWhereHas('enterpriseParticipants', function ($q) {
                $q->where('enterprise_id', Auth::user()->enterprise_id)->where('agree', '=', '1');
            });
        }
        if (Auth::user()->enterprise_id === null && $project_id == -2) {
            //協同組合クラブのメンバー
            $userRule->whereIn('enterprise_id', $enterpriseParticipant);

        }
        $userArr = $userRule->orderBy('id')->get(['id', 'name'])->toArray();
        foreach ($schedule as $item){
            if ($item['st_span'] && $item['ed_span']){
                $item['ed_datetime'] = $item['ed_span'].' '.date('H:i:s',strtotime($item['ed_datetime']));
            }
        }
        if (count($userArr) > 0 && $project_id > 0) {
            $newUserArr=array();
            foreach ($userArr as $k => $v) {
                if (!in_array($v['id'], $ProjectUsers)) {
                    unset($userArr[$k]);
                }
            }
            $userArr=array_values($userArr);
        }
        $schItem = $this->getSchList($stDate, $edDate, $schedule, 7);
        $id = Auth::id();
        $name = Auth::user()->name;
        $proList = $this->addProjectSchedules($stDate, $edDate, $userArr);
        // 六曜、祝日取得
        $japaneseWeek = Common::getJapaneseMonthWithOthers($stDate);
        return [
            "schedule" => $schItem,
            "user" => $userArr,
            "userId" => $id,
            "userName" => $name,
            "japaneseWeek" => $japaneseWeek,
            'proSch' => $proList,
            'ProjectUsers' => $ProjectUsers,
            'enterpriseParticipant' => $enterpriseParticipant
        ];
    }

    public function dashboardScheduleCheck(Request $request){
        $scheduleId=$request->input('id');

        //スケジュール
        $schCount = Schedule::where('id',$scheduleId)->count();
        //参加者
        $schParticipantCount = ScheduleParticipant::where('schedule_id',$scheduleId)->where('user_id',Auth::id())->count();

        $res = 0;
        if ($schCount&&$schParticipantCount) {
            $res = 1;
        } else {
            $res = 0;
        }

        return $res;
    }

    /**
     * #4873「組織」で見ると表示されていない予定が、「個人」の週に表示されています。
     * @param $scheduleResult
     * @param $st_span
     * @param $ed_span
     * @return array
     */
    public function dealWeekIsDate($scheduleResult, $st_span, $ed_span)
    {

        $weekArray = array();
        for ($i = 1; $i <= 7; $i++) {
            $weekArray[$i] = ($scheduleResult['checkWeekArr'][$i - 1] == true) ? 1 : null;
        }

        $min = strtotime($st_span);
        $max = strtotime($ed_span);
        $days = ($max - $min) / 86400;
        //No need to judge if there are more than seven days in the time period
        if ($days < 7) {
            $subNum = 0;
            for ($i = $min; $i <= $max; $i = strtotime('1 day', $i)) {
                $week = date('w', $i) + 1;
                if ($weekArray[$week] == 1) {
                    $subNum++;
                }
            }
        } else {
            $subNum = 'week';
        }
        return [
            'weekArray' => $weekArray,
            'subNum' => $subNum
        ];

    }

    //cross-day check
    public function checkSchedule(Request $request){
        $scheduleId = $request->post('id');
        $subId = $request->post('subId');
        $updateDate = $request->post('updateDate');
        $schedule = Schedule::where('id',$scheduleId)->first();
        if ($schedule){
            $updateDate = $this->checkCrossDay($scheduleId,$schedule['st_datetime'],$schedule['ed_datetime'],$schedule['all_day'],$schedule['repeat_kbn'],$subId,$updateDate);
        }
        return $updateDate;
    }


    private function checkCrossDay($scheduleId,$startTime,$endTime,$allDay,$repeat,$subId,$updateDate){
        $isSpecial = $this->isSpecial($scheduleId,$startTime,$endTime);
        if ($isSpecial != 0 && $allDay != 1 && $repeat != 0){
            //cross-day
            $subDate = ScheduleSub::where('id',$subId)->value('s_date');
            if ($updateDate != $subDate){
                //is the second half of the cross day schedule
                $updateDate = date('Y-m-d', strtotime("$updateDate -1days"));
            }
        }
        return $updateDate;
    }
}
