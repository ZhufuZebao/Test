<?php
/**
 * アカウント管理のコントローラー
 *
 * @author  WuJi
 */

namespace App\Http\Controllers\Web;

use App\Http\Facades\Common;
use App\Models\Account;
use App\Models\ChatContact;
use App\Models\Enterprise;
use App\Models\AccountSearchModel;
use App\Models\EnterpriseParticipant;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\Schedule;
use App\Models\ScheduleParticipant;
use App\Models\ScheduleSub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatGroup;
use App\Models\Group;
use App\Models\ChatList;
use Illuminate\Support\Facades\DB;


/**
 * アカウント管理 一覧
 * @param Request $request
 * @return array|string
 */
class AccountController extends \App\Http\Controllers\Controller
{
    /**
     * アイデンティティの切り替え
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function switchIdentity(Request $request){
        $id = $request->get('id');
        $admin = $request->get('admin');
        //契約者
        if ($request->get('enterpriseId')>0) {
            //契約者
            $enterpriseId = $request->get('enterpriseId');
        } else {
            //SITEWEB
            $enterpriseId = Auth::user()->enterprise_id;
        }
        $idArr = Account::where('enterprise_id',$enterpriseId)->where('id',$id)->get('id')->toArray();
        $enterpriseArr = Enterprise::where('user_id',$id)->get()->toArray();
        if (!count($idArr) || $id == Auth::id() || count($enterpriseArr) || !Auth::user()->enterprise_admin){
            $error = trans('messages.error.permission');
            return $this->error($error);
        }
        $account = Account::find($id);
        if($admin){
            $account->enterprise_admin = 0;
        }else{
            $account->enterprise_admin = 1;
        }
        $account->update();
        return $this->json();
    }
    public function index(Request $request)
    {
        //契約者
        if ($request->get('enterpriseId')>0) {
            //契約者
            $enterpriseId = $request->get('enterpriseId');
        } else {
            //SITEWEB
            // 該当事業者の「会社ID」を取得
            $enterpriseId = Auth::user()->enterprise_id;
        }

        // 該当事業者のデータを取得
        $accounts = Account::where('enterprise_id', $enterpriseId)->orderBy('name', 'asc')->get()->toArray();
        $participant = EnterpriseParticipant::where('enterprise_id', $enterpriseId)
            ->where('agree',1)->get('user_id')->toArray();
        $idArr=array();
        foreach ($participant as $value){
            $idArr[]= $value['user_id'];
        }
        $participantUsers = Account::whereIn('id', $idArr)->orderBy('name', 'asc')->get()->toArray();
        for ($i = 0 ; $i < count($participantUsers) ; $i++){
            //enterprise_admin = 2の場合は協力会社です タグ
            $participantUsers[$i]['enterprise_admin'] = '2';
            //協力会社ユーザのid タグ 'e'
            $participantUsers[$i]['id'] = 'e' . $participantUsers[$i]['id'];
        }
        return array_merge_recursive($participantUsers,$accounts);
    }

    /**
     * アカウント管理 検索
     * @param Request $request
     * @return array|string
     */
    public function search(Request $request)
    {
        //契約者check
        if ($request->get('enterpriseId')>0) {
            //契約者の場合
            $enterpriseId = $request->get('enterpriseId');
        } else {
            //SITEWEB
            // 該当事業者の「会社ID」を取得
            $enterpriseId = Auth::user()->enterprise_id;
        }

        $query = new AccountSearchModel();
        $query->init(['keyword' => Common::escapeDBSelectKeyword($request->get('q')),]);
        $idArr = [];
        $items = EnterpriseParticipant::where('enterprise_id',$enterpriseId)->get();
        foreach ($items as $item){
            $idArr[] = $item['user_id'];
        }
        $accounts = $query->search()
            ->where(function($q) use ($enterpriseId,$idArr){
                $q->where('enterprise_id',$enterpriseId);
                $q->orWhereIn('id',$idArr);
            })->get()->toArray();
        for ($i = 0 ; $i < count($accounts) ; $i++){
            if (in_array($accounts[$i]['id'],$idArr)){
                //enterprise_admin = 2の場合は協力会社です タグ
                $accounts[$i]['enterprise_admin'] = '2';
                //協力会社ユーザのid タグ 'e'
                $accounts[$i]['id'] = 'e' . $accounts[$i]['id'];
            }
        }
        return $accounts;
    }

    /**
     * アカウント管理 削除
     * @param Request $request
     * @return string
     */
    public function delete(Request $request)
    {
        //契約者check
        if ($request->post('enterpriseId') > 0){
            //契約者の場合
            $enterpriseId = $request->get('enterpriseId');
        } else {
            //SITEWEB
            $enterpriseId = Auth::user()->enterprise_id;
        }
        //削除権限があるか検証する
        $ids = $request->post('ids');
        foreach ($ids as $k => $v) {
            //現在のアカウントの削除は禁止です
            if ($v == Auth::id()) {
                $error = trans('messages.error.delete');
                return $this->error(\PDOException, $error);
            }
            //!協力会社
            if ($v[0] != 'e') {   // todo フロント協力会社は既にシールドしており,コードは無効である
                $account_info = Account::where('id', $v)->first();
                if ($account_info['enterprise_id'] !== $enterpriseId) {
                    //データベースエラー
                    $error = trans('messages.error.delete');
                    return $this->error(\PDOException, $error);
                }
            }
            $is_bind = Enterprise::where('user_id', $v)->first('user_id');
            //エンタプライズに結び付けられた口座は削除禁止
            if ($is_bind) {
                $error = trans('messages.error.delete');
                return $this->error(\PDOException, $error);
            }
        }
        try {
            foreach ($ids as $k => $v) {
                //idは協力会社の場合　削除　'e'は協力会社のタグです
                if ($v[0] == 'e') {
                    $this->del_enterprise_participants(substr($v,1));// todo フロント協力会社は既にシールドしており,コードは無効である
                } else {
                    //user table softDeletes
                    Account::query()->where('id', $v)->delete();
                    //各ユーザの削除はループ処理される
                    $this->del_group($v);
                    //社内ユーザのproject Participantから削除
                    ProjectParticipant::where('user_id',$v)->delete();
                }
            }
            //スケジュールから削除
            //契約者check
            if ($request->post('enterpriseId') > 0){
                //契約者の場合
                $user_arr=User::where('enterprise_id',$request->get('enterpriseId'))->whereNotNull('enterprise_id')->pluck('id')->toArray();
            } else {
                //SITEWEB
                $user_arr=User::where('enterprise_id',Auth::user()->enterprise_id)->whereNotNull('enterprise_id')->pluck('id')->toArray();
            }
            if(!$user_arr){
                $user_arr[]=Auth::id();
            }
            $schedule=Schedule::whereIn('created_user_id',$user_arr)->where('ed_datetime','>=',date('Y-m-d H:i:s'))->pluck('id')->toArray();
            //schedule delete
            $this->deleteScheduleParticipant($ids,$schedule);
        } catch (\PDOException $e) {
            //データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, $error);
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

    private function del_group_contact($group_id)
    {
        //解散グループ 容量を増やす
        $group=Group::where('id', $group_id)->first();
        $chat = new ChatController();
        $chat->deleteContain($group);
        //相対データを2つ削除する
        ChatGroup::where('group_id', $group_id)->delete();
        ChatList::where('group_id', $group_id)->delete();
        //groupを削除する
        Group::destroy($group_id);
    }

    private function del_group_project($group_id, $user_id)
    {
        //groupとuser_idとを含む対応を削除する
        ChatGroup::where('group_id', $group_id)->where('user_id', $user_id)->delete();
        ChatList::where('group_id', $group_id)->where('owner_id', $user_id)->delete();
    }

    /**
     * アカウント管理 新規
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        if ($request->get('accountId')) {
            $user = Account::find($request->get('accountId'));
            $account = new Account();
            $account->fill($request->get("account"));
            //契約者の場合
            if ($request->get('enterpriseId')>0){
                $account->enterprise_id = $request->get('enterpriseId');
            }
            $validate = $account->validate();
            if (!$validate->fails()) {
                 DB::beginTransaction();
                try {
                    if ($account['editPassword']){
                        $user->password = bcrypt($account['editPassword']);
                    }
                    $user->email = $request->get('email');
                    $user->first_name = $account['first_name'];
                    $user->last_name = $account['last_name'];
                    $user->name = $account['name'];
                    $user->enterprise_admin = $account['enterprise_admin'];
                    $user->update();

                    //協力会社一覧や職人一覧にもメールアドレスの変更が
                    //反映される必要がありますので、改修
                    EnterpriseParticipant::where('user_id',$request->get('accountId'))
                        ->update(['email' => $request->get('email')]);
                    ChatContact::where('to_user_id',$request->get('accountId'))
                        ->update(['email' => $request->get('email')]);

                    DB::commit();
                } catch (\PDOException $e) {
                    DB::rollBack();
                    //データベースエラー
                    $error = trans('messages.error.insert');
                    return $this->error($e, $error);
                }
            }
            return $this->json($validate->errors()->all());
        } else {
            $userId = Auth::id();
            //契約者の場合
            if ($request->get('enterpriseId')>0){
                //契約者
                $enterpriseAmount = Enterprise::where('id', $request->get('enterpriseId'))->pluck('amount')->first();
            } else {
                //SITEWEB
                $enterpriseAmount = Enterprise::where('id', Auth::user()->enterprise_id)->pluck('amount')->first();
            }
            $usersAmount = count($this->index($request));
            if ($usersAmount >= $enterpriseAmount) {
                $error = trans('messages.error.contract');
                return $this->error($error, $error);
            }

            //契約者の場合
            if ($request->get('enterpriseId')>0){
                //契約者
                $enterpriseId = $request->get('enterpriseId');
            } else {
                //SITEWEB
                $enterpriseId = Account::where('id', $userId)->pluck('enterprise_id')->first();
            }


            $user = User::where('email', $request->get('email'))->count();
            if ($user > 0) {
                //user存在
                $userInvite = User::where('email', $request->get('email'))->first();
                DB::beginTransaction();
                try {
                    $userInvite->enterprise_admin = $request->get("account")['enterprise_admin'];
                    $userInvite->enterprise_id = $enterpriseId;
                    $userInvite->save();
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            } else {
                // 検証
                $account = new Account();
                $account->fill($request->get("account"));
                $validate = $account->validate();

                if (!$validate->fails()) {
                    $account->password = bcrypt($account['password']);
                    $account->enterprise_id = $enterpriseId;
                    $account->worker = 0;
                    try {
                        $account->save();
                        //FRIENDS関係を処理する
                        $friend_name = $account->name;
                        $friend_id = $account->id;
                        $accounts = Account::where('enterprise_id', $enterpriseId)->get(['id']);
                        foreach ($accounts as $k => $v) {
                            if ($v['id'] !== $friend_id) {
                                $this->add_friend_group($v['id'], $friend_id, $friend_name);
                            }
                        }
                    } catch (\PDOException $e) {
                        //データベースエラー
                        $error = trans('messages.error.insert');
                        return $this->error($e, $error);
                    }
                }
                return $this->json($validate->errors()->all());
            }
        }

    }

    private function add_friend_group($user_id, $friend_id, $friend_name)
    {
        //Group Create
        $group = new Group();
        $group->name = $friend_name;
        $group->kind = 1;
        $group->save();
        $group_id = $group->id;
        //ChatGroup Create
        $chatGroup = new ChatGroup();
        $chatGroup->group_id = $group_id;
        $chatGroup->user_id = $user_id;
        $chatGroup->admin = 1;
        $chatGroup->save();
        //Friend's ChatGroup Create
        $chatGroupFriend = new ChatGroup();
        $chatGroupFriend->group_id = $group_id;
        $chatGroupFriend->user_id = $friend_id;
        $chatGroupFriend->admin = 1;
        $chatGroupFriend->save();
        //ChatList Create
        $chatList = new ChatList();
        $countNum = DB::table('chatlists')->where('group_id', $chatGroup->group_id)->where('owner_id',$friend_id)->count();
        if($countNum<1){
            $chatList->owner_id =$friend_id;
            $chatList->user_id =$user_id;
            $chatList->group_id =$chatGroup->group_id;
            $chatList->top = 0;
            $chatList->save();
        }
        $chatList = new ChatList();
        $countNum = DB::table('chatlists')->where('group_id', $chatGroup->group_id)->where('owner_id', $user_id)->count();
        if($countNum<1){
            $chatList->owner_id =$user_id;
            $chatList->user_id =$friend_id;
            $chatList->group_id =$chatGroup->group_id;
            $chatList->top = 0;
            $chatList->save();
        }
    }

    /**
     * メール唯一性の確認
     * @param Request $request
     * @return string
     */
    public function mailConfirm(Request $request)
    {
        $user = User::where('email', $request->emailAddress)->count();
        if ($user === 0) {
            //　emailAddress存在しない
            return '0';
        } else {
            //　emailAddress存在しました
            return '1';
        }
    }

    /**
     * mail存在 user data取得
     */
    public function mailExist(Request $request)
    {
        $user = User::where('email', $request->emailAddress)->first();
        if (!$user->enterprise_id){
            return $user;
        } else {
          //emailAddressは他の社内ユーザの場合
          return '0';
        }
    }

    public function editMailConfirm(Request $request){
        $user = User::where('email', $request->emailAddress)->where('id','!=',$request->id)->first();
        if (!isset($user)){
            return $user;
        } else {
            //emailAddressは他の社内ユーザの場合
            return '0';
        }
    }

    /**
     * get account msg
     * @param Request $request
     * @return array
     */
    public function getAccountMsg(Request $request){
        $account = Account::where('id',$request->get("id"))
            ->get(['first_name','last_name','email','enterprise_admin']);
        return $this->json('',$account);
    }

    /**
     * 協力会社削除
     * タイブルenterprise_participantsとgroup、chatgroup　一括削除
     * @param $user_id
     */
    public function del_enterprise_participants($user_id)
    {
        $enterpriseParticipant = EnterpriseParticipant::where('enterprise_id',Auth::user()->enterprise_id)
            ->where('user_id',$user_id)->first();
        $chatGroupEnterprise = ChatGroup::query()->where('user_id', $enterpriseParticipant->user_id)->where('admin', 1)->get();
        $enterpriseUser = User::query()->where('enterprise_id', $enterpriseParticipant->enterprise_id)->get();
        foreach ($chatGroupEnterprise as $item) {
            foreach ($enterpriseUser as $itemUser) {
                $enter = ChatGroup::query()->where('user_id', $itemUser->id)->where('admin', 1)->get();
                foreach ($enter as $ent) {
                    if ($item->group_id == $ent->group_id) {
                        $delete1 = ChatGroup::query()->where('id', $item->id)->first()->delete();
                        $delete2 = ChatGroup::query()->where('id', $ent->id)->first()->delete();
                        $delete3 = Group::query()->where('id', $item->group_id)->first()->delete();
                    }
                }
            }
        }
        $enterpriseParticipant->delete();
    }

    /**
     * 会社のamountを取得
     * @return mixed
     */
    public function getEnterpriseAmount(Request $request){
        //契約者check
        if ($request->post('enterpriseId') > 0){
            //契約者の場合
            $enterpriseId = $request->get('enterpriseId');
        } else {
            //SITEWEB
            $enterpriseId = Auth::user()->enterprise_id;
        }
        //会社のamountを取得
        $enterpriseAmount = Enterprise::where('id',$enterpriseId)->pluck('amount')->first();
        return $enterpriseAmount;
    }
}
