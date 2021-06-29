<?php
/**
 * 契約者
 */

namespace App\Http\Controllers\Admin;

use App\Http\Facades\Common;
use App\Models\Account;
use App\Models\ChatGroup;
use App\Models\ChatLastRead;
use App\Models\ChatList;
use App\Models\ChatMessage;
use App\Models\ChatTask;
use App\Models\ChatTaskCharge;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Enterprise;
use App\Models\EnterpriseIntelligence;
use App\Models\EnterprisePerson;
use App\Models\Group;
use App\Models\Operator;
use App\Models\ProjectParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EnterpriseParticipant;
use App\Models\ChatContact;
use App\Models\Project;
use App\Models\Browse;
use App\Models\ProjectCustomer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserStorage;
use App\Http\Controllers\Web\ChatController;

class ContractController extends \App\Http\Controllers\Controller
{
    public function getList()
    {
        $model = Enterprise::with('user')->where('cooperator',0)->get();
        return $model;
    }

    public function searchContract(Request $request)
    {
        $keyword = Common::escapeDBSelectKeyword($request->post('keyword'));
        $model = Enterprise::with('user')
            ->where('name', 'LIKE', "%{$keyword}%")
            ->where('cooperator',0)->get();
        return $model;
    }

    public function getContractDetail(Request $request)
    {
        $enterpriseId = $request->get('enterpriseId');

        $bossId = Enterprise::where('id',$enterpriseId)->pluck('user_id');

        $enterpriseUser = User::where('id',$bossId)->first();

        $enterpriseModel = Enterprise::where('id',$enterpriseId)
            ->with('contractor')->first();

        //契約者
        $contractorModel = Contractor::where('enterprise_id',$enterpriseId)->first();

        return ['enterprise' => $enterpriseModel,'contractor' => $contractorModel,'enterpriseUser' => $enterpriseUser];
    }

    public function updateContractDetail(Request $request)
    {
        $enterpriseId = $request->get('enterpriseId');
        $contractorArr = $request->get('contractor');
        $enterpriseArr = $request->get('contractEnterprise');

        DB::beginTransaction();
        try{
            $contractor = Contractor::where('enterprise_id',$enterpriseId)->first();
            if ($contractor) {
                $contractor->name = $contractorArr['name'];
                $contractor->zip = $contractorArr['zip'];
                $contractor->pref = $contractorArr['pref'];
                $contractor->town = $contractorArr['town'];
                $contractor->street = $contractorArr['street'];
                $contractor->house = $contractorArr['house'];
                $contractor->tel = $contractorArr['tel'];
                $contractor->people = $contractorArr['people'];
                $contractor->remark = $contractorArr['remark'];
                $contractor->save();
            } else {
                $contractor = new Contractor();
                $contractor->name = $contractorArr['name'];
                $contractor->zip = $contractorArr['zip'];
                $contractor->pref = $contractorArr['pref'];
                $contractor->town = $contractorArr['town'];
                $contractor->street = $contractorArr['street'];
                $contractor->house = $contractorArr['house'];
                $contractor->tel = $contractorArr['tel'];
                $contractor->enterprise_id = $enterpriseId;
                $contractor->people = $contractorArr['people'];
                $contractor->remark = $contractorArr['remark'];
                $contractor->save();
            }


            $enterprise = Enterprise::where('id',$enterpriseId)->first();
            $enterprise->name = $enterpriseArr['name'];
            $enterprise->zip = $enterpriseArr['zip'];
            $enterprise->pref = $enterpriseArr['pref'];
            $enterprise->town = $enterpriseArr['town'];
            $enterprise->street = $enterpriseArr['street'];
            $enterprise->house = $enterpriseArr['house'];
            $enterprise->tel = $enterpriseArr['tel'];
            if ($enterpriseArr['plan'] == '有料プラン'){
                $enterprise->plan = 1;
            } elseif($enterpriseArr['plan'] == '有料プラン（改定前）') {
                $enterprise->plan = 2;
            } elseif($enterpriseArr['plan'] == '無料トライアル') {
                $enterprise->plan = 3;
            } elseif($enterpriseArr['plan'] == '永年無料プラン') {
                $enterprise->plan = 4;
            } elseif($enterpriseArr['plan'] == 1 || $enterpriseArr['plan'] == 2
                || $enterpriseArr['plan'] == 3 || $enterpriseArr['plan'] == 4) {
                $enterprise->plan = $enterpriseArr['plan'];
            }

            $enterprise->amount = $enterpriseArr['amount'];
            $enterprise->storage = $enterpriseArr['storage'];
            $enterprise->save();


            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            Log::error($e);
            //データベースエラー
            return $this->error($e);
        }

    }

    public function getOperatorUsers(Request $request){
        $enterpriseIdRequest = $request->post('enterpriseId');

        $enterpriseModel = Enterprise::where('id',$enterpriseIdRequest)->first();
        $enterpriseId = null;
        $coopEnterpriseId = null;
        if ($enterpriseModel->cooperator){
            $coopEnterpriseId = $enterpriseIdRequest;
        } else {
            $enterpriseId = $enterpriseIdRequest;
        }

        $operators = Operator::with('user')->get()->toArray();

        //目的会社の职人取得について
        $friendsArr = $this->getFriendList($enterpriseId,$coopEnterpriseId,$enterpriseModel->user_id);

        //既存の運営を排除
        foreach ($operators as $key => $value){
            if(!$value['user']) { // if user has been deleted,remove it from operators
                unset($operators[$key]);
            } else {
                foreach ($friendsArr as $item){
                    if($value['user_id'] == $item['id']){
                        unset($operators[$key]);
                    }
                }
            }
        }
        $operators=array_values($operators);
        return $operators;
    }
    public function contractAccount(){
        $enterpriseId=request('enterpriseId');
        $res=DB::table('enterprises')->where('enterprises.id',$enterpriseId)->first();
        return response()->json($res);
    }

    //アカウント数消費
    public function getOffice(){
        $enterpriseId=request('enterpriseId');
        $model=User::where('enterprise_id',$enterpriseId)->orderBy('enterprise_admin','desc')->get()->toArray();
        $InviteCount=$this->InviteCount($enterpriseId);
        $enterpriseAmount = Enterprise::where('id',$enterpriseId)->pluck('amount')->first();

        $usersArr = User::where('enterprise_id', $enterpriseId)->withTrashed()->get('id');
        $res = [];
        foreach ($usersArr as $user) {
            $res[] = $user['id'];
        }
        $models = ChatContact::whereIn('from_user_id', $res)
            ->where('contact_agree',1)->whereNotIn('to_user_id',$res)->pluck('to_user_id')->toArray();

        foreach ($model as $key => $value){
            $value['invitecount']=$InviteCount;
            $num=$this->FriendCount($value['id']);
            $val=array_unique(array_merge($models,$num));
            $value['FriendCount']=count($val);
            $model[$key]=$value;
        }
        return ['user'=>$model,'enterpriseAmount'=>$enterpriseAmount,'InviteCount'=>$InviteCount];
    }

    //関係者数
    private function InviteCount($enterprise_id)
    {
        $res = User::where('enterprise_id', $enterprise_id)->withTrashed()->pluck('id')->toArray();
        $user = User::onlyTrashed()->pluck('id')->toArray();

        $model = EnterpriseParticipant::where('enterprise_id',$enterprise_id)
            ->where('agree',1)->whereNotIn('user_id',$res)->whereNotIn('user_id',$user)->count();
        return $model;
    }
    //仲間数
    private function FriendCount($user_id)
    {
        //仲間招待者取得
        $usersArr = User::where('enterprise_id', Auth::user()->enterprise_id)->withTrashed()->get('id');
        $modelInvite = ChatContact::where('to_user_id', $user_id)
        ->where('contact_agree',1)->whereNotIn('from_user_id',$usersArr)->pluck('from_user_id')->toArray();
        return $modelInvite;
    }
    //ログイン履歴
    public function history(){
        $enterpriseId=request('enterpriseId');
        $model=User::where('enterprise_id',$enterpriseId)->orderBy('enterprise_admin','desc')->get()->toArray();
        return $model;
    }
    //案件の注目頻度
    public function projectCount(){
        $enterprise_id=request('enterpriseId');
        $model=Project::where('enterprise_id',$enterprise_id)->get()->toArray();
        $allCount=count($model);
        $monthCount=0;
        $theDay=time();
        $thirtyDay = strtotime("-30 day");
        $progress_status=[];
        for ($i=1;$i<=8;$i++){
            $progress_status[$i]=0;
        }
        foreach ($model as $key => $value) {
            $value['time']=strtotime($value['st_date']);
            if($value['time']<=$theDay&&$value['time']>$thirtyDay){
                $monthCount+=1;
            }
            if($value['progress_status']){
                $progress_status[$value['progress_status']]+=1;
            }
        }
        $DayCount=sprintf("%.2f",$monthCount/30);
        $data=['allCount'=>$allCount,'monthCount'=>$monthCount,'dayCount'=>$DayCount,'res'=>$model,'progress_status'=>$progress_status];
        return $data;
    }
    //各機能使用頻度
    public function getBrowse(){
        $enterprise_id=request('enterpriseId');
        $model=Browse::where('enterprise_id',$enterprise_id)->get(['schedule','project','chat','friend','invite','customer','profile','account'])->toArray();
        $data=0;
        $res=[];
        if(count($model) > 0){
            $res=array_values($model[0]);
            $data=array_sum($res);
        }
        for ($i=0;$i<10;$i++){
            if(count($res)>$i){
                $progress_status[$i]=round($res[$i]/$data*100,0);
            }else{
                $progress_status[$i]=0;
            }
        }
        $data=['progress_status'=>$progress_status];
        return $data;
    }
    //データ容量状況
    public function  contractContain(){
        $enterprise_id=request('enterpriseId');
        //合計
        $doc_storage=0;//ドキュメント容量
        $chat_storage=0;//チャット送信容量
        $chat_file_storage=0;//	チャット添付容量
        $model=User::where('enterprise_id',$enterprise_id)->orderBy('enterprise_admin','desc')->get()->toArray();
        foreach ($model as $key =>$value){
            $value['storage']['doc_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$value['id'])->where('type',1)->sum('doc_storage');
            $value['storage']['chat_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$value['id'])->where('type',1)->sum('chat_storage');
            $value['storage']['chat_file_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$value['id'])->where('type',1)->sum('chat_file_storage');
            $doc_storage+=$value['storage']['doc_storage'];
            $chat_storage+=$value['storage']['chat_storage'];
            $chat_file_storage+=$value['storage']['chat_file_storage'];
            $model[$key]=$value;
        }
        //削除済みユーザ使用分
        $delStorage=[];
        $deleteUser=User::where('enterprise_id',$enterprise_id)->onlyTrashed()->pluck('id')->toArray();
        $delStorage['doc_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->whereIn('user_id',$deleteUser)->where('type',1)->sum('doc_storage');
        $delStorage['chat_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->whereIn('user_id',$deleteUser)->where('type',1)->sum('chat_storage');
        $delStorage['chat_file_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->whereIn('user_id',$deleteUser)->where('type',1)->sum('chat_file_storage');

        //協力会社
        $inviteUser=EnterpriseParticipant::where('enterprise_id',$enterprise_id)->where('agree', 1)
            ->pluck('user_id')->toArray();
        //協力会社の職人
        $inviteUserContract=ChatContact::whereIn('from_user_id',$inviteUser)->where('contact_agree', 1)->pluck('to_user_id')->toArray();
        $inviteUser=array_merge($inviteUser,$inviteUserContract);

        //職人
        $allUser=User::where('enterprise_id',$enterprise_id)->withTrashed()->pluck('id')->toArray();
        $chatContactUser=ChatContact::whereIn('from_user_id',$allUser)->where('contact_agree', 1)->pluck('to_user_id')->toArray();
        //2,協力会社 3,職人 4,案件
        $invite=UserStorage::where('type',2)
            ->where('other_enterprise_id',$enterprise_id)->groupBy('user_id')
            ->pluck('user_id')->toArray();
        $friend=UserStorage::where('type',3)
            ->where('other_enterprise_id',$enterprise_id)->groupBy('user_id')
            ->pluck('user_id')->toArray();
        $project=UserStorage::where('type',4)
            ->where('other_enterprise_id',$enterprise_id)->groupBy('project_id')
            ->pluck('project_id')->toArray();
        $project=array_unique($project);
        foreach ($invite as $key =>$value) {
            $inviteItem=DB::table('users')->where('id',$value)->first();
            $inviteItem->storage['doc_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$inviteItem->id)->where('type',2)->sum('doc_storage');
            $inviteItem->storage['chat_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$inviteItem->id)->where('type',2)->sum('chat_storage');
            $inviteItem->storage['chat_file_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$inviteItem->id)->where('type',2)->sum('chat_file_storage');
            $doc_storage+=$inviteItem->storage['doc_storage'];
            $chat_storage+=$inviteItem->storage['chat_storage'];
            $chat_file_storage+=$inviteItem->storage['chat_file_storage'];
            if(in_array($inviteItem->id,$inviteUser)){
                $inviteItem->relation=1;
            }else{
                $inviteItem->relation=0;
            }
            $invite[$key]=$inviteItem;
        }
        foreach ($friend as $key =>$value) {
            $friendItem=DB::table('users')->where('id',$value)->first();
            $friendItem->storage['doc_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$friendItem->id)->where('type',3)->sum('doc_storage');
            $friendItem->storage['chat_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$friendItem->id)->where('type',3)->sum('chat_storage');
            $friendItem->storage['chat_file_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('user_id',$friendItem->id)->where('type',3)->sum('chat_file_storage');
            $doc_storage+=$friendItem->storage['doc_storage'];
            $chat_storage+=$friendItem->storage['chat_storage'];
            $chat_file_storage+=$friendItem->storage['chat_file_storage'];
            if(in_array($friendItem->id,$chatContactUser)){
                $friendItem->relation=1;
            }else{
                $friendItem->relation=0;
            }
            $friend[$key]=$friendItem;
        }

        foreach ($project as $key =>$value) {
            $projectItem=DB::table('projects')->where('id',$value)->first();
            $projectItem->storage['doc_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('project_id',$projectItem->id)->where('type',4)->sum('doc_storage');
            $projectItem->storage['chat_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('project_id',$projectItem->id)->where('type',4)->sum('chat_storage');
            $projectItem->storage['chat_file_storage']=UserStorage::where('other_enterprise_id',$enterprise_id)->where('project_id',$projectItem->id)->where('type',4)->sum('chat_file_storage');
            $doc_storage+=$projectItem->storage['doc_storage'];
            $chat_storage+=$projectItem->storage['chat_storage'];
            $chat_file_storage+=$projectItem->storage['chat_file_storage'];
            $project[$key]=$projectItem;
        }
        $doc_storage+=$delStorage['doc_storage'];
        $chat_storage+=$delStorage['chat_storage'];
        $chat_file_storage+=$delStorage['chat_file_storage'];
        $data=['res'=>$model,'doc_storage'=>$doc_storage,'chat_storage'=>$chat_storage,'chat_file_storage'=>$chat_file_storage,
            'invite'=>$invite,'friend'=>$friend,'project'=>$project,'delStorage'=>$delStorage];
        return $data;
    }
    public function  enterpriseData(){
        $res=Auth::user();
        return $res;
    }

    /**
     * $fromUserId  会社担当者 user_id
     * $toUserId    運営 user_id
     * @param Request $request
     */
    public function addContractFriend(Request $request)
    {

        $userIdsArr = $request->post('friendUserIds');
        $enterpriseId = $request->post('enterpriseId');

        $enterpriseModel = Enterprise::where('id',$enterpriseId)->first();
        $enterpriseUserId = $enterpriseModel->user_id;  //from_user_id

        $userModels = User::whereIn('id',$userIdsArr)->get();
        DB::beginTransaction();
        try {
            foreach ($userModels as $user) {
                $friend = new ChatContact();
                $friend->from_user_id = $enterpriseUserId;
                $friend->to_user_id = $user->id;
                $friend->email = $user->email;
                $friend->contact_date = date('Y-m-d H:i:s', time());
//            $friend->contact_message = $message;
                $friend->contact_agree = 1;
                $friend->append_status = 1;
                $friend->save();

                $this->setAgreement($enterpriseUserId, $user->id, $enterpriseId);
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            Log::error($e);
        }

    }

    /**
     * 未承認ユーザーを承認する
     *
     * $fromUserId  会社担当者 user_id
     * $toUserId    運営 user_id
     * @return unknown
     */
    private function setAgreement($fromUserId, $toUserId,$fromUserEnterpriseId)
    {
        $toUserModel = User::where('id',$toUserId)->first();

        DB::beginTransaction();
        try {

            $enterpriseUsers = Account::
            where('enterprise_id', $fromUserEnterpriseId)
                ->orWhere('coop_enterprise_id',$fromUserEnterpriseId)
                ->get();
            $data = DB::table('chatgroups')->where('user_id', $toUserId)
                ->get()->toArray();
            foreach ($data as $key => $value) {
                $value->people = DB::table('chatgroups')
                    ->where('group_id', $value->group_id)->pluck('user_id')
                    ->toArray();
                $data[$key] = $value;
            }
            foreach ($enterpriseUsers as $key => $value) {
                $insert = false;
                foreach ($data as $k => $val) {
                    if (in_array($value->id, $val->people)
                        && count($val->people) == 2
                    ) {
                        $insert = true;
                    }
                }
                if (!$insert) {
                    $group = new Group();
                    $group->name = $toUserModel->name;
                    $group->kind = 1;
                    $group->save();

                    $chatMineGroup = new ChatGroup();
                    $chatMineGroup->user_id = $value->id;
                    $chatMineGroup->group_id = $group->id;
                    $chatMineGroup->admin = 1;
                    $chatMineGroup->save();

                    $chatMineGroup = new ChatGroup();
                    $chatMineGroup->user_id = $toUserId;
                    $chatMineGroup->group_id = $group->id;
                    $chatMineGroup->admin = 1;
                    $chatMineGroup->save();

                    $chatMineGroup = new ChatList();
                    $chatMineGroup->owner_id = $toUserId;
                    $chatMineGroup->user_id = $value->id;
                    $chatMineGroup->group_id = $group->id;
                    $chatMineGroup->save();

                    $chatMineGroup = new ChatList();
                    $chatMineGroup->owner_id = $value->id;
                    $chatMineGroup->user_id = $toUserId;
                    $chatMineGroup->group_id = $group->id;
                    $chatMineGroup->save();
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            Log::error($e);
        }
    }

    /**
     * 仲間 一覧
     * @param Request $request
     * @return array
     */
    private function getFriendList($enterpriseId,$coopEnterpriseId,$enterpriseUserId)
    {
        $usersArr = [];
        if ($enterpriseId){
            $usersArr = User::where('enterprise_id', $enterpriseId)->withTrashed()->get('id');
        } elseif ($coopEnterpriseId) {
            $usersArr = User::where('coop_enterprise_id', $coopEnterpriseId)->withTrashed()->get('id');
        }
        $res = [];
        foreach ($usersArr as $user) {
            $res[] = $user['id'];
        }
        $toUser = ChatContact::whereIn('from_user_id', $res)->where('contact_agree', 1)
            ->pluck('to_user_id')->toArray();
        $usersIdArray = $toUser;


        $friends = User::whereIn('id', $usersIdArray)->where('id', '!=', $enterpriseUserId)->get()->toArray();

        $friends=array_values($friends);

        return $friends;
    }

    /**
     * 運営-契約者一括削除
     * @param Request $request
     */
    public function deleteContract(Request $request){

        $enterpriseId = $request->post('enterpriseId');
        $enterpriseModel = Enterprise::where('id',$enterpriseId)->first();
        if ($enterpriseModel->cooperator) {
            //協力会社
            $userIdsArr = User::where('coop_enterprise_id',$enterpriseId)->whereNotNull('coop_enterprise_id')->pluck('id')->toArray();
        } else {
            //会社
            $userIdsArr = User::where('enterprise_id',$enterpriseId)->whereNotNull('enterprise_id')->pluck('id')->toArray();
        }
        DB::beginTransaction();
        try {

            //project
            $projectIds = Project::where('enterprise_id',$enterpriseId)->pluck('id')->toArray();
            ProjectParticipant::whereIn('project_id',$projectIds)->delete();
            Project::where('enterprise_id',$enterpriseId)->delete();
            ProjectCustomer::whereIn('project_id',$projectIds)->delete();

            //customer
            Customer::where('enterprise_id',$enterpriseId)->delete();

            //会社情報 削除
            $enterpriseIntelligenceIds = EnterpriseIntelligence::where('enterprise_id',$enterpriseId)->pluck('id')->toArray();
            foreach ($enterpriseIntelligenceIds as $value){
                EnterprisePerson::where('enterprise_intelligences_id', '=', $value)->delete();
            }

            //task 削除
            $chatTaskIds = ChatTask::whereIn('create_user_id',$userIdsArr)->pluck('id')->toArray();
            ChatTask::whereIn('create_user_id',$userIdsArr)->delete();
            ChatTaskCharge::whereIn('task_id',$chatTaskIds)->orWhereIn('user_id',$userIdsArr)->delete();

            //enterprise
            EnterpriseParticipant::where('enterprise_id',$enterpriseId)->delete();

            //friend
            ChatContact::whereIn('from_user_id',$userIdsArr)->orWhereIn('to_user_id',$userIdsArr)->delete();

            $this->deleteAccount($userIdsArr,$enterpriseId);

            Enterprise::where('id',$enterpriseId)->delete();

            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            Log::error($e);
        }


    }

    /**
     * アカウント管理 削除
     * @param Request $request
     * @return string
     */
    private function deleteAccount($userIdsArr,$enterpriseId)
    {
        $ids = $userIdsArr;
        try {
            foreach ($ids as $k => $v) {
                //user table softDeletes
                Account::query()->where('id', $v)->delete();
                //各ユーザの削除はループ処理される
                $this->del_group($v);
            }
            //スケジュールから削除
            $user_arr=DB::table('users')->where('enterprise_id',$enterpriseId)->orWhere('coop_enterprise_id',$enterpriseId)->pluck('id')->toArray();

            $scheduleIds = DB::table('schedules')->whereIn('created_user_id',$user_arr)->pluck('id')->toArray();
            DB::table('schedulesubs')->whereIn('relation_id',$scheduleIds)->delete();
            DB::table('scheduleparticipants')->whereIn('schedule_id',$scheduleIds)->delete();
            DB::table('scheduleparticipants')->whereIn('user_id',$user_arr)->delete();
            DB::table('schedules')->whereIn('created_user_id',$user_arr)->delete();

        } catch (\PDOException $e) {
            //データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, $error);
        }
        return $this->json();
    }

    private function del_group($user_id)
    {
        //そのユーザのすべてのグループを見つける
        $groups = ChatGroup::where('user_id', $user_id)->get(['group_id']);
        $groups_arr = $groups->toArray();
        //すべてのグループを見つけてkindを判定する
        foreach ($groups_arr as $k => $v) {
            $kind = $this->get_kind($v['group_id']);
            if (!$kind[0]['kind']) {
                $this->del_group_project($v['group_id'], $user_id);
            } else {
                $this->del_group_contact($v['group_id']);
            }
        }
    }

    private function get_kind($group_id)
    {
        $group_info = Group::where('id', $group_id)->get(['kind']);
        return $group_info->toArray();
    }

    private function del_group_project($group_id, $user_id)
    {
        //解散グループ 容量を増やす
        $group=Group::where('id', $group_id)->first();
        $chat = new ChatController();
        $chat->deleteContain($group);
        //groupとuser_idとを含む対応を削除する
        ChatGroup::where('group_id', $group_id)->where('user_id', $user_id)->delete();
        ChatList::where('group_id', $group_id)->delete();
        ChatMessage::where('group_id', $group_id)->delete();
        ChatLastRead::where('group_id', $group_id)->delete();
        //groupを削除する
        Group::destroy($group_id);
    }

    private function del_group_contact($group_id)
    {
        //解散グループ 容量を増やす
        $group=Group::where('id', $group_id)->first();
        $chat = new ChatController();
        $chat->deleteContain($group);
        //相対データを2つ削除する
        ChatGroup::where('group_id', $group_id)->delete();
        ChatList::where('group_id', $group_id)->delete();
        ChatMessage::where('group_id', $group_id)->delete();
        ChatLastRead::where('group_id', $group_id)->delete();
        //groupを削除する
        Group::destroy($group_id);
    }

    public function addContractEnterprise(Request $request)
    {
        $enterpriseRequest = $request->post('enterprise');
        try {
            DB::beginTransaction();

            $user = new Account();
            $user->name = $enterpriseRequest['userLastName'] . $enterpriseRequest['userFirstName'];
            $user->last_name = $enterpriseRequest['userLastName'];
            $user->first_name = $enterpriseRequest['userFirstName'];
            $user->email = $enterpriseRequest['userEmail'];
            $user->enterprise_admin = "1";
            $user->worker = "0";
            $user->password = bcrypt($enterpriseRequest['userPassword']);
            $user->save();

            $enterprise = new Enterprise();
            $enterprise->name = $enterpriseRequest['name'];
            $enterprise->user_id = $user->id;
            $enterprise->zip = $enterpriseRequest['zip'];
            $enterprise->pref = $enterpriseRequest['pref'];
            $enterprise->town = $enterpriseRequest['town'];
            $enterprise->street = $enterpriseRequest['street'];
            $enterprise->house = $enterpriseRequest['house'];
            $enterprise->tel = $enterpriseRequest['tel'];
            $enterprise->plan = $enterpriseRequest['plan'];
            $enterprise->amount = $enterpriseRequest['amount'];
            //データ容量の初期表示は契約してるユーザライセンス数の上限×10GB
            $enterprise->storage = $enterpriseRequest['amount']*10;
            $enterprise->save();

            $user->enterprise_id = $enterprise->id;
            $user->save();

            //契約情報
            $contractor = new Contractor();
            $contractor->name = $enterpriseRequest['name'];
            $contractor->zip = $enterpriseRequest['zip'];
            $contractor->pref = $enterpriseRequest['pref'];
            $contractor->town = $enterpriseRequest['town'];
            $contractor->street = $enterpriseRequest['street'];
            $contractor->house = $enterpriseRequest['house'];
            $contractor->tel = $enterpriseRequest['tel'];
            $contractor->enterprise_id = $enterprise->id;
            $contractor->people = $enterpriseRequest['userLastName'] . $enterpriseRequest['userFirstName'];
            $contractor->save();

            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            Log::error($e);
            //データベースエラー
            return $this->error($e);
        }

    }
    /**
     * CSV出力機能の追加
     */
    public function contractCsv(){
        $sql=<<<EOF
SELECT users.id, users.name, users.first_name, users.last_name, users.email, e1.name as enterprise_name, 
e1.zip , e1.pref , e1.town ,e1.street, e1.house, e1.tel, e2.name as invite_name , e2.zip as invite_zip ,
 e2.pref as invite_pref, e2.town as invite_town, e2.street as invite_street,e2.house as invite_house, e2.tel as invite_tel, 
 users.created_at, c.chatcount, s.schedulecount
FROM users
    LEFT OUTER JOIN enterprises AS e1 ON users.enterprise_id = e1.id
    LEFT OUTER JOIN enterprises AS e2 ON users.coop_enterprise_id = e2.id
    LEFT OUTER JOIN (
 SELECT from_user_id, COUNT(*) AS chatcount
    FROM chatmessages
    GROUP BY from_user_id) AS c ON users.id = c.from_user_id
    LEFT OUTER JOIN (
 SELECT created_user_id, COUNT(*) AS schedulecount
    FROM schedules
    GROUP BY created_user_id) AS s ON users.id = s.created_user_id
WHERE users.deleted_at IS NULL AND enterprise_admin IS NOT NULL AND
    NOT EXISTS (SELECT *from developers WHERE users.id = developers.user_id);
EOF;
        $data = DB::select($sql);
        return $this->exportCsv($data);
    }

    /**
     * CSVファイルのエクスポート
     */
    private function exportCsv($data)
    {
        $data=json_decode(json_encode($data),true);
        $date=date('YmdHis');
        //using UTF-8
        $filename = '契約者一覧_'.$date.'.csv';
        //題名 改行なし
        $fileData = 'ユーザID,ユーザ名,名,姓,メールアドレス,会社名,会社郵便番号,会社都道府県,会社市区町村,会社番地,会社建物名,会社電話番号,協力会社名,協力会社郵便番号,協力会社都道府県,協力会社市区町村,協力会社番地,協力会社建物名,協力会社電話番号,アカウント登録日時,チャット送信数,スケジュール作成数' . "\n";
        //文字列へのデータスプライシング
        foreach ($data as $value) {
            $temp = $value['id'] . ',' . $value['name']. ',' . $value['first_name'].
                ',' . $value['last_name']. ',' . $value['email']. ',' . $value['enterprise_name'].
                ',' . $value['zip']. ',' . $value['pref']. ',' . $value['town'].
                ',' . $value['street']. ',' . $value['house']. ',' . $value['tel'].
                ',' . $value['invite_name']. ',' . $value['invite_zip']. ',' . $value['invite_pref'].
                ',' . $value['invite_town']. ',' . $value['invite_street']. ',' . $value['invite_house'].
                ',' . $value['invite_tel']. ',' . $value['created_at']. ',' . $value['chatcount']. ',' . $value['schedulecount'];
            $fileData .= $temp . "\n";
        }
        header('Content-Encoding:UTF-8');
        header('Content-Type:text/csv;charset=UTF-8');
        header('Content-Disposition:attachment;filename=' . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo "\xEF\xBB\xBF";
        echo $fileData;
        exit;
    }

}
