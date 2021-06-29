<?php
/**
 * Created by goki
 */

namespace App\Http\Controllers\Web;

use App\Helpers\ApiHelper;
use App\Http\Facades\Common;
use App\Http\Services\FirebaseService;
use App\Mail\ContactFriend;
use App\Mail\ContactInvite;
use App\Models\ChatLastRead;
use App\Models\ChatList;
use App\Models\ChatMessage;
use App\Models\Developer;
use App\Models\EnterpriseParticipant;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\ScheduleParticipant;
use App\Models\ScheduleSub;
use App\Models\User;
use App\Models\FriendSearchModel;
use App\Models\ChatContact;
use App\Models\ChatGroup;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\UserCategories;
use App\Models\ProjectParticipant;

class FriendController extends \App\Http\Controllers\Controller
{
    /**
     * 仲間 一覧
     */
    public function getList()
    {
        if (Auth::user()->enterprise_id){
            $usersArr = User::where('enterprise_id', Auth::user()->enterprise_id)->withTrashed()->get()->toArray();
        } elseif (Auth::user()->coop_enterprise_id) {
            $usersArr = User::where('coop_enterprise_id', Auth::user()->coop_enterprise_id)->withTrashed()->get()->toArray();
        } else {
            $usersArr = [];
        }
        $res = [];
        foreach ($usersArr as $user) {
            $res[] = $user['id'];
        }
        $resEmail = [];
        foreach ($usersArr as $user) {
            $resEmail[] = $user['email'];
        }
        $model = ChatContact::whereIn('from_user_id', $res)->where(function ($q) use ($res,$resEmail){
            $q->whereNotIn('to_user_id',$res);
        })->with(['accounts'=>function ($q) {
                $q->withTrashed();
            },'accounts.enterprise', 'accounts.coopEnterprise','userworkareas.workareas','userworkareas.workplaces'])
            ->get()->toArray();

        //職人userは事業者の場合「所属」colはenterprisesテーブルのname
        //職人userは協力会社の場合「所属」 colはenterprisesテーブルのname
        //職人userは職人workerの場合「所属」 colはusersテーブルのcompany_name
        foreach ($model as $k=>$item)
        {
            if ($item['accounts']['enterprise']){
                //職人userは事業者の場合
                $model[$k]['accounts']['company_name'] = $item['accounts']['enterprise']['name'];
            }elseif ($item['accounts']['coop_enterprise']){
                //職人userは協力会社の場合
                $model[$k]['accounts']['company_name'] = $item['accounts']['coop_enterprise']['name'];
            }else{
                //職人userは職人workerの場合
                //なしを処理
            }
        }
        $resArr = [];
        $toResArr = [];
        foreach ($model as $item){
            if (!in_array($item['to_user_id'],$toResArr) || !$item['to_user_id']){
                $toResArr[] = $item['to_user_id'];
                $resArr[] = $item;
            }
        }

        //to_user_id filter
        //【連絡先】同じ職人が複数表示されている #3200
        $to_user_id_Tmp = array_column($resArr,'to_user_id');

        //仲間招待者取得
        $modelInvite = ChatContact::where(function ($q){
            $q->where('to_user_id', Auth::id())->orWhere('email',Auth::user()->email);
            })->whereNotIn('from_user_id', $res)
            ->whereNotIn('from_user_id', $to_user_id_Tmp)
            ->with(['accountsInvite'=>function ($q) {
                $q->withTrashed();
            },'accountsInvite.enterprise', 'accountsInvite.coopEnterprise','userworkareasInvite.workareas', 'userworkareasInvite.workplaces'])->get()->toArray();
        for ($i = 0; $i < count($modelInvite); $i++) {
            $modelInvite[$i]['accounts'] = $modelInvite[$i]['accounts_invite'];

            //職人userは事業者の場合「所属」colはenterprisesテーブルのname
            //職人userは協力会社の場合「所属」 colはenterprisesテーブルのname
            //職人userは職人workerの場合「所属」 colはusersテーブルのcompany_name
            if ($modelInvite[$i]['accounts_invite']['enterprise']){
                //職人userは事業者の場合
                $modelInvite[$i]['accounts']['company_name'] = $modelInvite[$i]['accounts_invite']['enterprise']['name'];
            }elseif ($modelInvite[$i]['accounts_invite']['coop_enterprise']){
                //職人userは協力会社の場合
                $modelInvite[$i]['accounts']['company_name'] = $modelInvite[$i]['accounts_invite']['coop_enterprise']['name'];
            }else{
                //職人userは職人workerの場合
                //なしを処理
            }

            $modelInvite[$i]['userworkareas'] = $modelInvite[$i]['userworkareas_invite'];
            $modelInvite[$i]['email'] = $modelInvite[$i]['accounts_invite']['email'];
            $modelInvite[$i]['file'] = $modelInvite[$i]['accounts_invite']['file'];
            //受取人
            $modelInvite[$i]['recipient'] = true;
        }
        $fromResArr = [];
        $resItem = [];
        foreach ($modelInvite as $item){
            if (!in_array($item['accounts_invite']['enterprise_id'],$fromResArr)){
                if($item['accounts_invite']['enterprise_id']){
                    $fromResArr[] = $item['accounts_invite']['enterprise_id'];
                }
                $resItem[] = $item;
            }
        }
        $res = array_merge($resArr, $resItem);

        //承認待ちの場合 日付 = '1970-01-01'
        foreach ($res as $k => $re) {
            //承認待ち
            if ($re['contact_agree'] != 1){
                $res[$k]['contact_date'] = '1970-01-01';
                $res[$k]['created_at'] = '1970-01-01';
                $res[$k]['updated_at'] = '1970-01-01';
            }
        }
        //array filter
        //【連絡先】同じ職人が複数表示されている #3200
        $res = $this->assoc_unique($res,'email');
        
        return $res;
    }
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

    /**
     * 職人の名前、所属会社名で検索する。
     * 既に連携登録済みの職人は除外する。
     * @param Request $request
     * @return array
     */
    public function searchFriend(Request $request)
    {
        $word = Common::escapeDBSelectKeyword($request->post('word'));
        if (strlen($word) == 0) {
            return [];
        }
        $account = User::where('enterprise_id',Auth::user()->enterprise_id)->get('id')->toArray();
        $userRes=[];
        foreach ($account as $item){
            $userRes[] = $item['id'];
        }
        $userIdArr = ChatContact::whereIn('from_user_id', $userRes)
            ->with(['accountsInvite'])->get('to_user_id');
        $usersArr = [];
        foreach ($userIdArr as $userId) {
            $usersArr[] = $userId['to_user_id'];
        }
        if (!Developer::check(Auth::id())){
            $sql = <<<EOF
select user_id from developers;
EOF;
            $result = DB::select($sql);
            foreach ($result as $item){
                if (!in_array($item->user_id,$usersArr)){
                    $usersArr[] = $item->user_id;
                }
            }
        }
        $res = User::WhereNotIn('id', $usersArr)->
        where(function ($q) {
            if (Auth::user()->enterprise_id) {
                $q->where('enterprise_id', '!=', Auth::user()->enterprise_id);
                $q->orWhereNull('enterprise_id');
            } else {
                $q->where('coop_enterprise_id', '!=', Auth::user()->coop_enterprise_id);
                $q->orWhereNull('coop_enterprise_id');
            }
        })->where('id', '!=', Auth::id())
            ->where('worker',1)
            ->where(function ($q) use ($word) {
                $q->where('name', 'LIKE', "%{$word}%");
                $q->orWhere('company_name', 'LIKE', "%{$word}%");
                $q->orWhereHas('enterprise', function ($query) use ($word) {
                    $query->where('name', 'LIKE', "%{$word}%");
                });
                $q->orWhereHas('enterpriseCoop', function ($query) use ($word) {
                    $query->where('name', 'LIKE', "%{$word}%");
                });
            })
            ->with(['enterprise','enterpriseCoop'])
            ->get(['id', 'email', 'name', 'company_name','enterprise_id','coop_enterprise_id']);

        //所属col 構築
        foreach ($res as $k=>$v)
        {
            if ($v['enterprise']){
                $res[$k]['company_name'] = $v['enterprise']['name'];
            }elseif ($v['enterpriseCoop']){
                $res[$k]['company_name'] = $v['enterpriseCoop']['name'];
            }else{
                //職人userは職人workerの場合
                //なしを処理
            }
        }

        $res = $res->toArray();

        //所属col検索
        //enterprise or enterpriseCoopに存在の場合 enterprise.name enterpriseCoop.name検索only
        //company_nameフィールドに基づいて取得したデータをフィルタリングします

        $resTmp = [];

        foreach ($res as $key=>$val)
        {
            if (stripos($val['name'],$word) === false && stripos($val['company_name'],$word) === false){
                //
            }else{
                $resTmp[] = $val;
            }
        }

        return $resTmp;
    }

    /**
     * 仲間DetailInformation
     */
    public function getFriendDetailInformation(Request $request)
    {
        $id = $request->post('userId');
        $sql = <<<EOF
select  u.*, p.name as pref_name, a.city, ifnull(TIMESTAMPDIFF(YEAR, `birth`, CURDATE()), 0) AS age
from    users u
        left join prefs p
            on ifnull(u.pref, 0) = p.id
        left join mst_address_mini a
            on ifnull(u.addr_code, 0) = a.officialCode

where   u.id = ?
AND u.deleted_at IS NULL
EOF;

        $data = DB::select($sql, [
            $id
        ]);

        $sql = <<<EOF
select  wa.id as workarea_id, wa.name as workarea_name, wp.id as workplace_id, wp.name as workplace_name
from    users u
        left join user_workareas uw
            left join workareas wa
                on uw.area_id = wa.id
            left join workplaces wp
                on uw.place_id = wp.id
            on u.id = uw.user_id
where   u.id = ?
AND u.deleted_at IS NULL
AND uw.deleted_at IS NULL
EOF;
        $areas = DB::select($sql, [
            $id
        ]);
        //print_r($areas);

        $tmp = "";
        if (is_array($areas)) {
            foreach ($areas as $area) {
                if ($area->workarea_name) {
                    $tmp .= $area->workarea_name . '　';
                }
                if ($area->workplace_name) {
                    $tmp .= $area->workplace_name . '　';
                }
            }
        }
        $data[0]->workarea = $tmp;
        $data[0]->workareaArray = $areas;

        // 分野取得
        $data[0]->categories = UserCategories::getUserCategories($id);


        $user = User::where('id',$request->post('userId'))
            ->with(['enterprise','enterpriseCoop'])
            ->first()
            ->toArray();

        //職人のcompany_name colを処理
        if ($user['enterprise_id']){
            $data[0]->company_name = $user['enterprise']['name'];
        } elseif ($user['coop_enterprise_id']){
            $data[0]->company_name = $user['enterprise_coop']['name'];
        } else {
            //
        }


        return $data;
    }

    /**
     * 仲間 検索
     * @param Request $request
     * @return array
     */
    public function search(Request $request)
    {
        $query = new FriendSearchModel();
        $searchArray = Common::escapeDBSelectKeyword($request->input('searchName'));
        if (strlen($searchArray) == 0) {
            return $this->getList();
        }
        $models = $query->search($searchArray)
            ->with(['accounts' => function ($q) {
                $q->withTrashed();
            }])
            ->with('accounts.enterprise', 'accounts.coopEnterprise','userworkareas.workareas','userworkareas.workplaces')
            ->get()->toArray();

        foreach ($models as $k=>$item)
        {
            if ($item['accounts']['enterprise']){
                //職人userは事業者の場合
                $models[$k]['accounts']['company_name'] = $item['accounts']['enterprise']['name'];
            }elseif ($item['accounts']['coop_enterprise']){
                //職人userは協力会社の場合
                $models[$k]['accounts']['company_name'] = $item['accounts']['coop_enterprise']['name'];
            }else{
                //職人userは職人workerの場合
                //なしを処理
            }
        }
        $resArr = [];
        $toResArr = [];
        foreach ($models as $item){
            if (!in_array($item['to_user_id'],$toResArr) || !$item['to_user_id']){
                $toResArr[] = $item['to_user_id'];
                $resArr[] = $item;
            }
        }
        $modelsInvite = $query->searchInvite($searchArray)
            ->with(['accountsInvite' => function ($q) {
                $q->withTrashed();
            }])
            ->with('accountsInvite.enterprise', 'accountsInvite.coopEnterprise','userworkareasInvite.workareas','userworkareasInvite.workplaces' )
            ->get()->toArray();
        for ($i = 0; $i < count($modelsInvite); $i++) {
            $modelsInvite[$i]['accounts'] = $modelsInvite[$i]['accounts_invite'];

            //職人userは事業者の場合「所属」colはenterprisesテーブルのname
            //職人userは協力会社の場合「所属」 colはenterprisesテーブルのname
            //職人userは職人workerの場合「所属」 colはusersテーブルのcompany_name
            if ($modelsInvite[$i]['accounts_invite']['enterprise']){
                //職人userは事業者の場合
                $modelsInvite[$i]['accounts']['company_name'] = $modelsInvite[$i]['accounts_invite']['enterprise']['name'];
            }elseif ($modelsInvite[$i]['accounts_invite']['coop_enterprise']){
                //職人userは協力会社の場合
                $modelsInvite[$i]['accounts']['company_name'] = $modelsInvite[$i]['accounts_invite']['coop_enterprise']['name'];
            }else{
                //職人userは職人workerの場合
                //なしを処理
            }

            $modelsInvite[$i]['userworkareas'] = $modelsInvite[$i]['userworkareas_invite'];
            $modelsInvite[$i]['email'] = $modelsInvite[$i]['accounts_invite']['email'];
        }
        $fromResArr = [];
        $resItem = [];
        foreach ($modelsInvite as $item){
            if (!in_array($item['accounts_invite']['enterprise_id'],$fromResArr)){
                if($item['accounts_invite']['enterprise_id']){
                    $fromResArr[] = $item['accounts_invite']['enterprise_id'];
                }
                $resItem[] = $item;
            }
        }
        $res = array_merge($resArr, $resItem);
        $toArr=[];
        //承認待ちの場合 日付 = '1970-01-01'
        foreach ($res as $k => $re) {
            //承認待ち
            if ($re['contact_agree'] != 1){
                $res[$k]['contact_date'] = '1970-01-01';
                $res[$k]['created_at'] = '1970-01-01';
                $res[$k]['updated_at'] = '1970-01-01';
            }
            if(in_array($re['to_user_id'],$toArr)){
                unset($res[$k]);
            }
            $toArr[]=$re['to_user_id'];
        }
        $res=array_values($res);
        return $res;
    }

    private function delCommonEnterprise($idRes, $groupArr)
    {
        //協力会社の関係，私たちは会社
        $partner = [];
        $enterpriseParticipant = EnterpriseParticipant::where('enterprise_id', Auth::user()->enterprise_id)->whereIn('user_id', $idRes)
            ->where('agree', 1)->get('user_id')->toArray();
        foreach ($enterpriseParticipant as $item) {
            $partner[] = $item['user_id'];
        }
        if (count($partner)) {
            $chatGroup = ChatGroup::whereHas('user', function ($q) {
                $q->where('enterprise_id', Auth::user()->enterprise_id);
            })->whereHas('mine', function ($q) use ($partner) {
                $q->whereIn('user_id', $partner);
            })->get('group_id')->toArray();
            foreach ($chatGroup as $item) {
                $groupArr[] = $item['group_id'];
            }
        }
        return ['group'=>$groupArr,'person'=>$partner];
    }

    private function delCommonInvite($idRes, $groupArr,$partner)
    {
        //協力会社の関係，私たちは協力会社
        $arr = [];
        $part = [];
        $enterprises = User::whereIn('id', $idRes)->get(['id','enterprise_id'])->toArray();
        foreach ($enterprises as $item) {
            $arr[] = $item['enterprise_id'];
        }
        if (count($arr)) {
            $enterpriseParticipant = EnterpriseParticipant::where('user_id', Auth::id())->whereIn('enterprise_id', $arr)
                ->where('agree', 1)->get('enterprise_id')->toArray();
            foreach ($enterpriseParticipant as $item) {
                $part[] = $item['enterprise_id'];
            }
            $chatGroup = ChatGroup::where('user_id', Auth::id())->whereHas('mine', function ($q) use ($part) {
                $q->whereHas('user', function ($q1) use ($part) {
                    $q1->whereIn('enterprise_id', $part);
                });
            })->get(['group_id','user_id'])->toArray();
            foreach ($chatGroup as $item) {
                $groupArr[] = $item['group_id'];
                $partner[] = $item['user_id'];
            }
        }
        return ['group'=>$groupArr,'person'=>$partner];
    }

    private function delCommonFriendAndInvite($idRes)
    {
        //協力会社の関係，私たちは会社
        $res = $this->delCommonEnterprise($idRes, []);

        //協力会社の関係，私たちは協力会社
        $res = $this->delCommonInvite($idRes, $res['group'],$res['person']);
        return $res;
    }

    /**
     * 仲間 削除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delFriend(Request $request)
    {
        $ids = $request->get('id');
        $idArr = ChatContact::whereIn('id', $ids)->where('contact_agree', 1)->
            with(['accounts','accountsInvite'])->get(['from_user_id', 'to_user_id', 'email'])->toArray();
        $idRes = [];
        $groupArr = [];
        $personArr = [];
        $fromUsersId = [];
        DB::beginTransaction();
        $dashboard = new DashboardController();
        foreach ($idArr as $arr) {
            //削除者は職人です
            if (Auth::user()->email == $arr['email'] || Auth::id() === $arr['to_user_id']) {
                $fromUsersId[] = $arr['from_user_id'];
                $idsArr = User::where('enterprise_id',$arr['accounts_invite']['enterprise_id'])
                    ->orWhere('id',$arr['from_user_id'])->pluck('id')->toArray();
                foreach ($idsArr as $item){
                    if ($item != Auth::id()){
                        $dashboard->addDashboard($item,7,Auth::user()->name.'さんが削除されました。','',$item);
                    }
                }
                //削除者は社内の従業員です
            } else {
                $idRes[] = $arr['to_user_id'];
                $idsArr = [];
                $toUsrName = '';
                if (Auth::user()->enterprise_id){
                    $idsArr = User::where('id',$arr['to_user_id'])
                        ->orWhere('enterprise_id',Auth::user()->enterprise_id)->pluck('id')->toArray();
                    $toUsrName = User::where('id',$arr['to_user_id'])->pluck('name')->toArray();
                }
                foreach ($idsArr as $item){
                    if ($item != Auth::id()){
                        if($item == $arr['to_user_id']){
                            $dashboard->addDashboard($item,7,Auth::user()->name.'さんが削除されました。','',$item);
                        }else{
                            $dashboard->addDashboard($item,7,$toUsrName[0].'さんが削除されました。','',$item);
                        }
                    }
                }
            }
        }
        $fromEnterpriseId = User::whereIn('id',$fromUsersId)->withTrashed()->pluck('enterprise_id')->toArray();

        $AuthIdArr = User::where(function ($q)use ($fromEnterpriseId){
            $q->whereIn('enterprise_id',$fromEnterpriseId)->whereNotNull('enterprise_id');
        })->orWhereIn('id',$fromUsersId)->withTrashed()->pluck('id')->toArray();

        //案件共有者を削除user
        $userIdsArr = array_merge_recursive($AuthIdArr,$idRes);

        if (count($AuthIdArr)){
            //わたしは職人->グループgroup_id
            $chatArr = ChatGroup::whereHas('user', function ($q) use ($AuthIdArr) {
                $q->whereIn('id', $AuthIdArr);//社内
            })->whereHas('mine', function ($q) {
                $q->where('user_id', Auth::id());//職人
            })->whereHas('group', function ($q) {
                $q->where('kind', 0);// 0=グループチャット
            })->pluck('group_id')->toArray();
            $groupArr = $chatArr;

            //わたしは職人->個人group_id
            $chatPersonArr = ChatGroup::whereHas('user', function ($q) use ($AuthIdArr) {
                $q->whereIn('id', $AuthIdArr);//社内
            })->whereHas('mine', function ($q) {
                $q->where('user_id', Auth::id())->where('admin', 1);//職人
            })->whereHas('group', function ($q) {
                $q->where('kind', 1);//1=ダイレクトチャット
            })->pluck('group_id')->toArray();
            $personArr = $chatPersonArr;
        }
        if (count($idRes)){
            $enterpriseIdsArr = User::where('enterprise_id', Auth::user()->enterprise_id)
                ->withTrashed()->pluck('id')->toArray();

            //わたしは事业者->グループgroup_id
            $groupsArr = ChatGroup::whereHas('group', function ($q) {
                $q->where('kind', 0);
            })->whereHas('mine', function ($q) use ($enterpriseIdsArr) {
                $q->whereIn('user_id', $enterpriseIdsArr)->where('admin', 1);//社内
                //職人
            })->whereIn('user_id', $idRes)->pluck('group_id')->toArray();
            foreach ($groupsArr as $item) {
                if (!in_array($item,$groupArr)){
                    $groupArr[] = $item;
                }
            }

            //わたしは事业者->個人group_id
            $personGroupsArr = ChatGroup::whereHas('group', function ($q) {
                $q->where('kind', 1);
            })->whereHas('mine', function ($q) use ($enterpriseIdsArr) {
                $q->whereIn('user_id', $enterpriseIdsArr)->where('admin', 1);//社内
                //職人
            })->whereIn('user_id', $idRes)->pluck('group_id')->toArray();
            foreach ($personGroupsArr as $item) {
                if (!in_array($item,$personArr)){
                    $personArr[] = $item;
                }
            }
        }
        $notGroupArr = $this->delCommonFriendAndInvite($userIdsArr);
        $groupArrRes = [];
        foreach ($groupArr as $item){
            if (!in_array($item,$notGroupArr['group'])){
                //n v n->group_id
                $groupArrRes[] = $item;
            }
        }
        $personArrRes = [];
        foreach ($personArr as $item){
            if (!in_array($item,$notGroupArr['group'])){
                //1 v 1->group_id
                $personArrRes[] = $item;
            }
        }

        $friendArrRes = [];
        foreach ($idRes as $item){
            if (!in_array($item,$notGroupArr['person'])){
                //only friend user id
                $friendArrRes[] = $item;
            }
        }
        //スケジュールから削除
        $user_arr=User::where('enterprise_id',Auth::user()->enterprise_id)->whereNotNull('enterprise_id')->pluck('id')->toArray();
        if(!$user_arr){
            $user_arr[]=Auth::id();
        }
        $schedule=Schedule::whereIn('created_user_id',$user_arr)->where('ed_datetime','>=',date('Y-m-d H:i:s'))->pluck('id')->toArray();
        try {
            //除職者関係を解消する
            ChatContact::whereIn('id', $ids)->delete();

            if (count($personArrRes))
            {
                //解散グループ 容量を増やす
                $groupArr=Group::whereIn('id', $personArrRes)->get();
                $chat = new ChatController();
                foreach ($groupArr as $group){
                    $chat->deleteContain($group);
                }
                //1 vs 1チャットを削除
                //すべてを削除
                ChatGroup::whereIn('group_id', $personArrRes)->delete();
                ChatList::whereIn('group_id', $personArrRes)->delete();
                //2020-11-2 #2298  チャット情報は削除しないことにした
//                ChatMessage::whereIn('group_id', $personArrRes)->delete();
                ChatLastRead::whereIn('group_id', $personArrRes)->delete();
                Group::whereIn('id', $personArrRes)->delete();
            }
            //おしゃべり組
            if (count($idRes)) {
                //groupチャットを削除
                //グループを削除
                ChatGroup::whereIn('group_id', $groupArr)->whereIn('user_id',$friendArrRes)->delete();
                ChatList::whereIn('group_id', $groupArr)->whereIn('user_id',$friendArrRes)->delete();
                //案件共有者を削除
                $this->deleteProjectParticipants($friendArrRes, $groupArr);
                //schedule->delete
                $this->deleteScheduleParticipant($friendArrRes,$schedule);
            }
            //職人案件共有者
            if (count($AuthIdArr)){
                $this->deleteProjectParticipants($AuthIdArr, $groupArr,1);
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, [$error]);
        }
        return $this->json();
    }
    public function deleteScheduleParticipant($res,$schedule){
        $schedulePartArr=ScheduleParticipant::whereIn('schedule_id',$schedule)->whereIn('user_id',$res)->pluck('schedule_id')->toArray();
        $schedule_arr=Schedule::whereIn('id',$schedulePartArr)->get()->toArray();
        foreach ($schedule_arr as $key=>$value){
            //削除（削除日の後参加者），保留 （削除日の前的参加者）
            if($value['st_datetime'] < date('Y-m-d') ){
                if(!$value['repeat_kbn']){//なし
                    //削除日の後
                    $model = new Schedule();
                    $model->fill($value);
                    unset($model->invite);
                    unset($model->id);
                    unset($model->updated_user_id);
                    unset($model->updated_at);
                    $model->st_datetime=date('Y-m-d');
                    $model->created_at=date('Y-m-d H:i:s');
                    $model->save();
                    $this->savePart($model->id,$value['id'],$res);
                    //削除日の前
                    Schedule::where('id',$value['id'])->update(['ed_datetime'=>date('Y-m-d H:i:s',strtotime(date('Y-m-d'))-1)]);
                }else{
                    //削除日の後
                    $model = new Schedule();
                    $model->fill($value);
                    unset($model->invite);
                    unset($model->id);
                    unset($model->updated_user_id);
                    unset($model->updated_at);
                    $model->created_at=date('Y-m-d H:i:s');
                    $st_time=date('H:i:s',strtotime($model->st_datetime));
                    $ed_time=date('H:i:s',strtotime($model->ed_datetime));
                    $model->st_datetime=date("Y-m-d").' '.$st_time;
                    $model->save();
                    $this->savePart($model->id,$value['id'],$res);
                    $this->savePartSub($model->id,$value['id']);
                    //削除日の前
                    Schedule::where('id',$value['id'])->update(['ed_datetime'=>date("Y-m-d",strtotime("-1 day")).' '.$ed_time]);
                }
            }else{
                ScheduleParticipant::where('schedule_id',$value['id'])->whereIn('user_id',$res)->delete();
            }
        }
    }


    public function savePart($id,$schedule_id,$res){
        $modelPart=ScheduleParticipant::where('schedule_id',$schedule_id)->get()->toArray();
        foreach ($modelPart as $k=>$v){
            $v['schedule_id']=$id;
            unset($v['id']);
            unset($v['updated_at']);
            if(!in_array($v['user_id'],$res)){
                $v['created_at']=date('Y-m-d H:i:s');
                $model=new ScheduleParticipant();
                $model->fill($v);
                $model->save();
            }
        }
    }
    public function savePartSub($id,$schedule_id){
        ScheduleSub::where('relation_id',$schedule_id)->where('s_date','>=',date('Y-m-d'))->update(['relation_id'=>$id]);
    }
    /**
     * 削除時に案件共有者を同時に削除する
     * @param $userIds
     * @param $groupArr
     * @param $flag
     */
    public function deleteProjectParticipants($userIds,$groupArr,$flag = 0)
    {
        $projectIds = Project::whereIn('group_id',$groupArr)->get(['id','group_id'])->toArray();
        try {
            $projectIdArr = [];
            $groupIdArr = [];
            foreach ($projectIds as $item){
                $projectIdArr[] = $item['id'];
                $groupIdArr[] = $item['group_id'];
            }
            $groups = Group::whereIn('parent_id',$groupIdArr)->pluck('id')->toArray();
            $groupIdArr = array_merge($groupIdArr,$groups);
            if($flag){
                ProjectParticipant::where('user_id', Auth::id())->whereIn('project_id', $projectIdArr)->delete();
                ChatGroup::whereIn('group_id', $groupIdArr)->where('user_id',Auth::id())->delete();
                ChatList::whereIn('group_id', $groupIdArr)->where('user_id',Auth::id())->delete();
                $chatArr = ChatGroup::whereHas('user', function ($q) use ($userIds) {
                    $q->whereIn('id', $userIds);//社内
                })->whereHas('mine', function ($q) {
                    $q->where('user_id', Auth::id())->where('admin', 1);//職人
                })->whereHas('group', function ($q) {
                    $q->where('kind', 0);// 0=グループチャット
                })->pluck('group_id')->toArray();
                ChatGroup::whereIn('group_id', $chatArr)->whereIn('user_id',$userIds)->delete();
                ChatList::whereIn('group_id', $chatArr)->whereIn('user_id',$userIds)->delete();
            }else{
                ProjectParticipant::whereIn('user_id', $userIds)->whereIn('project_id', $projectIdArr)->delete();
            }
        } catch (\PDOException $e) {
            //データベースエラー
            Log::error($e);
        }
    }

    /**
     * 仲間招待メイルを送信機能
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendEmail(Request $request)
    {
        $toEmail = $request->input('toEmail');
        $message = $request->input('message');
        $nameSearch = $request->input('nameSearch');
        $count = 0;
        try {
            DB::beginTransaction();
            $users = User::query()->whereIn('email', $toEmail)
                ->where(function ($q) {
                    if (Auth::user()->enterprise_id) {
                        $q->where('enterprise_id', '!=', Auth::user()->enterprise_id);
                        $q->orWhereNull('enterprise_id');
                    } else {
                        $q->where('coop_enterprise_id', '!=', Auth::user()->coop_enterprise_id);
                        $q->orWhereNull('coop_enterprise_id');
                    }
                })->where('id', '!=', Auth::id())
                ->get();
            foreach ($users as $user) {
                //職人（USERテーブルにWORKER=1）の場合
                //userテーブル,worker=1は職人です、worker=0は職人以外です
                $friend = new ChatContact();
                $friend->from_user_id = Auth::id();
                $friend->to_user_id = $user->id;
                $friend->email = $user->email;
                $friend->contact_date = date('Y-m-d H:i:s', time());
                $friend->contact_message = $message;
                $friend->contact_agree = 0;
                if ($nameSearch){
                    $friend->append_status = 0;
                }else{
                    $friend->append_status = 1;
                }
                $friend->save();

                //仲間招待メール送信する
                // $url = url('/', null, true) . '/invitation/' . Auth::id() . '/chat';
                $url = ApiHelper::getInvitationUrl(Auth::id());
                Mail::to($user->email)->send(new ContactInvite(Auth::user()->name, $message, $url));
                $fireBase = new FirebaseService();
                $fireBase->pushAddChatFriend(Auth::user(), $user->id);
                if (Mail::failures()) {
                    DB::rollBack();
                    return $this->error('email send error', [trans('messages.error.email')]);
                } else {
                    $count++;
                }

                //return $this->json();
            }
            $emailArr = [];
            $userArr = User::query()->whereIn('email', $toEmail)->get();
            foreach ($userArr as $item) {
                $emailArr[] = $item->email;
            }
            foreach ($toEmail as $eachToEmail) {
                if (!in_array($eachToEmail, $emailArr)) {
                    //ユーザーテーブルにメールアドレスが存在しない場合（現行アプリの招待のまま）
                    $friend = new ChatContact();
                    $friend->from_user_id = Auth::id();
                    $friend->to_user_id = '0';
                    $friend->email = $eachToEmail;
                    $friend->contact_date = date('Y-m-d H:i:s', time());
                    $friend->contact_message = $message;
                    $friend->contact_agree = 0;
                    if ($nameSearch){
                        $friend->append_status = 0;
                    }else{
                        $friend->append_status = 1;
                    }
                    $friend->save();

                    //アプリをダウンロードのURLと招待URLを送る
                    // $url = url('/', null, true) . '/invitation/' . Auth::id() . '/chat';
                    $url = ApiHelper::getInvitationUrl(Auth::id());
                    $downloadUrl = url('/', null, true) . '/api/downloadFile?path=/apk/app-ChatAppli.apk';
                    Mail::to($eachToEmail)->send(new ContactFriend(Auth::user()->name, $message, $url, $downloadUrl));
                    if (Mail::failures()) {
                        DB::rollBack();
                        return $this->error('email send error', [trans('messages.error.email')]);
                    }else{
                        $count++;
                    }
                    //return $this->json();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            return $this->error($e, [trans('messages.error.email')]);
        }
        return $this->json(null,$count);
    }

    /**
     * ユーザーメイル check
     * 職人メイル check
     * ユーザーテーブルにメールアドレスが存在しない check
     * @param Request $request
     * @return string
     */
    public function checkEmail(Request $request)
    {
        $checkEmail = $request->input('checkEmail');
        $user = User::query()->where('email', $checkEmail)->first();
        $item = User::query()->where('email', $checkEmail)
            ->where(function ($q) {
                if (Auth::user()->enterprise_id) {
                    $q->where('enterprise_id', Auth::user()->enterprise_id);
                } else {
                    if (Auth::user()->coop_enterprise_id) {
                        $q->where('coop_enterprise_id', Auth::user()->coop_enterprise_id);
                    }
                }
            })
            ->first();
        if (!empty($user)) {
            $account = User::where('enterprise_id',Auth::user()->enterprise_id)->withTrashed()->get('id')->toArray();
            $userRes=[];
            foreach ($account as $itemTmp){
                $userRes[] = $itemTmp['id'];
            }
            $friend = ChatContact::query()->where('email', $checkEmail)->whereIn('from_user_id', $userRes)->first();
            if (!empty($friend)) {
                return 'already';
            }else if (!empty($item)) {
                return 'enterprise';
            } else {
                return 'shokunin';
            }
        } else {
            if (!empty($item)) {
                return 'enterprise';
            } else {
                return 'shokunin';
            }
        }
    }

    /**
     * 仲間一覧チャットリンク
     * @param Request $request
     * @return mixed
     */
    public function friendChatLink(Request $request)
    {
        $friendId = $request->input('id');
        $chatGroup = ChatGroup::query()->where('user_id', $friendId)
            ->where('admin', 1)->get();
        $chatGroupAuth = ChatGroup::query()->where('user_id', Auth::id())
            ->where('admin', 1)->get();
        //チャットリンクを取得
        $groupIdsTempArr = [];
        foreach ($chatGroup as $item) {
            foreach ($chatGroupAuth as $itemAuth) {
                if ($item->group_id == $itemAuth->group_id) {
                    $groupIdsTempArr[] = $item->group_id;
                }
            }
        }

        //1 vs 1 chatgroup filter
        $groupModels = DB::table('groups')->whereIn('id', $groupIdsTempArr)
            ->where('kind', 1)->pluck('id');
        if (count($groupModels) > 0) {
            $arr = array("user_id" => $friendId, "group_id" => $groupModels[0]);
            return $arr;
        } else {
            //新規の場合
            return $this->createNewGroup($friendId);
        }
    }

    /**
     * create new group chat
     *
     * @param $inviteUserId
     *
     * @return array
     */
    private function createNewGroup($inviteUserId){
        try {
            DB::beginTransaction();

            $group = new Group();
            $group->name = Auth::user()->name;
            $group->kind = 1;
            $group->save();

            $chatMineGroup = new ChatGroup();
            $chatMineGroup->user_id = $inviteUserId;
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->admin = 1;
            $chatMineGroup->save();

            $chatMineGroup = new ChatGroup();
            $chatMineGroup->user_id = Auth::id();
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->admin = 1;
            $chatMineGroup->save();

            $chatMineGroup = new ChatList();
            $chatMineGroup->owner_id = Auth::id();
            $chatMineGroup->user_id = $inviteUserId;
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->save();

            $chatMineGroup = new ChatList();
            $chatMineGroup->owner_id = $inviteUserId;
            $chatMineGroup->user_id = Auth::id();
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->save();
            DB::commit();

            $arr = array("user_id" => $inviteUserId, "group_id" => $group->id);
            return $arr;
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            Log::error($e);
        }
    }

    /**
     * 仲間を並べ替え
     * @param Request $request
     * @return array|string|null
     */
    public function detailSearch(Request $request)
    {
        $searchArray = $request->input('searchName');
        $sort = $request->get('sort');
        $order = $request->get('order');
        if ($sort == 'name') {
            foreach ($searchArray as $item) {
                $paytime[] = $item['accounts']['name'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, SORT_LOCALE_STRING ,$searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, SORT_LOCALE_STRING ,$searchArray);
            }
        } elseif ($sort == 'date') {
            foreach ($searchArray as $item) {
                $paytime[] = $item['contact_date'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, SORT_LOCALE_STRING ,$searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, SORT_LOCALE_STRING ,$searchArray);
            }
        } elseif ($sort == 'company_name') {
            foreach ($searchArray as $item) {
                $paytime[] = $item['accounts']['company_name'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, SORT_LOCALE_STRING ,$searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, SORT_LOCALE_STRING ,$searchArray);
            }
        }
        return $searchArray;
    }
}
