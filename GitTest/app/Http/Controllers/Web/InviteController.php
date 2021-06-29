<?php
/**
 * 協力会社招待
 * @author ZhouChuanhui
 */

namespace App\Http\Controllers\Web;


use App\Http\Facades\Common;
use App\Mail\ContactInvite;
use App\Models\ChatContact;
use App\Models\ChatGroup;
use App\Models\ChatLastRead;
use App\Models\ChatList;
use App\Models\ChatMessage;
use App\Models\Dashboard;
use App\Models\Enterprise;
use App\Models\EnterpriseParticipant;
use App\Models\Group;
use App\Models\InviteSearchModel;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\Schedule;
use App\Models\ScheduleParticipant;
use App\Models\ScheduleSub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class InviteController extends \App\Http\Controllers\Controller
{
    /**
     * 協力会社一覧取得
     * @return mixed
     */
    public function getList()
    {
        $enterprise=request('enterprise');
        $invite=request('invite');
        //協力会社ユーザ、事業者ユーザ、協力会社兼事業者ユーザ
        $arr['enterprise']=false;
        $arr['invite']=false;
        $arr['enterpriseShow']=false;
        $arr['inviteShow']=false;
        if(Auth::user()->enterprise_id){
            $arr['enterprise']=true;
        }
        $res=EnterpriseParticipant::where('user_id',Auth::id())->get()->toArray();
        if(count($res)>0){
            $arr['invite']=true;
        }

        if(!$enterprise&&!$invite){
            $enterprise=$arr['enterprise'];
        }
        if ($enterprise){
            $arr['enterpriseShow']=true;
            $model = EnterpriseParticipant::where('enterprise_id',Auth::user()->enterprise_id)
                ->with(['account'=>function ($q) {
                    $q->withTrashed();
                },'account.coopEnterprise','account.enterprise'])->get()->toArray();
        } else {
            $arr['inviteShow']=true;
            $model = EnterpriseParticipant::where('user_id', Auth::id())->with(['createdBy'=>function ($q) {
                $q->withTrashed();
            },'createdBy.enterprise'])->get()->toArray();
            foreach ($model as $k => $modelItem) {
                $model[$k]['account']=[];
                if ($modelItem['created_by']){
                    $model[$k]['user_id'] = $modelItem['created_by']['id'];
                    $model[$k]['email'] = $modelItem['created_by']['email'];
                    $model[$k]['account']['id']= $modelItem['created_by']['id'];
                    $model[$k]['account']['name'] = $modelItem['created_by']['name'];
                    $model[$k]['account']['file'] = $modelItem['created_by']['file'];
                    $model[$k]['account']['enterprise_id'] = $modelItem['created_by']['enterprise_id'];
                    $model[$k]['account']['auth_id ']= $modelItem['created_by']['auth_id'];
                    $model[$k]['account']['union_id ']= $modelItem['created_by']['union_id'];
                    $model[$k]['account']['enterprise']= $modelItem['created_by']['enterprise'];
                    $model[$k]['account']['deleted_at']= $modelItem['created_by']['deleted_at'];
                }
                $model[$k]['isParticipant'] = true;
            }
        }
        foreach ($model as $k => $v){
            if($v['account']){
                $v['deleted_at'] = $v['account']['deleted_at'];
            }else{
                $v['deleted_at']= null;
            }
            $model[$k]=$v;
        }

        //承認待ちの場合 created_at = '1970-01-01'
        foreach ($model as $k => $v)
        {
            //承認待ち
            if ($v['agree'] != 1){
                $model[$k]['created_at'] = '1970-01-01';
                $model[$k]['updated_at'] = '1970-01-01';
            }
        }
        $res=['data'=>$model,'power'=>$arr];
        return $res;
    }

    /**
     * 協力会社テーブル削除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delInvite(Request $request)
    {
        $resArr = [];
        $ids = $request->get('id');
        $userIdArr = EnterpriseParticipant::whereIn('id', $ids)->where('agree',1)->pluck('user_id')->toArray();

        //協力会社・職人は同じ人を含む
        if (count($userIdArr)){
            //私自身の会社を招待の職人
            $resArr = ChatContact::whereIn('to_user_id',$userIdArr)->where('contact_agree',1)
                ->whereHas('accountsInvite',function ($q){
                    $q->where('enterprise_id',Auth::user()->enterprise_id)->whereNotNull('enterprise_id');
                })->pluck('to_user_id')->toArray();

            $enterpriseArr = User::whereIn('id',$userIdArr)->pluck('enterprise_id')->toArray();

            //わたしは職人
            $contact = ChatContact::where('to_user_id',Auth::id())->where('contact_agree',1)
                ->whereHas('accountsInvite',function ($q) use($enterpriseArr,$userIdArr){
                    $q->whereIn('enterprise_id',$enterpriseArr);
                    $q->orWhere('id',$userIdArr);
                })->pluck('from_user_id')->toArray();
            $resArr = array_merge($resArr,$contact);
        }
        $res = [];
        foreach ($userIdArr as $item){
            if (!in_array($item,$resArr)){
                $res[] = $item;
            }
        }
        //スケジュールから削除
        $user_arr=User::where('enterprise_id',Auth::user()->enterprise_id)->whereNotNull('enterprise_id')->pluck('id')->toArray();
        if(!$user_arr){
            $user_arr[]=Auth::id();
        }
        $schedule=Schedule::whereIn('created_user_id',$user_arr)->where('ed_datetime','>=',date('Y-m-d H:i:s'))->pluck('id')->toArray();
        $groupPersonArr = ChatGroup::whereHas('mine.user', function ($q) {
            $q->where('enterprise_id', Auth::user()->enterprise_id);
            $q->whereNotNull('enterprise_id');
        })->whereIn('user_id', $res)
            ->whereHas('group', function ($q){
                $q->where('kind',1); //1=ダイレクトチャット
            })->pluck('group_id')->toArray();

        $enterpriseIdsArr = User::where('enterprise_id', Auth::user()->enterprise_id)
            ->withTrashed()->pluck('id')->toArray();

        $groupsArr = ChatGroup::whereHas('mine.user', function ($q) {
            $q->where('enterprise_id', Auth::user()->enterprise_id);
            $q->whereNotNull('enterprise_id');
        })->whereIn('user_id', $res)
            ->whereHas('mine', function ($q) use ($enterpriseIdsArr) {
                $q->whereIn('user_id', $enterpriseIdsArr)->where('admin', 1);//社内
            })
            ->whereHas('group', function ($q){
                $q->where('kind',0); //0=グループチャット
            })->pluck('group_id')->toArray();
        try {
            DB::beginTransaction();
            EnterpriseParticipant::whereIn('id', $ids)->delete();
            //schedule->delete
            $this->deleteScheduleParticipant($res,$schedule);
            //group->delete
            $this->selfGroupCheck($res, $groupsArr);
            //project group->delete
            $this->deleteProjectParticipants($res, $groupsArr);
            //dashboard
            $dashboard = new DashboardController();
            $userArr = User::where('enterprise_id',Auth::user()->enterprise_id)
                ->whereNotNull('enterprise_id')->get()->toArray();
            $users =  User::whereIn('id',$userIdArr)->with(['enterprise','enterpriseCoop'])->get()->toArray();
            foreach ($userArr as $item){
                foreach ($users as $value) {
                    if ($value['enterprise_id']){
                        $value['company'] = $value['enterprise']['name'];
                    }elseif ($value['coop_enterprise_id']){
                        $value['company'] = $value['enterprise_coop']['name'];
                    }else{
                        $value['company'] = $value['company_name'];
                    }
                    if (Auth::id() != $item['id']) {
                        if ($value['company']){
                            $dashboard->addDashboard($item['id'], 6,
                                $value['name'] . '（' . $value['company'] . '）が削除されました。',
                                '', $item['id']);
                        }else{
                            $dashboard->addDashboard($item['id'], 6,
                                $value['name'] . 'が削除されました。',
                                '', $item['id']);
                        }

                    }
                }
            }
            if (count($groupPersonArr)) {
                //解散グループ 容量を増やす
                $groupArr=Group::whereIn('id', $groupPersonArr)->get();
                $chat = new ChatController();
                foreach ($groupArr as $group){
                    $chat->deleteContain($group);
                }
                //1 vs 1 -> delete
                ChatGroup::whereIn('group_id', $groupPersonArr)->delete();
                ChatList::whereIn('group_id', $groupPersonArr)->delete();
                //2020-11-2 #2298  チャット情報は削除しないことにした
//                ChatMessage::whereIn('group_id', $groupPersonArr)->delete();
                ChatLastRead::whereIn('group_id', $groupPersonArr)->delete();
                Group::whereIn('id', $groupPersonArr)->delete();
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

    public function selfGroupCheck($userIdsArr,$exceptIds)
    {
        //groupチャットを削除
        //グループを削除
        ChatGroup::whereIn('group_id', $exceptIds)->whereIn('user_id',$userIdsArr)->delete();
        ChatList::whereIn('group_id', $exceptIds)->whereIn('user_id',$userIdsArr)->delete();

    }

    /**
     * 削除時に案件共有者を同時に削除する
     * @param $userIds
     * @param $groupArr
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function deleteProjectParticipants($userIds,$groupArr)
    {
        $projectIds = Project::whereIn('group_id',$groupArr)->get(['id','group_id'])->toArray();
        try {
            foreach ($projectIds as $item) {
                $groupIdArr[] = $item['group_id'];
                ProjectParticipant::whereIn('user_id', $userIds)->where('project_id', $item['id'])->delete();
            }
        } catch (\PDOException $e) {
            //データベースエラー
            $error = trans('messages.error.delete');
            Log::error($e);
            return $this->error($e, [$error]);
        }
    }

    /**
     * 協力会社検索
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function search(Request $request)
    {
        $query = new InviteSearchModel();
        $searchArray = Common::escapeDBSelectKeyword($request->input('searchName'));
        if (Auth::user()->enterprise_id){
            $models = $query->search($searchArray)
                ->where('enterprise_participants.enterprise_id',Auth::user()->enterprise_id)
                ->with('account.coopEnterprise','account.enterprise')->get();
        } else {
            $models = EnterpriseParticipant::where('user_id', Auth::id())->with('createdBy.enterprise')->where(
                function ($q) use ($searchArray){
                    if (strlen($searchArray) > 0) {
                        $q->where(function($q1) use ($searchArray){
                            $q1->whereHas('createdBy', function ($query) use ($searchArray) {
                                $query->where('users.name', 'like', "%$searchArray%");
                            })->orWhereHas('createdBy.enterprise',function ($query) use ($searchArray) {
                                $query->where('enterprises.name', 'like', "%$searchArray%");
                            })->orWhereHas('createdBy.coopEnterprise',function ($query) use ($searchArray) {
                                $query->where('enterprises.name', 'like', "%$searchArray%");
                            });
                        });
                    }
                })->get();
            foreach ($models as $k => $modelItem) {
                $models[$k]->user_id = $modelItem->createdBy->id;
                $models[$k]->email = $modelItem->createdBy->email;
                $models[$k]->account->id = $modelItem->createdBy->id;
                $models[$k]->account->name = $modelItem->createdBy->name;
                $models[$k]->account->file = $modelItem->createdBy->file;
                $models[$k]->account->enterprise_id = $modelItem->createdBy->enterprise_id;
                $models[$k]->account->auth_id = $modelItem->createdBy->auth_id;
                $models[$k]->account->union_id = $modelItem->createdBy->union_id;
                $models[$k]->account->enterprise = $modelItem->createdBy->enterprise;
                $models[$k]->isParticipant = true;
            }
        }

        //承認待ちの場合 日付created_at = '1970-01-01'
        foreach ($models as $k => $v)
        {
            //承認待ち
            if ($v->agree != 1){
                $models[$k]->created_at = '1970-01-01';
                $models[$k]->updated_at = '1970-01-01';
            }
        }

        return $models;
    }

    /**
     * inviteを並べ替え
     * @param Request $request
     * @return array|string|null
     */
    public function detailSearch(Request $request)
    {
        $searchArray = $request->input('searchName');
        $sort = $request->get('sort');
        $order = $request->get('order');
        if ($sort == 'name'){
            foreach ($searchArray as $item) {
                $paytime[] = $item['account']['name'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, SORT_LOCALE_STRING,$searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, SORT_LOCALE_STRING,$searchArray);
            }
        } elseif ($sort == 'enterprise'){
            foreach ($searchArray as $item) {
                //組織 会社名or協力会社名を取得
                if ($item['account']['enterprise']['name']) {
                    //会社名
                    $paytime[] = $item['account']['enterprise']['name'];
                } elseif ($item['account']['coop_enterprise']['name']) {
                    //協力会社名
                    $paytime[] = $item['account']['coop_enterprise']['name'];
                } else {
                    //NULL
                    $paytime[] = null;
                }
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, SORT_LOCALE_STRING,$searchArray);
            } else {
                array_multisort($paytime, SORT_DESC,SORT_LOCALE_STRING, $searchArray);
            }
        } elseif ($sort == 'date'){
            foreach ($searchArray as $item) {
                $paytime[] = $item['created_at'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC,SORT_LOCALE_STRING, $searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, SORT_LOCALE_STRING,$searchArray);
            }
        }
        return $searchArray;
    }

    /**
     * 協力会社招待メール送信する
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request)
    {
        $enterpriseAmount = Enterprise::where('id', Auth::user()->enterprise_id)->pluck('amount')->first();
        $controller = new AccountController();
        $usersAmount = count($controller->index(new Request));
        if ($usersAmount >= $enterpriseAmount) {
            $error = trans('messages.error.contract');
            return $this->error($error, $error);
        }
        $toEmail = $request->input('toEmail');
        $message = $request->input('message');
        $hold_time = config('web.cache.email_verification.expiration_minutes');
        foreach ($toEmail as $eachToEmail) {
            $user = User::query()->where('email', $eachToEmail)->first();
            $data = Str::random(30);
            Cache::put("inviteMail" . $data, Auth::id().':'.$eachToEmail, $hold_time * 60 *60 * 24);
            //ユーザーテーブルにメールアドレスが存在する場合
            if (!empty($user)) {
                //事業者（USERテーブルにENTERPRISE_ID≠NULL）且つ本社ユーザーではない場合
                if ($user->enterprise_id != Auth::user()->enterprise_id) {
                    DB::beginTransaction();
                    //insert db enterprise_participants
                    try {
                        //事業協力会社テーブルに会社ID、ユーザーID、承認待ち（AGREE=0）のデータを追加
                        $invitation = new EnterpriseParticipant();
                        $invitation->enterprise_id = Auth::user()->enterprise_id;
                        $invitation->user_id = $user->id;
                        $invitation->email = $eachToEmail;
                        $invitation->message = $message;
                        $invitation->agree = 0;
                        $invitation->created_by = Auth::id();
                        $invitation->invitation_code = $data;
                        $invitation->save();
                        DB::commit();

                        //事業者（USERテーブルにENTERPRISE_ID≠NULL）且つ本社ユーザーではない場合
                        $url = url('/', null, true) . '/participant/invitation/' . $data;
                        Mail::to($eachToEmail)->send(new ContactInvite(Auth::user()->name, $message, $url));
                        if (Mail::failures()) {
                            DB::rollBack();
                            $error = trans('messages.error.email');
                            return $this->error($error, $error);
                        }
                    } catch (\Exception $e) {
                        Cache::forget("inviteMail" . $data);
                        DB::rollBack();
                        $error = trans('messages.error.email');
                        return $this->error($error, $error);
                    }
                } else {
                    //事業者（USERテーブルにENTERPRISE_ID≠NULL）且つ本社ユーザーの場合
                    //メイル送信機能check処理
                }
            } else {
                //ユーザーテーブルにメールアドレスが存在しない場合
                DB::beginTransaction();
                try {
                    //事業協力会社テーブルに会社ID、承認待ち（AGREE=0）のデータを追加
                    $invitation = new EnterpriseParticipant();
                    $invitation->enterprise_id = Auth::user()->enterprise_id;
                    $invitation->email = $eachToEmail;
                    $invitation->message = $message;
                    $invitation->agree = 0;
                    $invitation->created_by = Auth::id();
                    $invitation->invitation_code = $data;
                    $invitation->save();
                    DB::commit();

                    //事業者登録招待メール送信する
                    $url = url('/', null, true) . '/participant/invitation/' . $data;
                    Mail::to($eachToEmail)->send(new ContactInvite(Auth::user()->name, $message, $url));
                    if (Mail::failures()) {
                        Cache::forget("inviteMail" . $data);
                        DB::rollBack();
                        $error = trans('messages.error.email');
                        return $this->error($error, $error);
                    }
                } catch (\Exception $e) {
                    Cache::forget("inviteMail" . $data);
                    DB::rollBack();
                    $error = trans('messages.error.email');
                    return $this->error($error, $error);
                }
            }
        }

    }

    /**
     * 本社ユーザーメイル check
     * 職人メイル check
     * ユーザーテーブルにメールアドレスが存在しない check
     * @param Request $request
     * @return string
     */
    public function checkEmail(Request $request)
    {
        $checkEmail = $request->input('checkEmail');
        $user = User::query()->where('email',$checkEmail)->first();
        if (!empty($user)){
            if(Auth::user()->enterprise_id) {
                $enterprise = EnterpriseParticipant::query()->where('email',$checkEmail)->where('enterprise_id',Auth::user()->enterprise_id)->first();
                if (!empty($enterprise)){
                    return 'already';
                }
                if ($user->enterprise_id == Auth::user()->enterprise_id){
                    return 'sameEnterprise';
                }
                if ($user->enterprise_id != Auth::user()->enterprise_id){
                    return 'ok';
                }
            }else{
                return 'ok';
            }
        }else{
            $enterprise = EnterpriseParticipant::query()->where('email',$checkEmail)->where('enterprise_id',Auth::user()->enterprise_id)->first();
            if (!empty($enterprise)){
                return 'already';
            }
            if (strlen($checkEmail) == 0) {
                return 'empty';
            } else {
                return 'ok';
            }
        }
    }

    /**
     * 協力会社一覧チャットリンク
     * @param Request $request
     * @return mixed
     */
    public function inviteChatLink(Request $request)
    {
        $enterpriseParticipantId = $request->input('invite');
        $inviteUserId = $request->input('userId');
        if (Auth::user()->enterprise_id) {
            $enterpriseParticipant = EnterpriseParticipant::query()->where('id',$enterpriseParticipantId['id'])->first();
            if ($enterpriseParticipant->agree == 1 && $enterpriseParticipant->enterprise_id == Auth::user()->enterprise_id) {
                $chatGroup = ChatGroup::query()->where('user_id', $enterpriseParticipant->user_id)->where('admin', 1)->get();
                $chatGroupAuth = ChatGroup::query()->where('user_id', Auth::id())->where('admin', 1)->get();
                $groupIdsTempArr = [];
                foreach ($chatGroup as $item) {
                    foreach ($chatGroupAuth as $itemAuth) {
                        if ($item->group_id == $itemAuth->group_id) {
                            $groupIdsTempArr[] = $item->group_id;
                        }
                    }
                }
                if (count($groupIdsTempArr)>0){
                    //1 vs 1 chatgroup filter
                    $groupModels = DB::table('groups')->whereIn('id',$groupIdsTempArr)->where('kind',1)->pluck('id');
                    if (count($groupModels)>0){
                        $arr = array("user_id" => $inviteUserId, "group_id" => $groupModels[0]);
                        return $arr;
                    } else {
                        $error = trans('messages.error.system');
                        return $this->error($error, $error);
                    }
                } else {
                    //新規の場合
                    return $this->createNewGroup($inviteUserId);
                }
            }
        } else {
            if ($enterpriseParticipantId['agree'] == 1) {
                $chatGroup = ChatGroup::query()->where('user_id', $enterpriseParticipantId['user_id'])->where('admin', 1)->get();
                $chatGroupAuth = ChatGroup::query()->where('user_id', Auth::id())->where('admin', 1)->get();
                $groupIdsTempArr = [];
                foreach ($chatGroup as $item) {
                    foreach ($chatGroupAuth as $itemAuth) {
                        if ($item->group_id == $itemAuth->group_id) {
                            $groupIdsTempArr[] = $item->group_id;
                        }
                    }
                }
                if (count($groupIdsTempArr)>0){
                    //1 vs 1 chatgroup filter
                    $groupModels = DB::table('groups')->whereIn('id',$groupIdsTempArr)->where('kind',1)->pluck('id');
                    if (count($groupModels)>0){
                        $arr = array("user_id" => $inviteUserId, "group_id" => $groupModels[0]);
                        return $arr;
                    } else {
                        $error = trans('messages.error.system');
                        return $this->error($error, $error);
                    }
                } else {
                    //新規の場合
                    return $this->createNewGroup($inviteUserId);
                }
            }
        }
    }

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

    public function cooperatorFetch(Request $request){
        $invitationToken = $request->post('invitationToken');
        return $enterpriseParticipant = EnterpriseParticipant::where('invitation_code', $invitationToken)
            ->get(['created_by','email']);
    }

    public function cooperatorRegister(Request $request)
    {
        $invitationFromUserId = $request->post('invitationFromUserId');
        $invitationToken = $request->post('invitationToken');
        $cooperators = $request->get('cooperators');
        if (Cache::has("inviteMail" . $invitationToken) and strlen($invitationToken) == 30) {
            $var = Cache::get("inviteMail" . $invitationToken);
            $token_arr = explode(':', $var);
            $id = $token_arr[0];
            $email = $token_arr[1];
            if ( $invitationToken  && $invitationFromUserId == $id && $email == $cooperators['email']) {
                $enterpriseParticipant = EnterpriseParticipant::query()
                    ->where('created_by', $id)->where('email',$email)
                    ->where(function ($query) use ($invitationToken) {
                        $query->where('invitation_code', $invitationToken);
                    })->get();
                if (!empty($enterpriseParticipant)) {
                    $enterpriseIds = User::where('id',$id)->pluck('enterprise_id');
                    $userRes = User::whereIn('enterprise_id',$enterpriseIds)->pluck('id')->toArray();
                    $dashboard = new DashboardController();
                    $toName = $cooperators['last_name'] . $cooperators['first_name'];
                    foreach ($userRes as $p){
                        if ($p != $id) {
                            //協力会社dashboard  名前（所属名）add
                            $cooperatorNameTmp = '';
                            if (isset($cooperators['cooperator_name'])) {
                                $cooperatorNameTmp = '('.$cooperators['cooperator_name'].')';
                            }
                            $dashboard->addDashboard($p,6,
                                $toName.$cooperatorNameTmp.'が承認されました。',
                                '',$p);
                        } else {
                            //
                        }
                    }
                    foreach ($enterpriseParticipant as $item) {
                        DB::beginTransaction();
                        try {
                            if ($item->agree == 0) {
                                if (count($cooperators) != 0) {
                                    //table users
                                    $user = new User();
                                    $user->name = $cooperators['last_name'] . $cooperators['first_name'];
                                    $user->last_name = $cooperators['last_name'];
                                    $user->first_name = $cooperators['first_name'];
                                    $user->email = $cooperators['email'];
                                    $user->password = bcrypt($cooperators['userPassword']);
                                    $user->worker = '0';
                                    $user->enterprise_admin = '1';
                                    $user->save();

                                    //table enterprises
                                    $cooperator = new Enterprise();
                                    if (isset($cooperators['cooperator_name'])) {
                                        $cooperator->name = $cooperators['cooperator_name'];
                                    }
                                    if (isset($cooperators['zip'])) {
                                        $cooperator->zip = $cooperators['zip'];
                                    }
                                    if (isset($cooperators['pref'])) {
                                        $cooperator->pref = $cooperators['pref'];
                                    }
                                    if (isset($cooperators['town'])) {
                                        $cooperator->town = $cooperators['town'];
                                    }
                                    if (isset($cooperators['street'])) {
                                        $cooperator->street = $cooperators['street'];
                                    }
                                    if (isset($cooperators['house'])) {
                                        $cooperator->house = $cooperators['house'];
                                    }
                                    if (isset($cooperators['tel'])) {
                                        $cooperator->tel = $cooperators['tel'];
                                    }
                                    $cooperator->cooperator = '1';
                                    $cooperator->user_id = $user->id;
                                    $cooperator->save();

                                    $user->coop_enterprise_id = $cooperator->id;
                                    $user->save();

                                    $cooperator->user_id = $user->id;
                                    $cooperator->save();


                                    //事業協力会社テーブルに承認する（AGREE＝0から1に変更）
                                    $item->agree = 1;
                                    $item->user_id = $user->id;
                                    $item->save();

                                    $user = User::query()->where('id', $item->user_id)->first();

                                    $users = User::query()->where('enterprise_id', $item->enterprise_id)->get();

                                    foreach ($users as $itemUsers) {
                                        //group
                                        $group = new Group();
                                        $group->name = $user->name;
                                        $group->kind = 1;
                                        $group->save();

                                        //chatgroup
                                        $chatGroup = new ChatGroup();
                                        $chatGroup->user_id = $user->id;
                                        $chatGroup->group_id = $group->id;
                                        $chatGroup->admin = 1;
                                        $chatGroup->save();
                                        $chatGroup2 = new ChatGroup();
                                        $chatGroup2->user_id = $itemUsers->id;
                                        $chatGroup2->group_id = $group->id;
                                        $chatGroup2->admin = 1;
                                        $chatGroup2->save();
                                    }
                                    DB::commit();
                                    Auth::login($user);
                                    Cache::forget("inviteMail" . $invitationToken);
                                    return "inviteOk";
                                } else {
                                    DB::rollBack();
                                    Cache::forget("inviteMail" . $invitationToken);
                                    return "inviteNot";
                                }
                            } else {
                                DB::rollBack();
                                Cache::forget("inviteMail" . $invitationToken);
                                return "inviteNot";
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Cache::forget("inviteMail" . $invitationToken);
                            return "inviteNot";
                        }
                    }
                } else {
                    return "inviteNot";
                }
            } else{
                return "inviteNot";
            }
        } else{
            return "inviteNot";
        }
    }
}
