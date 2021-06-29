<?php

namespace App\Http\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Http\Facades\Common;
use App\Models\Browse;
use App\Models\ChatContact;
use App\Models\ChatGroup;
use App\Models\ChatList;
use App\Models\Enterprise;
use App\Models\EnterpriseParticipant;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        return view('index');
    }

    /**
     * メールが確認されていません
     * @param $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function verifiedEmail($token)
    {

        if (Cache::has("newemail" . $token) and strlen($token) == 50) {
            try {
                DB::beginTransaction();
                $var = Cache::pull("newemail" . $token);
                $token_arr = explode(':', $var);
                $id = $token_arr[0];
                $email = $token_arr[1];
                $user = User::find(intval($id));
                $user->email = $email;
                Auth::login($user);
                $user->update();

                //協力会社一覧や職人一覧にもメールアドレスの変更が
                //反映される必要がありますので、改修
                EnterpriseParticipant::where('user_id',intval($id))
                    ->update(['email' => $email]);
                ChatContact::where('to_user_id',intval($id))
                    ->update(['email' => $email]);

                DB::commit();
                return redirect('/');
            } catch (\PDOException $e) {
                DB::rollBack();
                //データベースエラー
                return $this->verification($token);
            }
        } else {
            return $this->verification($token);
        }
    }

    /**
     * invitationメールが確認されていません
     * @param $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function verifiedInvitation($token)
    {
        if (Cache::has("inviteMail" . $token) and strlen($token) == 30) {
            try {
                $var = Cache::get("inviteMail" . $token);
                $token_arr = explode(':', $var);
                $id = $token_arr[0];//送信者ID
                $email = $token_arr[1];//受信者email
                $participant = EnterpriseParticipant::where('created_by', $id)->where('email', $email)
                    ->where('invitation_code', $token)->get()->toArray();
                if (count($participant)) {
                    $usr = User::where('email', $email)->with(['enterprise','enterpriseCoop'])->first();
                    if (!empty($usr)) {
                        try {
                            DB::beginTransaction();
                            $query = EnterpriseParticipant::find($participant[0]['id']);
                            $query->agree = 1;
                            $query->save();
                            if ($usr->worker && !$usr->coop_enterprise_id && !$usr->enterprise_id){
                                $enterprise = new Enterprise();
                                $enterprise->user_id = $usr->id;
                                $enterprise->cooperator = 1;
                                $enterprise->save();

                                $usr->coop_enterprise_id = $enterprise->id;
                                $usr->save();
                            }
                            $enterprisePerson = User::where('enterprise_id', $participant[0]['enterprise_id'])->get(['id', 'name'])->toArray();
                            $midRes = [];
                            foreach ($enterprisePerson as $person){
                                $midRes[] = $person['id'];
                            }
                            $chatUserArr = ChatGroup::whereHas('group', function ($q) {
                                $q->where('kind', 1);
                            })->whereHas('mine', function ($q) use ($usr) {
                                $q->where('user_id', $usr->id)->where('admin', 1);
                            })->whereIn('user_id', $midRes)->where('admin', 1)->get('user_id')->toArray();
                            $res = [];

                            foreach ($chatUserArr as $user){
                                $res[] = $user['user_id'];
                            }
                            $dashboard = new DashboardController();
                            $usrRes = $usr->toArray();
                            if ($usrRes['enterprise']){
                                $usrRes['company'] = $usrRes['enterprise']['name'];
                            }elseif ($usrRes['coop_enterprise_id']){
                                $usrRes['company'] = $usrRes['enterprise_coop']['name'];
                            }else{
                                $usrRes['company'] = $usrRes['company_name'];
                            }
                            $enterpriseIds = User::where('id',$id)->pluck('enterprise_id')->toArray();
                            $userRes = User::where('enterprise_id',$enterpriseIds[0])->pluck('id')->toArray();
                            foreach ($userRes as $p){
                                if ($usrRes['company']) {
                                    $dashboard->addDashboard($p,6,
                                        $usrRes['name'].'（'.$usrRes['company'].'）が承認されました。',
                                        '',$p);
                                } else {
                                    $dashboard->addDashboard($p,6,
                                        $usrRes['name'].'が承認されました。',
                                        '',$p);
                                }
                            }
                            foreach ($enterprisePerson as $p) {
                                if (!in_array($p['id'],$res)) {
                                    $group = new Group();
                                    $group->name = $p['name'];
                                    $group->kind = 1;
                                    $group->save();

                                    $chatMineGroup = new ChatGroup();
                                    $chatMineGroup->user_id = $p['id'];
                                    $chatMineGroup->group_id = $group->id;
                                    $chatMineGroup->admin = 1;
                                    $chatMineGroup->save();

                                    $chatMineGroup = new ChatGroup();
                                    $chatMineGroup->user_id = $usr->id;
                                    $chatMineGroup->group_id = $group->id;
                                    $chatMineGroup->admin = 1;
                                    $chatMineGroup->save();

                                    $chatMineGroup = new ChatList();
                                    $chatMineGroup->owner_id = $usr->id;
                                    $chatMineGroup->user_id = $p['id'];
                                    $chatMineGroup->group_id = $group->id;
                                    $chatMineGroup->save();

                                    $chatMineGroup = new ChatList();
                                    $chatMineGroup->owner_id = $p['id'];
                                    $chatMineGroup->user_id = $usr->id;
                                    $chatMineGroup->group_id = $group->id;
                                    $chatMineGroup->save();
                                }
                            }
                            DB::commit();
                            Cache::forget("inviteMail" . $token);
                            Auth::login($usr);
                            return redirect('/#/invite/ok');
                        } catch (\PDOException $e) {
                            DB::rollBack();
                            return redirect('/#/invite/not');
                        }
                    } else {
                        $user = User::where('id', $id)->first();
                        if (!$user->enterprise_id){
                            $user->enterprise_admin = '1';
                            $user->save();
                        }
                    }
                    return redirect('pub/#/invite/cooperator/register/' . $token);
                } else {
                    return $this->verification($token);
                }
            } catch (\PDOException $e) {
                DB::rollBack();
                //データベースエラー
                return $this->verification($token);
            }
        } else {
            return $this->verification($token);
        }
    }

    public function zipCloud(Request $request)
    {
        return Common::zipCloud($request);
    }

    /**
     * ファイルアップロード
     * @param Request $request
     * @param $type =users,groups,projects,group_id
     * @return mixed
     */
    public function upload(Request $request, $type)
    {
        return Common::upload($request, $type);
    }

    /**
     * ファイル表示
     * @param $type =users,groups,projects,group_id
     * @param $file_name
     */
    public function getFile(Request $request, $type, $file_name)
    {
        return Common::getFile($type, $file_name);
    }

    /**
     * ファイルダウンロード
     * @param $type =users,groups,projects,group_id
     * @param $file_name
     */
    public function download($type, $file_name)
    {
        return Common::getFile($type, $file_name, true);
    }

    /**
     * 検証ジャンプ
     * @param $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function verification($token)
    {
        if (Auth::id()) {
            return redirect('/#/error');
        }
        Cache::forget(("newemail" . $token));
        return redirect('/login');
    }

    //使用頻度
    public function setBrowse(){

        $enterprise_id=Auth::user()->enterprise_id;
        if($enterprise_id){

        $id=request('id');
        $model=Browse::where('enterprise_id',$enterprise_id)->first();
        if($model){
            $model->$id=$model->$id+1;
        }else{
            $model=new Browse();
            $model->$id=1;
            $model->enterprise_id=$enterprise_id;
        }
        $model->save();
        }
        return $this->json('', 'success');
    }

}
