<?php
/**
 * ダッシュボード
 */

namespace App\Http\Controllers\Web;

use App\Models\ChatTask;
use App\Models\Dashboard;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\Schedule;
use App\Models\SysNotice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class DashboardController extends \App\Http\Controllers\Controller
{   //ページネーションのデフォルトの50データ
    private $pagination = 50;

    /**
     * method: GET
     */
    public function index()
    {
        return view('index');
    }

    /**
     * 新着情報をクリックすると、新着情報は既読メッセージになります
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editDashBoardStatus(Request $request)
    {
        try {
            Dashboard::where('related_id', $request->get('id'))->where('to_user_id',Auth::id())
                ->where('type',$request->get('type'))->update(['read'=>1]);
        } catch (\PDOException $e) {
            //データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, [$error]);
        }
        return $this->json();
    }

    public function  setNodeId(Request $request){
        if($request->input('id')){
            $key = 'findNodeId_' . Auth::id();
            Redis::setex($key, 10,$request->input('id'));
        }
        $doc_db = config('const.db_database_doc');
        $node = DB::table("$doc_db.nodes")->whereNull('deleted_at')->where('node_id', $request->input('id'))->first();
        if ($node){
            if ($node->project_id){
                $count=ProjectParticipant::where('project_id',$node->project_id)->where('user_id',Auth::id())->count();
                return $count;
            }elseif ($node->enterprise_id && $node->enterprise_id == Auth::user()->enterprise_id){
                return 1;
            }
        }
        return 0;
    }
    /**
     * dashboardsテーブル増加データ
     * @param $related_id = 関連付けID
     * @param $type = 種類:0,チャット,1,案件チャット,2,タスク,3,スケジュール,4,案件,5,ドキュメント,6,協力会社,7,職人,8,施主
     * @param $title = デフォルトのメッセージ, チャットでメッセージを表示
     * @param $content = チャットで人を送る
     * @param $toUserId
     */
    public function addDashboard($related_id, $type, $title, $content, $toUserId ,$add = null)
    {

        if($type == 3){
            $res = Dashboard::where('type', $type)->where('related_id', $related_id)
                ->where('to_user_id', $toUserId)->first();
        }else{
            $res = Dashboard::where('type', $type)->where('related_id', $related_id)
                ->where('from_user_id', Auth::id())->where('to_user_id', $toUserId)->first();
        }
        if (isset($res) && (intval($type) <= 1 || intval($type) == 3) && !$add) {
            $dashboard = $res;
        } else {
            $dashboard = new Dashboard();
        }
        //登録時の協力会社，Auth::id() is null
        if(Auth::id()){
            $userId = Auth::id();
        }else{
            $userId = 0;
        }
        $dashboard->sort_time = time();
        $dashboard->related_id = $related_id;
        $dashboard->read = 0;
        $dashboard->type = $type;
        $dashboard->title = $title;
        $dashboard->content = $content;
        $dashboard->from_user_id = $userId;
        $dashboard->to_user_id = $toUserId;
        $dashboard->save();
    }
    /**
    *職人参加者としてのダッシュボード
    */
    private function getExceptIdArr(){
        $projectController = new ProjectController();
        $userArr=$projectController->getChatContact();
        $project = Project::where(function ($q) {
            // 共有者
            $q->whereHas('projectParticipant', function ($q1) {
                $q1->where('user_id', Auth::id());
            });
        })->pluck('id')->toArray();
        //【案件】共有者リストと、連絡先と、アプリの案件のグループチャットのメンバーと、表示される内容が違う　＃1860
        //---start---
        //自分は他の案件共有者　職人登録
        $projectParticipantsProjIds = $projectController->getProjectIdWhereProjectParticipantsIsChatContact($project);
        $newProjectArr = [];
        foreach ($project as $itemKey2 => $projItem2){
            if (in_array($projItem2,$projectParticipantsProjIds)) {
                array_push($newProjectArr,$projItem2);
            }
        }
        $project = $newProjectArr;
        //---end---
        //職人として働くときは、ケースダッシュボードを取り外してください
        $dashIdArr = Dashboard::where('type', 4)->where('to_user_id',Auth::id())
            ->orderBy('sort_time','desc')->wherein('related_id',$project)->pluck('id')->toArray();
        return $dashIdArr;
    }
    /**
     * fetch dashboard message
     * @return array
     */
    public function getDashboardList()
    {
        //#2796【案件】他社社員ユーザを職人登録した際に、案件情報を見えないように変更する
        $dashIdArr=$this->getExceptIdArr();
        $dashboards = Dashboard::where('to_user_id', Auth::id())->where('from_user_id','!=',Auth::id())->whereNotIn('id',$dashIdArr)
            ->orderBy('sort_time', 'desc')
            ->paginate($this->getPagesize($this->pagination))->toArray();
        foreach ($dashboards['data'] as $key =>$value){
            if($value['type'] == 3){
                $value['st_datetime']=DB::table('schedules')->where('id',$value['related_id'])->value('st_datetime');
            }else{
                $value['st_datetime']='';
            }
            $dashboards['data'][$key]=$value;
        }
        $user = Auth::user();
        $date = date('Y-m-d');
        $project = $this->getProject(); //案件通知
        $sch = $this->getSchList($date); //sch通知
        $task = $this->getTask($date);//タスク通知
        $date = array('date' => date('Y/m/d'));

        //dashboard  task並べ替え
        $sortCols = [];
        foreach ($task['task'] as $item)
        {
            $sortCols[] = $item['sort_date'];
        }
        array_multisort($sortCols, SORT_DESC, SORT_LOCALE_STRING,$task['task']);

        return array_merge_recursive($task, $sch, $project, $date, ['newMsg' => $dashboards],['user' => $user]);
    }

    /**
     * 新着情報をsch通知
     * @param $date
     * @return array
     */
    private function getSchList($date)
    {
        $stDate = date('Y-m-d H:i', strtotime($date));
        $edDate = date('Y-m-d H:i', strtotime("$date +1 day -1 Minute"));
        $schedules = Schedule::where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) { //繰り返し
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
        })->with('scheduleSubs')->with([
            'users' => function ($q) {
                $q->whereNull('scheduleparticipants.deleted_at');
                $q->select(['users.id', 'users.name']);
            }
        ])->get()->toArray();

        $createUserIds = [];
        foreach ($schedules as $item) {
            $createUserIds[] = $item['created_user_id'];
        }
        $users = User::withTrashed()->whereIn('id', $createUserIds)->get();
        $schArr = [];
        foreach ($schedules as $sch) {
            if ($sch['repeat_kbn'] != '0') { //定期的なイベント
                foreach ($sch['schedule_subs'] as $item) {
                    if ($item['s_date'] == $date) {
                        $arr_filter = [];
                        $arr_filter['type'] = $sch['type'];
                        $arr_filter['subject'] = $sch['subject'];
                        $arr_filter['content'] = $sch['comment'];
                        $arr_filter['address'] = $sch['location'];
                        $arr_filter['all_day'] = $sch['all_day'];
                        $arr_filter['startTime'] = date('H:i', strtotime($sch['st_datetime']));
                        $arr_filter['finishTime'] = date('H:i', strtotime($sch['ed_datetime']));
                        $sch['stDate'] = date("Y/m/d");
                        $sch['edDate'] = date("Y/m/d");
                        //登録者
                        $uc = $this->get_user($sch['created_user_id'], $users);
                        $arr_filter['createUser'] = $uc;
                        $arr_filter['createDate'] = $sch['created_at'];
                        //更新者
                        if ($sch['updated_at']) {
                            $uu = $this->get_user($sch['updated_user_id'], $users);
                            $arr_filter['updateUser'] = $uu;
                            $arr_filter['updateDate'] = $sch['updated_at'];
                        } else {
                            $arr_filter['updateUser'] = '';
                            $arr_filter['updateDate'] = '';
                        }
                        $arr_filter['participantUsers'] = $sch['users'];
                        $schArr[] = $arr_filter;
                    }
                }
            } else { //通常のイベント
                $stDate = date("Y-m-d", strtotime($sch['st_datetime']));
                $edDate = date("Y-m-d", strtotime($sch['ed_datetime']));
                $arr_filter = [];
                $arr_filter['type'] = $sch['type'];
                $arr_filter['subject'] = $sch['subject'];
                $arr_filter['content'] = $sch['comment'];
                $arr_filter['address'] = $sch['location'];
                $arr_filter['all_day'] = $sch['all_day'];
                $arr_filter['stDate'] = date("Y/m/d", strtotime($sch['st_datetime']));
                $arr_filter['edDate'] = date("Y/m/d", strtotime($sch['ed_datetime']));
                $arr_filter['startTime'] = date('H:i', strtotime($sch['st_datetime']));
                $arr_filter['finishTime'] = date('H:i', strtotime($sch['ed_datetime']));
                if ($stDate != $edDate) {
                    if ($date > $stDate) {
                        $arr_filter['startTime'] = date('H:i', strtotime($date));
                    }
                    if ($date < $edDate) {
                        $arr_filter['finishTime'] = date('H:i', strtotime("$edDate +1 day -1 Minute"));
                    }
                }
                //登録者
                $uc = $this->get_user($sch['created_user_id'], $users);
                $arr_filter['createUser'] = $uc;
                $arr_filter['createDate'] = $sch['created_at'];
                //更新者
                if ($sch['updated_at']) {
                    $uu = $this->get_user($sch['updated_user_id'], $users);
                    $arr_filter['updateUser'] = $uu;
                    $arr_filter['updateDate'] = $sch['updated_at'];
                } else {
                    $arr_filter['updateUser'] = '';
                    $arr_filter['updateDate'] = '';
                }
                $arr_filter['participantUsers'] = $sch['users'];
                $schArr[] = $arr_filter;
            }
        }
        $start_time_ts = array_column($schArr, 'startTime');//索引付け
        array_multisort($start_time_ts, SORT_ASC, $schArr);//結果セットのランキング
        return ['schedule' => $schArr];
    }
    /**
     * ユーザー情報を取得する
     * @param $uid
     * @param $users
     * @return mixed
     */
    private function get_user($uid, $users)
    {
        foreach ($users as $user) {
            if ($uid == $user->id) {
                return $user;
            }
        }
    }

    /**
     * 新着情報をタスク通知
     * @param $date
     * @return array
     */
    private function getTask($date)
    {
        $stDate = date('Y-m-d H:i', strtotime('1970-01-01'));
        $edDate = date('Y-m-d H:i', strtotime("$date +1 day -1 Minute"));
        $tasks = ChatTask::whereBetween('limit_date', [$stDate, $edDate])
            ->where(function ($q) {
                $q->where('create_user_id', Auth::id());
                $q->orWhereHas('chattaskcharges', function ($q1) {

                    $q1->where('user_id', Auth::id());
                });
            })->whereNull('complete_date')->orderBy('limit_date',"desc")->with('chatmessages')->get()->toArray();
        $res = [];
        foreach ($tasks as $task) {
            $arr_filter = [];
            $arr_filter['note'] = $task['note'];
            $arr_filter['id'] = $task['id'];
            $arr_filter['chatmessages'] = $task['chatmessages'];
            $arr_filter['group_id'] = $task['group_id'];
            if ($task['limit_date'] < date('Y-m-d H:i') && !$task['complete_date']) {
                $arr_filter['expired'] = true;
            } else {
                $arr_filter['expired'] = false;
            }

            //dashboard  task並べ替え
            if ($task['updated_at']) {
                //あるなら、彼を使います。
                $arr_filter['sort_date'] = $task['updated_at'];
            } else {
                $arr_filter['sort_date'] = $task['created_at'];
            }
            if (count($res) < 20){
                $res[] = $arr_filter;
            }
        }
        return ['task' => $res];
    }

    /**
     * スクロールして取得新着情報次のページのコンテンツ
     * 案件通知
     * @return array
     */
    private function getProject()
    {
        $dashboard = Dashboard::where('type', 4)->where('from_user_id','!=',Auth::id())->where('to_user_id',Auth::id())
            ->select(DB::raw('count(*) as num, related_id'))->where('read',0)
            ->groupBy('related_id')->get()->toArray();

        //#2796【案件】他社社員ユーザを職人登録した際に、案件情報を見えないように変更する
        $projectController = new ProjectController();
        $userArr=$projectController->getChatContact();
        $projectIdArr = Dashboard::where('type', 4)->where('pinned_mark', 1)->where('to_user_id',Auth::id())
            ->orderBy('sort_time','desc')->take(6)->pluck('related_id')->toArray();
        $projectIdArr = array_unique($projectIdArr);
        $middlerIdArr = $projectIdArr;
        if (count($projectIdArr) < 6){
            $project = Project::WhereIn('id', $projectIdArr)->whereNotIn('created_by',$userArr)->get(['id','subject_image','place_name','created_at','updated_at'])->toArray();
            $item1 = Project::where(function ($q)  use ($userArr){
                // 作成者と共有者
                $q->where('created_by', Auth::id());
                $q->orWhereHas('projectParticipant', function ($q1) use ($userArr) {
                    $q1->where('user_id', Auth::id())->whereNotIn('created_by',$userArr);
                });
            })->orderBy('updated_at','desc')->take(6)->get(['id','subject_image','place_name','created_at','updated_at'])->toArray();
            $project = array_merge($project,$item1);
        }else{
            $project = Project::where(function ($q) {
                // 作成者と共有者
                $q->where('created_by', Auth::id());
                $q->orWhereHas('projectParticipant', function ($q1) {
                    $q1->where('user_id', Auth::id());
                });
            })->whereIn('id', $middlerIdArr)->whereNotIn('created_by',$userArr)->get(['id','subject_image','place_name','created_at','updated_at'])->toArray();
        }
        //【案件】共有者リストと、連絡先と、アプリの案件のグループチャットのメンバーと、表示される内容が違う　＃1860
        //---start---
        $projIds = [];
        foreach ($project as $projItem){
            $projIds[] = $projItem['id'];
        }
        //自分は他の案件共有者　職人登録
        $projectParticipantsProjIds = $projectController->getProjectIdWhereProjectParticipantsIsChatContact($projIds);
        $newProjectArr = [];
        foreach ($project as $itemKey2 => $projItem2){
            if (!in_array($projItem2['id'],$projectParticipantsProjIds)) {
                array_push($newProjectArr,$project[$itemKey2]);
            }
        }
        $project = $newProjectArr;
        //---end---
        foreach ($project as $k=>$item){
            $item['num'] = 0;
            foreach ($dashboard as $value){
                if ($value['related_id'] == $item['id']){
                    $item['num'] = $value['num'];
                }
            }
            if (in_array($item['id'],$middlerIdArr)){
                $item['fixedLable'] = true;
            }else{
                $item['fixedLable'] = false;
            }
            $project[$k] = $item;
        }
        $last_nums = array_column($project,'updated_at');
        array_multisort($last_nums,SORT_DESC,$project);
        $res = [];
        $ids = [];
        foreach ($project as $item){
            if (in_array($item['id'],$middlerIdArr) && !in_array($item['id'],$ids)){
                $ids[] = $item['id'];
                $res[]=$item;
            }
        }
        foreach ($project as $item){
            if (count($res)>5){
                break;
            }else {
                if (!in_array($item['id'],$ids)){
                $ids[] = $item['id'];
                $res[]=$item;
                }
            }
        }
        $sortCols = [];
        foreach ($res as $item)
        {
            $sortCols[] = $item['updated_at'];
        }
        array_multisort($sortCols, SORT_DESC, SORT_LOCALE_STRING,$res);
        return ['project' => $res];
    }


    /**
     * 通知の詳細を取得する
     * @param Request $request
     * @return mixed
     */
    public function getSysNoticeDetail(Request $request)
    {
        $noticeId = $request->get('id');
        return SysNotice::where('id', $noticeId)->get();
    }

    /**
     * 右クリックしてケース操作をマークします
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setProjectFixedLabel(Request $request)
    {
        try {
            $related_id = $request->get('id');
            $dashboard = Dashboard::where('related_id', $related_id)->where('type', 4)->where('to_user_id',Auth::id())
                ->first();
            $count = ProjectParticipant::where('project_id',$related_id)->where('user_id', Auth::id())->count();
            //案件の参加者かどうかを判断する
            if ($count) { //验证
                if (isset($dashboard)){
                    if ($dashboard->pinned_mark == '1') {
                        $dashboard->pinned_mark = '0';
                    } else {
                        $dashboard->pinned_mark = '1';
                    }
                    $dashboard->update();
                }else{
                    $res = new Dashboard();
                    $res->type = 4;
                    $res->title = '';
                    $res->related_id = $related_id;
                    $res->from_user_id = Auth::id();
                    $res->to_user_id = Auth::id();
                    $res->pinned_mark = '1';
                    $res->save();
                }
                return $this->json();
            }
            return $this->error('', trans('messages.error.system'));
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }


    /**
     * すべて確認済みにする
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearNewMsg(Request $request)
    {
        try {
            if (is_array($request->get('type')) && empty($request->get('type'))) {
                Dashboard::where('to_user_id', Auth::id())->update(['read'=>1]);
            }else if(is_array($request->get('type')) && !empty($request->get('type'))){
                Dashboard::where('to_user_id', Auth::id())->whereIn('type', $request->get('type'))->update(['read'=>1]);
            }else{

            }
            $res = $this->getNewMsg($request);
            //#2654 ダッシュボード】案件の新着を表す数字が「すべて確認済み」をクリックしても消えない
            $res['project'] = $this->getProject()['project']; //案件通知
            return $this->json('',$res);
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }
    /**
     * 案件の未読番号をクリアします
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearProjectNum(Request $request){
        try {
            Dashboard::where('to_user_id', Auth::id())->where('type',4)
                ->where('related_id',$request->get('id'))->update(['read'=>1]);
            return $this->json();
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }

    /**
     * デフォルトのページネーション
     * @param int $default
     * @return mixed
     */
    protected function getPagesize($default = 20)
    {
        return parent::getPagesize($default);
    }


    /**
     * 得る新着情報
     * $type = 種類:0,チャット,1,案件チャット,2,タスク,3,スケジュール,4,案件,5,ドキュメント,6,協力会社,7,職人,8,施主
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function getNewMsg($request){
        $typeArr = ['0', '1', '2', '3', '4', '5', '6', '7', '8'];
        //配列が空の場合
        if (is_array($request->get('type')) && empty($request->get('type'))) {
            $type = $typeArr;
            //配列の値は指定されたタイプです
        } else if (is_array($request->get('type')) && !array_diff($request->get('type'), $typeArr)) {
            $type = $request->get('type');
        } else {
            throw new \PDOException(trans('messages.error.system'));
        }
        //#2796【案件】他社社員ユーザを職人登録した際に、案件情報を見えないように変更する
        $dashIdArr=$this->getExceptIdArr();
        //get date
        $dashboardArr = Dashboard::where('to_user_id', Auth::id())->where('from_user_id', '!=', Auth::id())
            ->whereIn('type', $type)->whereNotIn('id',$dashIdArr)->pluck('id');
        //add page
        $dashboards = Dashboard::whereIn('id', $dashboardArr)
            ->orderBy('sort_time', 'desc')
            ->paginate($this->getPagesize($this->pagination))->toArray();

        //When you click "schedule", add 'st_datetime'
        //--------------start--------------
        $scheduleArrayTmp = [];
        foreach ($dashboards['data'] as $key =>$value){
            if($value['type'] == 3){
                $scheduleArrayTmp[] = $value;
            }
        }

        $scheduleIds = array_column($scheduleArrayTmp,'related_id');
        $schModelArray = DB::table('schedules')->select('id', 'st_datetime')->whereIn('id',$scheduleIds)->get()->toArray();

        foreach ($dashboards['data'] as $keyDashboard => $dashItem) {
            foreach ($schModelArray as $key => $schItem) {
                if ($dashboards['data'][$keyDashboard]['related_id'] == $schItem->id) {
                    $dashboards['data'][$keyDashboard]['st_datetime'] = $schItem->st_datetime;
                }
            }
        }
        //--------------end--------------

        return $dashboards;
    }

    /**
     * ページめくりが得られます新しいメッセージデータ
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardNewMsg(Request $request){
        try {
            $res = $this->getNewMsg($request);
            return $this->json('',$res);
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }
}
