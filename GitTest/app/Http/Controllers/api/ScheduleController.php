<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Schedule;
use App\Schedulesub;
use App\Scheduleparticipant;
use App\Chatcontact;
use App\Mail\ContactRequestSend;
use Response;
use DB;
use \Datetime;

use Illuminate\Support\Facades\Mail;

class ScheduleController extends Controller {

    public function __construct() {
        $this->content = array();
    }

    /**
     * ログイン済対象ユーザーのスケジュールを日付指定で取得し、返す
     */
    public function getDateSchedules(){
        $start = request('start');
        $end = request('end');
        $result = Schedule::getSchedulesTerm(Auth::id(), $start, $end);
        if (empty($result)){
            $res = array();
            $res = [
                'status'        => '0001',
                'message'       => 'NO DATE.'
            ];
            $json = json_encode($res, JSON_UNESCAPED_UNICODE);
        } else {
            $json = json_encode(["results" => $result], JSON_UNESCAPED_UNICODE);
        }
        return response($json)
                ->header('Content-Length', strlen($json))
                ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * 入力されたスケジュールをデータベースに登録する
     */
    public function regist() {

        $st_span = null;
        $ed_span = null;
        $monthdiff = null;
        $daydiff = null;
        if (request('stSpan') && request('edSpan')) {
            $st_span = request('stSpan');
            $ed_span = request('edSpan');
            $datetime1 = date_create($st_span);
            $datetime2 = date_create($ed_span);
            $interval = date_diff($datetime1, $datetime2);
            $monthdiff = $interval->format('%m');
            $daydiff = $interval->format('%a');
        }

        $st_datetime = request('mStartAt');
        $ed_datetime = request('mEndAt');
        $type_id = request('type');
        $subject = request('subject');
        $comment = request('comment');
        $open_range = request('open_range');

        $status = 200;
        $res;
        // 自分も参加者として追加
        $user_str = request('participants');
        if(!empty($user_str)) {
            $participants = explode(',', $user_str);
        }
        $participants[] = Auth::id();
        // 他の人も設定された場合、配列に追加
        DB::beginTransaction();
        try {
            // 新規登録
            switch(request('repeatKbn')) {
                // 1日だけ
                case '0':
                    Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $open_range, $participants);
                    break;

                // 毎日
                case '1':
                    Schedule::setEveryDay($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $daydiff, $open_range, $participants);
                    break;

                // 毎月
                case '3':
                    Schedule::setEveryMonth($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $monthdiff, $open_range, $participants);
                    break;

                // 曜日指定
                case '4':
                    $weekArray = array();
                    for ($i = 1; $i <= 7; $i++) {
                        $weekArray[$i] = (request('week'. $i) == 1) ? 1 : null;
                    }
                    Schedule::setWeek($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $daydiff, $weekArray, $open_range, $participants);
            }

            DB::commit();
            $res = [
                'status'        => '0000',
                'message'       => ''
            ];

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * スケジュール照会
     */
    public function retrieve() {
        $id = request('id');
        $sub_id = request("sub_id");
        $res;
        $schedule = Schedule::getOneDayScheduleDetail(Auth::id(), $id, $sub_id);
        if(empty($schedule)) {
            $res = [
                'status'        => '0101',
                'message'       => 'データがありません。'
            ];
        } else {
            $res = ['event' => $schedule, 'status' => '0000'];
        }
        $status = 200;
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * スケジュール削除
     */
    public function deleteAll() {
        $id = request('id');
        $status = 200;
        $res;
        DB::beginTransaction();
        try {
            Schedule::deleteAll($id);

            DB::commit();
            $res = [
                'status'        => '0000',
                'message'       => ''
            ];

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * サブスケジュール削除
     */
    public function deleteSub() {
        $id = request('id');
        $subId = request('sub_id');

        // サブが一件のみの場合、全体削除
        $subCount = Schedulesub::getCount($id);
        if($subCount <= 1) {
            return $this->deleteAll();
        }

        $status = 200;
        $res;
        DB::beginTransaction();
        try {
            Schedulesub::deleteSub($id, $subId);

            DB::commit();
            $res = [
                'status'        => '0000',
                'message'       => ''
            ];

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * サブスケジュール以降削除　スケジュールの日付は該当日付前日を終了日時に設定
     */
    public function deleteAfter() {
        $id = request('id');
        $subId = request('sub_id');

        // サブが一件のみの場合、全体削除
        $subCount = Schedulesub::getCount($id);
        if($subCount <= 1) {
            return $this->deleteAll();
        }

        $target_date = request('target_date');
        $date = new DateTime($target_date);
        $pre_date = $date->modify('-1 days')->format('Y-m-d');

        $status = 200;
        $res;
        DB::beginTransaction();
        try {
            Schedulesub::deleteAfter($id, $subId);

            // 前日日付までにSchedule繰り返し終了日時を更新
            Schedule::updateEdSpan($id, $pre_date);

            DB::commit();
            $res = [
                'status'        => '0000',
                'message'       => ''
            ];

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 自分のスケジュール参加者のみ削除
     */
    public function deleteMine() {
        $id = request('id');
        $userId = Auth::id();
        $status = 200;
        $res;
        DB::beginTransaction();
        try {
            Scheduleparticipant::deleteFromId($id, $userId);

            DB::commit();
            $res = [
                'status'        => '0000',
                'message'       => ''
            ];

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * スケジュール更新
     * スケジュール情報を更新、繰り返し日、参加者をdelete&insert
     */
    public function updateAll() {
        $id = request('id');

        $st_span = null;
        $ed_span = null;
        $monthdiff = null;
        $daydiff = null;
        if (request('stSpan') && request('edSpan')) {
            $st_span = request('stSpan');
            $ed_span = request('edSpan');
            $datetime1 = date_create($st_span);
            $datetime2 = date_create($ed_span);
            $interval = date_diff($datetime1, $datetime2);
            $monthdiff = $interval->format('%m');
            $daydiff = $interval->format('%a');
        }

        $st_datetime = request('mStartAt');
        $ed_datetime = request('mEndAt');
        $type_id = request('type');
        $subject = request('subject');
        $comment = request('comment');
        $open_range = request('open_range');
        $status = 200;
        $res;

        DB::beginTransaction();
        try {

            Schedulesub::deleteByScheduleId($id);
            Scheduleparticipant::deleteByScheduleId($id);

            // 自分も参加者として追加
            $user_str = request('participants');
            if(!empty($user_str)) {
                $participants = explode(',', $user_str);
            }
            $participants[] = Auth::id();
            // 他の人も設定された場合、配列に追加
                // 新規登録
                switch(request('repeatKbn')) {
                    // 1日だけ
                    case '0':
                        Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                            , $subject ,$comment, $open_range
                            , $participants, $id);
                        break;

                    // 毎日
                    case '1':
                        Schedule::setEveryDay($st_datetime, $ed_datetime, $type_id
                            , $subject ,$comment, $st_span
                            , $ed_span, $daydiff, $open_range, $participants, $id);
                        break;

                    // 毎月
                    case '3':
                        Schedule::setEveryMonth($st_datetime, $ed_datetime, $type_id
                            , $subject ,$comment, $st_span
                            , $ed_span, $monthdiff, $open_range, $participants, $id);
                        break;

                    // 曜日指定
                    case '4':
                        $weekArray = array();
                        for ($i = 1; $i <= 7; $i++) {
                            $weekArray[$i] = (request('week'. $i) == 1) ? 1 : null;
                        }
                        Schedule::setWeek($st_datetime, $ed_datetime, $type_id
                            , $subject ,$comment, $st_span
                            , $ed_span, $daydiff, $weekArray, $open_range, $participants, $id);
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

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * スケジュール対象日のみ更新
     * 元のスケジュールの対象繰り返し日を削除、スケジュール、参加者をinsert
     * その日のみ更新なので繰り返しなし
     */
    public function updateOne() {
        $id = request('id');
        $subId = request('sub_id');

        // サブが一件のみの場合、すべて更新
        $subCount = Schedulesub::getCount($id);
        if($subCount <= 1) {
            return $this->updateAll();
        }

        $st_span = null;
        $ed_span = null;
        $monthdiff = null;
        $daydiff = null;
        if (request('stSpan') && request('edSpan')) {
            $st_span = request('stSpan');
            $ed_span = request('edSpan');
            $datetime1 = date_create($st_span);
            $datetime2 = date_create($ed_span);
            $interval = date_diff($datetime1, $datetime2);
            $monthdiff = $interval->format('%m');
            $daydiff = $interval->format('%a');
        }

        $st_datetime = request('mStartAt');
        $ed_datetime = request('mEndAt');
        $type_id = request('type');
        $subject = request('subject');
        $comment = request('comment');
        $open_range = request('open_range');

        $status = 200;
        $res;
        // 自分も参加者として追加
        $user_str = request('participants');
        if(!empty($user_str)) {
            $participants = explode(',', $user_str);
        }
        $participants[] = Auth::id();
        // 他の人も設定された場合、配列に追加
        DB::beginTransaction();
        try {

            Schedulesub::deleteSub($id, $subId);

            $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                , $subject ,$comment, $open_range
                , $participants);

            DB::commit();
            $res = [
                'id'            => $newId,
                'subId'        => '0',
                'status'        => '0000',
                'message'       => ''
            ];

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * スケジュールの対象日付以降を更新
     * 元のスケジュールの繰り返し終了日を前日に変更、以降の繰り返しを削除
     * スケジュール、参加者繰り返しをinsert
     */
    public function updateAfter() {
        $id = request('id');
        $subId = request('sub_id');

        // サブが一件のみの場合、すべて更新
        $subCount = Schedulesub::getCount($id);
        if($subCount <= 1) {
            return $this->updateAll();
        }

        $target_date = request('target_date');
        $date = new DateTime($target_date);
        $pre_date = $date->modify('-1 days')->format('Y-m-d');

        $st_span = null;
        $ed_span = null;
        $monthdiff = null;
        $daydiff = null;
        if (request('stSpan') && request('edSpan')) {
            $st_span = request('stSpan');
            $ed_span = request('edSpan');
            $datetime1 = date_create($st_span);
            $datetime2 = date_create($ed_span);
            $interval = date_diff($datetime1, $datetime2);
            $monthdiff = $interval->format('%m');
            $daydiff = $interval->format('%a');
        }

        $st_datetime = request('mStartAt');
        $ed_datetime = request('mEndAt');
        $type_id = request('type');
        $subject = request('subject');
        $comment = request('comment');
        $open_range = request('open_range');

        $status = 200;
        $res;
        $newId;
        $newSubId;
        // 自分も参加者として追加
        $user_str = request('participants');
        if(!empty($user_str)) {
            $participants = explode(',', $user_str);
        }
        $participants[] = Auth::id();
        // 他の人も設定された場合、配列に追加
        DB::beginTransaction();
        try {

            Schedulesub::deleteAfter($id, $subId);

            // 前日日付までにSchedule繰り返し終了日時を更新
            Schedule::updateEdSpan($id, $pre_date);

            // 新規登録
            switch(request('repeatKbn')) {
                // 1日だけ
                case '0':
                    $newId = Schedule::setOneDay($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $open_range, $participants);
                    break;

                // 毎日
                case '1':
                    $newId = Schedule::setEveryDay($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $daydiff, $open_range, $participants);
                    break;

                // 毎月
                case '3':
                    $newId = Schedule::setEveryMonth($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $monthdiff, $open_range, $participants);
                    break;

                // 曜日指定（毎週）
                case '4':
                    $weekArray = array();
                    for ($i = 1; $i <= 7; $i++) {
                        $weekArray[$i] = (request('week'. $i) == 1) ? 1 : null;
                    }
                    $newId = Schedule::setWeek($st_datetime, $ed_datetime, $type_id
                        , $subject ,$comment, $st_span
                        , $ed_span, $daydiff, $weekArray, $open_range, $participants);
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

        } catch (\PDOException $e){
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }
        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * コンタクト承認依頼の登録とメール送信
     *
     */
    public function sendContact(Request $request) {

        $email      = $request->input('email');
        $message    = $request->input('message');
        $status = 200;
        $res;

        try {
            DB::beginTransaction();
            $fromUser = DB::table('users')->where('id', Auth::id())->first();
            Chatcontact::set(Auth::id(), $to_user_id=0, $message, $email);

            $url = url('/', null, true).'/invitation/'.$fromUser->id.'/schedule';
            $to = $email;
            Mail::to($to)->send(new ContactRequestSend($fromUser->name, $message, $url));

            if(count(Mail::failures()) > 0){
                DB::rollBack();
                $res = [
                    'status'        => '0101',
                    'message'       => 'メール送信に失敗しました。'
                ];
            } else {
                DB::commit();
                $res = [
                    'status'        => '0000',
                    'message'       => ''
                ];
            }
        } catch (Exception $e) {
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }

        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 既存会員へのコンタクト承認依頼の登録とメール送信
     *
     */
    public function sendContactRequest(Request $request) {

        $user_id = $request->input('user_id');
        $message = $request->input('message');
        $status = 200;
        $res;

        try {
            DB::beginTransaction();
            Chatcontact::set(Auth::id(), $user_id, $message);

            $toUser   = DB::table('users')->where('id', $user_id)->first();
            $fromUser = DB::table('users')->where('id', Auth::id())->first();

            $url = url('/', null, true).'/invitation/'.$fromUser->id.'/schedule';
            $to = $toUser->email;
            Mail::to($to)->send(new ContactRequestSend($fromUser->name, $message, $url));

            if(count(Mail::failures()) > 0){
                DB::rollBack();
                $res = [
                    'status'        => '0101',
                    'message'       => 'メール送信に失敗しました。'
                ];
            } else {
                DB::commit();
                $res = [
                    'status'        => '0000',
                    'message'       => ''
                ];
            }
        } catch (Exception $e) {
            DB::rollBack();
            $res = [
                'status'        => '0101',
                'message'       => $e->getMessage()
            ];
        }

        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }
}
