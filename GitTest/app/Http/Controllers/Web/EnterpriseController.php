<?php

namespace App\Http\Controllers\Web;

use App\Http\Facades\Common;
use App\Mail\AdminAuthKeySend;
use App\Mail\ContactLink;
use App\Models\ChatGroup;
use App\Models\Enterprise;
use App\Models\EnterpriseParticipant;
use App\Models\Group;
use App\Models\ProjectParticipant;
use App\Models\SysNotice;
use App\Models\SysNoticeAlreadyRead;
use App\Models\User;
use App\Models\Project;
use App\Models\UserStorage;
use App\Models\ChatContact;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Session;
use Illuminate\Support\Facades\Redis;

class EnterpriseController extends \App\Http\Controllers\Controller
{
    /**
     * メール唯一性の確認
     * @param Request $request
     * @return string
     */
    public function mailUnique(Request $request)
    {
        $user = User::where('email', $request->emailAddress)->count();
        if ($user === 0) {
            //emailAddress存在しない
            return '0';
        } else {
            //emailAddress存在しました
            return '1';
        }
    }

    /**
     * 変更時のメール唯一性の確認
     * @param Request $request
     * @return string
     */
    public function editMailUnique(Request $request)
    {
        if (Auth::user()->email==$request->emailAddress){
            return '0';
        }
        $user = User::where('email', $request->emailAddress)->count();
        if ($user === 0) {
            //emailAddress存在しない
            return '0';
        } else {
            //emailAddress存在しました
            return '1';
        }
    }
    /**
     *パスワード確認
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function verifyPwd(Request $request)
    {
        $input = $request->input("pwd");
        $user = Auth::user();
        if (!Hash::check($input, $user->password)) {
            return "1";
        }
        return $this->json();
    }

    /**
     * パスワード修正
     * @param Request $request
     * @return array|string
     */
    public function editPwd(Request $request)
    {
        $input = $request->input("user");
        $oldPassword = $input['oldPassword'];
        $password = $input['password'];
        $userModel = new User();
        $user = Auth::user();
        $userModel->oldPassword = $this->isEmpty('oldPassword', $input);
        $userModel->password = $this->isEmpty('password', $input);
        $userModel->password_confirmation = $this->isEmpty('password_confirmation', $input);
        $v = $userModel->Pwdvalidate();
        if (!$v->fails()) {
            if (!Hash::check($oldPassword, $user->password)) {
                return "2";
            }
            $update = array(
                'password' => bcrypt($password),
            );
            try {
                User::where('id', $user->id)->update($update);
            } catch (\PDOException $e) {
                DB::rollBack();
                $error=trans('messages.error.insert');
                return $this->error($e,[$error]);
            }
        }
        return $this->json($v->errors()->all());
    }

    /**
     * 取る事業者情報
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->ip = $request->ip();
        $nowtime = date("Y-m-d H:i:s");
        // all users will refresh lastDate
        $user->last_date = $nowtime;
        $user->save();
        $models = User::where('id', $user->id)->with(['enterprise.user', 'enterpriseCoop.user'])->get()->toArray();
        $pro = ProjectParticipant::where('user_id', $user->id)->count();
        if (!empty($models[0])) {
            if ($pro){
                $models[0]['displayProject'] = true;
            }else{
                $models[0]['displayProject'] = false;
            }
            if (empty($models[0]['enterprise_id'])){
                $models[0]['enterprise_admin'] = '1';
            }
        }

        $noticeCount = $this->getNoticeCount();

        return ['user'=>$models,'noticeCount'=>$noticeCount];
    }

    public function getFilesBeforeSent() {
        $key = 'sendFile_' . Auth::id();
        $filesInfo = Redis::get($key);
        $fileData = unserialize($filesInfo);
        $return['groupId'] = $fileData['groupId'];
        $return['filesSent'] = $fileData['filses'];
        //案件
        $project=Project::where('group_id',$return['groupId'])->count();
        $parent=Group::where('id',$return['groupId'])->value('parent_id');
        if($project||$parent){
            $return['project']=1;
        }else{
            $return['project']=0;
        }
        $fileDataArr['groupId'] = 0;
        $fileDataArr['filses'] = [];
        $fileStr = serialize($fileDataArr);
        Redis::set($key, $fileStr);
        return $return;
    }

    public function getNoticeCount()
    {
        $nowtime = date("Y-m-d");

        $noticeIds = SysNotice::where('st_date', '<=', $nowtime)
            ->where('ed_date', '>=', $nowtime)->pluck('id');

        $alreadyRead = SysNoticeAlreadyRead::where('user_id', Auth::id())
            ->whereIn('sys_notice_id', $noticeIds)
            ->get()->toArray();

        $noticeCount = count($noticeIds)-count($alreadyRead);

        return $noticeCount;
    }

    /**
     * noticeステータス変更の読み取り
     * @param Request $request
     */
    public function noticeAlreadyRead(Request $request)
    {
        $noticeId = $request->post('noticeId');
        $readCheck = SysNoticeAlreadyRead::where('user_id', Auth::id())
            ->where('sys_notice_id', $noticeId)
            ->count();
        if ($readCheck == 0){
            $alreadyRead = new SysNoticeAlreadyRead();
            $alreadyRead->sys_notice_id = $noticeId;
            $alreadyRead->user_id = Auth::id();
            $alreadyRead->save();
        } else {
            //
        }
    }

    /**
     * 事業者情報修正
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $id = Auth::id();
        $input = json_decode($request->input("user"), true);
        $model = User::find($id);
        if (Auth::user()->enterprise_id){
            $enterprise_model = Enterprise::find(Auth::user()->enterprise_id);
        }else{
            $enterprise_model = Enterprise::find(Auth::user()->coop_enterprise_id);
        }
        $attributes = $input['enterprise'];
        $managerName = '';
        $managerId = 0;
        foreach ($attributes as $attrK => $attrV) {
            if ($attrK == 'user') {
                foreach ($attrV as $resK => $resV) {
                    if ($resK == 'name') {
                        $managerName = $resV;
                    }
                    if ($resK == 'id') {
                        $managerId = $resV;
                    }
                }
            }
        }
        $model->name = $attributes['last_name'] . $attributes['first_name'];
        $model->last_name = $attributes['last_name'];
        $model->first_name = $attributes['first_name'];
        $u = $model->detailValidate();
        $vFile = $model->validateImageFile($request);

        $enterprise_model->fill($attributes);

        $en = $enterprise_model->validate();

        if (!$u->fails() && !$en->fails() && !$vFile->fails()) {
            DB::beginTransaction();
            try {
                $manager = User::find($managerId);
                $manager->name = $managerName;
                $uploadImg = Common::upload($request, 'users');
                if ($uploadImg) $model->file = $uploadImg;
                $enterprise_model->update();
                $model->update();
                $manager->update();
                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
                //データベースエラー
                $error = trans('messages.error.insert');
                return $this->error($e, [$error]);
            }
        }
        $errors = array_merge($u->errors()->all(), $en->errors()->all(), $vFile->errors()->all());
        return $this->json($errors);
    }


    /**
     * メール変更して確認
     * 0=>ok,  1=>other error 2=>password error
     * @param Request $request
     * @return string
     */
    public function editMail(Request $request)
    {
        //入力 確認コード
        $user = Auth::user();
        $userModel = new User();
        $attr = $request->get("user");
        $userModel->email = $this->isEmpty('email', $attr);
        $userVal = $userModel->mailValidate();
        $hold_time = config('web.cache.email_verification.expiration_minutes');
        if (!$userVal->fails()) {
            $to = $request->get('user')['email'];
            $email = $user->email;
            if($to === $email){
                return $this->json($userVal->errors()->all());
            }
            $data = Str::random(50);
            try {
                Mail::to($to)->send(new ContactLink($data));
            } catch (\Exception $e) {
                Cache::forget(Auth::id());
                $error=trans('messages.error.insert');
                return $this->error($e,[$error]);
            }
            if (!Cache::has(("newemail" . $data))) {
                Cache::put(("newemail" . $data), ($user->id . ':' . $attr['email']), $hold_time * 60);
            }
        }
        return $this->json($userVal->errors()->all());
    }


    /**
     * メールに認証キーを取得
     * @param Request $request
     * @return string
     */
    public function getAuthKey(Request $request)
    {
        $to = $request->post('email');
        $auth_key = Str::random(6);
        $hold_time = config('web.cache.email_verification.expiration_minutes');
        Cache::put('auth_key'.$to, $auth_key, $hold_time * 60);
        Mail::to($to)->send(new AdminAuthKeySend($auth_key));
        return $this->json();
    }

    /**
     * 事業者登録
     * @param Request $request
     * @return string
     */
    public function store(Request $request)
    {
        $email=$request->post('user')['email'];
        //catchに'auth_key'を取得
        $auth_key = Cache::get('auth_key'.$email);
        //入力の認証キー
        $emailKey = $request->post('emailKey');

        $invitationFromUserId = $request->post('invitationFromUserId');
        $invitationToken = $request->post('invitationToken');
        //catchに'auth_key'を取得の場所
        if (!empty($auth_key)) {
            //catchにに取得の'auth_key'＝入力の認証キー時
            if (strtoupper($emailKey) === strtoupper($auth_key)) {
                try {
                    $userModel = new User();
                    $userModel->fill($request->post("user"));
                    //[パスワード再入力]は空の場合
                    if (empty($request->post("enterprise")['password_confirmation'])) {
                        $userModel->password_confirmation = null;
                    } else {
                        $userModel->password_confirmation = $request->post("enterprise")['password_confirmation'];
                    }
                    //入力のuser情報を検証
                    $uValidate = $userModel->validate();

                    $enterprise = new enterprise();
                    $enterprise->fill($request->post("enterprise"));
                    //入力のenterprise情報を検証
                    $eValidate = $enterprise->validate();

                    if (!$uValidate->fails() && !$eValidate->fails()) {
                        DB::beginTransaction();
                        $user = new User();
                        $user->name = $userModel->last_name . $userModel->first_name;
                        $user->last_name = $userModel->last_name;
                        $user->first_name = $userModel->first_name;
                        $user->email = $userModel->email;
                        $user->enterprise_admin = "1";
                        $user->worker = "0";
                        $user->password = bcrypt($userModel->password);
                        $user->save();

                        $enterprise->user_id = $user->id;
                        $enterprise->save();
                        $user->enterprise_id = $enterprise->id;
                        $user->save();
                        $userID = $user->id;
                        //session(['userId' => $userID]);
                        $userLogin = User::query()->where('id',$userID)->first();

                        //招待 事業協力会社  周 改修start
                        if ($invitationFromUserId != null && $invitationToken != null && $invitationFromUserId != '' && $invitationToken != ''
                        && $invitationFromUserId != 'undefined' && $invitationToken != 'undefined'){
                            $enterpriseParticipant = EnterpriseParticipant::query()
                                ->where('created_by',$invitationFromUserId)
                                ->where(function($query) use ($invitationToken){
                                    $query->where('invitation_code',$invitationToken);
                                })->get();
                            if (!empty($enterpriseParticipant)) {
                                //リンクの認証コードを確認して
                                foreach ($enterpriseParticipant as $item) {
                                    //事業協力会社テーブルに承認する（AGREE＝0から1に変更）
                                    if ($item->agree == 0) {
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
                                    }else{
                                        //認証agree = 1の場合
                                        DB::commit();
                                        Auth::login($userLogin);
                                        return 'inviteNo';
                                    }
                                }
                                DB::commit();
                                Auth::login($userLogin);
                                return 'inviteOk';
                            }else{
                                //リンクの認証コードエラーの場合
                                DB::commit();
                                Auth::login($userLogin);
                                return 'inviteNo';
                            }
                        }
                        //招待 事業協力会社  周 改修end
                        DB::commit();
                        Auth::login($user);
                    }
                } catch (\PDOException $e) {
                    DB::rollBack();
                    //データベースエラー
                    return $this->error($e);
                }
            } else {
                //いれの認証キーは間違い
                return $this->json([trans('messages.error.auth')]);
            }
        } else {
            return $this->json([trans('messages.error.emailKeyOut')]);
        }
        $errors = array_merge($uValidate->errors()->all(), $eValidate->errors()->all());
        if(!$errors){
            Cache::forget('auth_key'.$email);
        }
        return $this->json($errors);
    }

    /**
     * 登録完了,TOPページへ遷移する
     * @return object
     */
    public function loginIn()
    {
        $userId = session('userId', 'default');
        try {
            $user = User::find(intval($userId));
            Auth::login($user);
            return $user;
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            return $this->error($e);
        }
    }

    /**
     * 空かどうかを確認
     * @param $key
     * @param $model
     * @return Object|null
     */
    private function isEmpty($key, $model)
    {
        if (empty($model)) {
            $model = null;
        } else {
            if (array_key_exists($key, $model)) {
                $model = $model[$key];
            } else {
                $model = null;
            }
        }
        return $model;
    }
    //データ容量状況
    public function  enterpriseContain(){
        $enterprise_id=Auth::user()->enterprise_id;
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
}
