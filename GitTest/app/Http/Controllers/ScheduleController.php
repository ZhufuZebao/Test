<?php
/**
 * スケジュール管理ページのコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
//use Request;
use DB;
use App\Schedule;
use App\Calendar;
use App\Schedulesetting;

class ScheduleController extends \App\Http\Controllers\Controller
{
    /**
     * スケジュール管理のトップページ
     *
     * @param   int     $ref    ・・・ -1=1か月前, 0=当月, 1=1か月後・・・
     */
    public function index($ref)
    {
        if (\Auth::check()) {

            // 今日の年月日をプロパティにセット
            $today_y = (int)date('Y');
            $today_m = (int)date('m');
            $today_d = (int)date('d');

            if (!$ref) {
                $ref = 0;
            }

            // カレンダーを作成
            $cal = new Calendar();
            $calendar = $cal->makeCalendar($ref);
//print_r($calendar);

            // 表示用の年月を算出
            $display_y = $today_y;
            $display_m = $today_m;
            $display_m = $display_m + $ref;
            if ($display_m < 1) {
                $display_m = 12 + $display_m;
                $display_y -= 1;
            } else if ($display_m > 12) {
                $display_m = $display_m - 12;
                $display_y += 1 ;
            }

            $schedule = Schedule::getSchedules(Auth::id(), $display_y, $display_m);
//print_r($schedule);
//exit();

            $types = DB::table('types')->get();

            return view('/schedule/index', [
                    'calendar'  => $calendar,
                    'display_y' => $display_y,
                    'display_m' => $display_m,
                    'today_y'   => $today_y,
                    'today_m'   => $today_m,
                    'today_d'   => $today_d,
                    'schedule'  => $schedule,
                    'ref'       => $ref,
                    'types'    => $types,
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * 新規のスケジュール登録ページ
     *
     * @param   int     $ref    ・・・ -1=1か月前, 0=当月, 1=1か月後・・・
     * @param   int     $y      年
     * @param   int     $m      月
     * @param   int     $d      日
     */
    public function new($ref, $y, $m, $d, $back)
    {
        if (\Auth::check()) {

            $types = DB::table('types')->get();

            return view('/schedule/new', [
                'ref'      => $ref,
                'target_y' => $y,
                'target_m' => $m,
                'target_d' => $d,
                'types'    => $types,
                'back'     => $back,
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * スケジュールの更新ページ
     *
     * @param   int     $id         スケジュールID
     * @param   int     $sub_id     サブID
     * @param   int     $ref        ・・・ -1=1か月前, 0=当月, 1=1か月後・・・
     */
    public function edit($id, $sub_id, $ref, $back)
    {
        if (\Auth::check()) {
            $data = Schedule::getOneDaySchedules(Auth::id(), $id, $sub_id);
            if (!$data) {
                abort('500', 'データがありません。');
            }

            $types = DB::table('types')->get();

            return view('/schedule/edit', [
                'id'       => $id,
                'sub_id'   => $sub_id,
                'target_y' => $data->year,
                'target_m' => $data->month,
                'target_d' => $data->day,
                'data'     => $data,
                'ref'      => $ref,
                'types'    => $types,
                'back'     => $back,
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * スケジュールの入力確認ページ
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function confirm(Request $request)
    {
        if (\Auth::check()) {

            $data = $request->all();

            $st_span = null;
            $ed_span = null;
            if ($request->input('st_y') && $request->input('st_m') && $request->input('st_d') &&
            $request->input('ed_y') && $request->input('ed_m') && $request->input('ed_d')) {
                $st_span = sprintf('%04d/%02d/%02d', $request->input('st_y'), $request->input('st_m'), $request->input('st_d'));
                $ed_span = sprintf('%04d/%02d/%02d', $request->input('ed_y'), $request->input('ed_m'), $request->input('ed_d'));
            }
//echo $st_span.'<br>';
//echo $ed_span;
//exit();

            if ($st_span != null) {
                $data['st_span'] = $st_span;
            }
            if ($ed_span != null) {
                $data['ed_span'] = $ed_span;
            }
//print_r($data);

            //$result = validator($data)->validate();
            $validator = Validator::make($data, [
                    'subject' => 'required|max:180',
                    'commet'  => 'max:1000',
                    'st_span' => 'date',
                    'ed_span' => 'date',
            ], [
                    'subject.required'  => 'タイトルを入力してください。',
                    'subject.max'       => 'タイトルは90文字以内で入力してください。',
                    'comment.max'       => '内容は500文字以内で入力してください。',
                    'st_span.date'      => '期間開始日付を正しく選択してください。',
                    'ed_span.date'      => '期間終了日付を正しく選択してください。',
            ])->validate();

            $request->flash();

            return view('/schedule/confirm', $data);

        } else {
            return redirect('/login');
        }
    }

    /**
     * 入力されたスケジュールをデータベースに登録する
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function regist(Request $request)
    {
        if (\Auth::check()) {

            $ref = $request->input('ref');
            $id = $request->input('id');

            $st_span = null;
            $ed_span = null;
            if ($request->old('st_y') && $request->old('st_m') && $request->old('st_d') &&
                $request->old('ed_y') && $request->old('ed_m') && $request->old('ed_d')) {
                $st_span = sprintf('%04d/%02d/%02d', $request->old('st_y'), $request->old('st_m'), $request->old('st_d'));
                $ed_span = sprintf('%04d/%02d/%02d', $request->old('ed_y'), $request->old('ed_m'), $request->old('ed_d'));
                $daydiff = (strtotime($ed_span) - strtotime($st_span)) / (3600 * 24);
                $monthdiff = ($request->old('ed_y') * 12 + $request->old('ed_m'))
                    - ($request->old('st_y') * 12 + $request->old('st_m'));
            }

            $data = [
                    'subject'   => $request->old('subject'),
                    'comment'   => $request->old('comment'),
            ];

            if ($st_span != null) {
                $data['st_span'] = $st_span;
            }
            if ($ed_span!= null) {
                $data['ed_span'] = $ed_span;
            }

            $result = validator($data);

            $st_time = sprintf('%02d:%02d', $request->old('st_time_h'), $request->old('st_time_m'));
            $ed_time = sprintf('%02d:%02d', $request->old('ed_time_h'), $request->old('ed_time_m'));

            // 更新の時
            if ($id != '') {
                $relation_id = $request->input('relation_id');

                if ($relation_id) {
                    DB::table('schedulesubs')->where('relation_id', '=', $relation_id)->delete();
                    DB::table('schedules')->where('id', '=', $relation_id)->delete();

                } else {
                    DB::table('schedules')->where('id', '=', $id)->delete();
                }
            }

            // 新規登録
            switch($request->old('repeat')) {
                // 1日だけ
                case 0:
                    Schedule::setOneDay(Auth::id(), $request->old('date'),
                    $st_time, $ed_time, $request->old('type'), $request->old('subject'), $request->old('comment'),
                    0, null, null);
                    break;

                // 毎日
                case 1:
                    Schedule::setEveryDay(Auth::id(), $request->old('date'),
                    $st_time, $ed_time, $request->old('type'), $request->old('subject'), $request->old('comment'),
                    $st_span, $ed_span, $daydiff);
                    break;

                // 毎週
                case 2:
                    Schedule::setEveryWeek(Auth::id(), $request->old('date'),
                    $st_time, $ed_time, $request->old('type'), $request->old('subject'), $request->old('comment'),
                    $st_span, $ed_span, $daydiff);
                    break;

                // 毎月
                case 3:
                    Schedule::setEveryMonth(Auth::id(), $request->old('date'),
                    $st_time, $ed_time, $request->old('type'), $request->old('subject'), $request->old('comment'),
                    $st_span, $ed_span, $monthdiff);
                    break;

                // 曜日指定
                case 4:
                    $weekArray = array();
                    for ($i = 1; $i <= 7; $i++) {
                        $weekArray[$i] = ($request->old('week'. $i) == 1) ? 1 : null;
                    }
                    Schedule::setWeek(Auth::id(), $request->old('date'),
                            $st_time, $ed_time, $request->old('type'), $request->old('subject'), $request->old('comment'),
                    $st_span, $ed_span, $daydiff, $weekArray);
            }

            $back = $request->input('back');

            if ($back == 'scheduleday') {
                return redirect('/'. $back. '/y/'. $request->old('st_y'). '/m/'. $request->old('st_m'). '/d/'. $request->old('st_d'));

            } else {
                return redirect('/'. $back. '/ref/'. $ref);
            }

            return redirect('/'. $back. '/ref/'. $ref);

        } else {
            return redirect('/login');
        }
    }

    /**
     * 既に登録済みのスケジュールを削除する
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function delete(Request $request)
    {
        if (\Auth::check()) {

            $ref            = $request->input('ref');
            $id             = $request->input('id');
            $sub_id         = $request->input('sub_id');
            $relation_id    = $request->input('relation_id');
            $delete_kbn     = $request->input('delete_kbn');
            $target_date    = $request->input('date');

            switch ($delete_kbn) {
                // １日だけ
                case 1:
                    if ($relation_id) {
                        DB::table('schedulesubs')->where('id', '=', $sub_id)->delete();
                    } else {
                        DB::table('schedules')->where('id', '=', $id)->delete();
                    }
                    break;

                case 2:
                    DB::table('schedulesubs')
                        ->where('relation_id', '=', $relation_id)
                        ->where('s_date', '>=', $target_date)
                        ->delete();
                    break;

                // 期間内すべて削除
                case 3:
                    if ($relation_id) {
                        DB::table('schedulesubs')->where('relation_id', '=', $relation_id)->delete();
                        DB::table('schedules')->where('id', '=', $relation_id)->delete();

                    } else {
                        DB::table('schedules')->where('id', '=', $id)->delete();
                    }
                    break;
            }

            $back = $request->input('back');

            if ($back == 'scheduleday') {
                return redirect('/'. $back. '/y/'. $request->old('st_y'). '/m/'. $request->old('st_m'). '/d/'. $request->old('st_d'));

            } else {
                return redirect('/'. $back. '/ref/'. $ref);
            }

        } else {
            return redirect('/login');
        }
    }

    /**
     * 入力検証
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($data)
    {
//print_r($data);
//exit();
        return Validator::make($data, [
            'subject' => 'required|max:180',
            'commet'  => 'max:1000',
            'st_span' => 'date',
            'ed_span' => 'date',
        ], [
            'subject.required'  => 'タイトルを入力してください。',
            'subject.max'       => 'タイトルは90文字以内で入力してください。',
            'comment.max'       => '内容は500文字以内で入力してください。',
            'st_span.date'      => '期間開始日付を正しく選択してください。',
            'ed_span.date'      => '期間終了日付を正しく選択してください。',
        ]);
    }

    /**
     * 週間スケジュール
     *
     * @param   int     $ref        ・・・ -1=1か月前, 0=当月, 1=1か月後・・・
     */
    public function week($ref)
    {
        // 今日の年月日をプロパティにセット
        $today_y = (int)date('Y');
        $today_m = (int)date('m');
        $today_d = (int)date('d');

        if (!$ref) {
            $ref = 0;
        }

        // カレンダーを作成
        $cal = new Calendar();
        $week = $cal->getWeekCalendar($ref);
//print_r($week);

        $display_y = substr($week[0], 0, 4);
        $display_m = (int)substr($week[0], 5, 2);
        $display_d = (int)substr($week[0], -2);

        $schedule = Schedule::getSchedules(Auth::id(), $display_y, $display_m, $display_d);
//print_r($schedule);
//exit();

        $setting= new Schedulesetting();

        $st_hour = $setting->where('name', 'st_hour')->value('value');
        if ($st_hour == '') $st_hour = 0;
        $ed_hour = $setting->where('name', 'ed_hour')->value('value');
        if ($ed_hour == '') $ed_hour = 0;

        return view('/schedule/week', [
                'week'  => $week,
                'display_y' => $display_y,
                'display_m' => $display_m,
                'today_y'   => $today_y,
                'today_m'   => $today_m,
                'today_d'   => $today_d,
                'schedule'  => $schedule,
                'ref'       => $ref,
                'st_hour'   => $st_hour,
                'ed_hour'   => $ed_hour,
        ]);
    }

    /**
     * １日のスケジュール
     *
     * @param   int     $display_y      年
     * @param   int     $display_m      月
     * @param   int     $display_d      日
     */
    public function day($display_y, $display_m, $display_d)
    {
        $schedule = Schedule::getSchedules(Auth::id(), $display_y, $display_m, $display_d);
//print_r(isset($schedule[$display_d]) ? $schedule[$display_d] : array());
//exit();

        $prev_date = date('Ymd', strtotime(sprintf('%04d/%02d/%02d', $display_y, $display_m, $display_d). ' -1 day'));
        $next_date = date('Ymd', strtotime(sprintf('%04d/%02d/%02d', $display_y, $display_m, $display_d). ' +1 day'));


        $setting= new Schedulesetting();

        $st_hour = $setting->where('name', 'st_hour')->value('value');
        if ($st_hour == '') $st_hour = 0;
        $ed_hour = $setting->where('name', 'ed_hour')->value('value');
        if ($ed_hour == '') $ed_hour = 0;

        return view('/schedule/day', [
                'display_y' => $display_y,
                'display_m' => $display_m,
                'display_d' => $display_d,

                'prev_y'    => substr($prev_date, 0, 4),
                'prev_m'    => (int)substr($prev_date, 4, 2),
                'prev_d'    => (int)substr($prev_date, -2),

                'next_y'    => substr($next_date, 0, 4),
                'next_m'    => (int)substr($next_date, 4, 2),
                'next_d'    => (int)substr($next_date, -2),

                'schedule'  => (isset($schedule[$display_d]) ? $schedule[$display_d] : array()),
                'st_hour'   => $st_hour,
                'ed_hour'   => $ed_hour,
        ]);
    }

    /**
     * スケジュール設定
     *
     */
    public function setting()
    {
        $setting= new Schedulesetting();

        $st_hour = $setting->where('name', 'st_hour')->value('value');
        $ed_hour = $setting->where('name', 'ed_hour')->value('value');

        return view('/schedule/setting', [
                'st_hour'   => $st_hour,
                'ed_hour'   => $ed_hour,
        ]);
    }

    /**
     * スケジュール設定の登録
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function registsetting(Request $request)
    {
        $st_hour = $request->input('st_hour');
        $ed_hour = $request->input('ed_hour');

        $setting= new Schedulesetting();

        $already= $setting->where('name', 'st_hour')->first();

        if ($already) {
            $setting->where('name', 'st_hour')
            ->update([
                    'value' => $st_hour,
            ]);
        } else {
            $setting->name  = 'st_hour';
            $setting->value = $st_hour;
            $setting->save();
        }

        $already= $setting->where('name', 'ed_hour')->first();
        if ($already) {
            $setting->where('name', 'ed_hour')
            ->update([
                    'value' => $ed_hour,
            ]);

        } else {
            $setting->name  = 'ed_hour';
            $setting->value = $ed_hour;
            $setting->save();
        }

        return redirect('/schedulesetting');
    }
}
