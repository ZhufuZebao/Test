<?php
/**
 * 工程管理のコントローラー
 *
 * @author LiYanlin
 */

namespace App\Http\Controllers\Web;

use App\Http\Facades\Common;
use App\Models\Account;
use App\Models\ChatContact;
use App\Models\ChatGroup;
use App\Models\ChatLastRead;
use App\Models\ChatList;
use App\Models\ChatMessage;
use App\Models\ChatNice;
use App\Models\ChatPerson;
use App\Models\ChatTask;
use App\Models\CustomerOffice;
use App\Models\Enterprise;
use App\Models\Dashboard;
use App\Models\EnterpriseParticipant;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectCustomer;
use App\Models\ProjectHospital;
use App\Models\ProjectLocaleChief;
use App\Models\ProjectParticipant;
use App\Models\ProjectSearchModel;
use App\Models\ProjectTradesChief;
use App\Models\User;
use App\Models\UserStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Node;
use App\Models\NodeLog;

class ProjectController extends \App\Http\Controllers\Controller
{
    private $pagination = 12;
    private $keyword;
    private $progressStatus;

    const STORAGE_TYPE = 4;

    /* 案件管理一覧
     * select project
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $proModel = Project::where('created_by', Auth::id())->get('id')->toArray();
        if (count($proModel)){
            $proArr = [];
            foreach ($proModel as $pro) {
                $proArr[] = $pro['id'];
            }
            $participants = ProjectParticipant::where('user_id', Auth::id())->whereIn('project_id', $proArr)
                ->get('project_id')->toArray();
            $participantArr = [];
            foreach ($participants as $participant) {
                $participantArr[] = $participant['project_id'];
            }
            $proModel = array_diff($proArr, $participantArr);
            DB::beginTransaction();
            try {
                foreach ($proModel as $pro) {
                    $partner = new ProjectParticipant();
                    $partner->project_id = $pro;
                    $partner->user_id = Auth::id();
                    $partner->save();
                }
                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
        }
        //#2796【案件】他社社員ユーザを職人登録した際に、案件情報を見えないように変更する
        $userArr=$this->getChatContact();
        $user = Auth::user();
        $this->keyword = Common::escapeDBSelectKeyword($request->get("keyword"));
        $this->progressStatus = $request->get("progressStatus");
        $query = Project::with(['projectLocaleChief'])
            ->where(function ($q) {
                // 作成者と共有者
                $q->where('created_by', Auth::id());
                $q->orWhereHas('projectParticipant', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            })->whereNotIn('created_by',$userArr);
        //【案件】共有者リストと、連絡先と、アプリの案件のグループチャットのメンバーと、表示される内容が違う　＃1860
        //---start---
        $projModel = $query->pluck('id')->toArray();
        //他の案件共有者の案件id取得
        $projectParticipantsProjIds = $this->getProjectIdWhereProjectParticipantsIsChatContact($projModel);
        $query->whereNotIn('id',$projectParticipantsProjIds);
        //---end---

        if (strlen($this->keyword) > 0) {
            $query->where(function ($q) {
                $q->where('place_name', 'LIKE', "%{$this->keyword}%");
                //#2790 remove construction_name in project
//                $q->orWhere('construction_name', 'LIKE', "%{$this->keyword}%");
                $q->orWhere('address', 'LIKE', "%{$this->keyword}%");
                $q->orWhere('project_no', 'LIKE', "%{$this->keyword}%");
            });
        }
        if ($this->progressStatus) {
            $query->where('progress_status', $this->progressStatus);
        }

        $query->orderBy('updated_at','desc');
        $models = $query->paginate($this->getPagesize($this->pagination));

        if ($user->enterprise_id){
            $enterpriseModel = Enterprise::where('id',$user->enterprise_id)->first()->toArray();
        } else{
            $enterpriseModel = [];
            $enterpriseModel['plan'] = 0;
        }

        return ['model' => $models, 'enterpriseId' => $user->enterprise_id, 'enterprisePlan' => $enterpriseModel['plan']];
    }
    /**
     *職人参加者としての案件
     */
    public function getChatContact(){
        $userArr=[];
        //職人に招待して
        $chatContact=ChatContact::where('to_user_id',Auth::id())->where('contact_agree',1)->pluck('from_user_id')->toArray();
        foreach ($chatContact as $value){
            $user=User::where('id',$value)->first();
            if(!$user->enterprise_id){
                $userArr[]=$value;
            }else{
                if($user->enterprise_id==Auth::user()->enterprise_id){
                    $userCombine=[];
                }else{
                    $userCombine=User::where('enterprise_id',$user->enterprise_id)->pluck('id')->toArray();
                }
                if(!$userArr){
                    $userArr=$userCombine;
                }else{
                    $userArr=array_merge($userArr,$userCombine);
                }
            }
        }

        //------------start------------
        //自分が協力会社で会社に招待　user_idを取得
        $inviteUserIds = $this->getSelfInviteEnterpriseId();
        //上の配列との比較フィルタ
        $userArr=array_diff($userArr,$inviteUserIds);
        //re index
        array_values($userArr);
        //------------end------------
        return $userArr;
    }
    /**
     * 協力に招待の会社のユーザーIDを取得
     * @return mixed
     */
    private function getSelfInviteEnterpriseId(){
        $inviteEnterpriseIds = EnterpriseParticipant::where('user_id',Auth::id())->where('agree',1)->pluck('enterprise_id')->toArray();
        $inviteEnterpriseIdsUnique = array_unique($inviteEnterpriseIds);
        $userIds = User::whereIn('enterprise_id',$inviteEnterpriseIdsUnique)->pluck('id')->toArray();
        return $userIds;
    }

    /**
     * 【案件】共有者リストと、連絡先と、アプリの案件のグループチャットのメンバーと、表示される内容が違う　＃1860
     * @param $projectIdsArr
     *
     * @return array
     */
    public function getProjectIdWhereProjectParticipantsIsChatContact($projectIdsArr){
        $contactProjectIdsArr = [];
        foreach ($projectIdsArr as $projId){
            $projectParticipantsArrTmp = $this->getProjectParticipants(new Request,$projId)['contactArr'];
            foreach ($projectParticipantsArrTmp as $tmpValue){
                if ($tmpValue['id'] == Auth::id()){
                    //他の案件共有者の案件id取得
                    $contactProjectIdsArr[] = $projId;
                }
            }
        }
        //自分は他の案件共有者　職人登録
        return $contactProjectIdsArr;
    }


    /**
     * 案件GoogleMapデータ取得
     * @return Project[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getMapProjectList()
    {
        $models = Project::with(['projectLocaleChief', 'user'])
            ->whereHas('projectParticipant', function ($q) {
                $q->where('user_id', Auth::id());
            })->orWhere('created_by',Auth::id())->get();
        return $models;
    }

    /**
     *チェットFileの一覧とファイル名検索を取得
     */
    public function getFileList(Request $request)
    {
        $user = Auth::user();
        $projectId = $request->get("projectId");
        $models = ChatMessage::with('user')->whereHas('project', function ($q) use ($user, $projectId) {
            $q->where('enterprise_id', $user->enterprise_id)->where('id', $projectId);
        })->where('file_name', '!=', '');
        return $models->get();
    }

    /**
     * チャットの記録を調べます
     * @param Request $request
     * @return mixed
     */
    public function getChatMessage(Request $request)
    {
        $models = ChatMessage::with(['user.enterprise' => function ($q) {
            $q->withTrashed();
        }
            , 'user' => function ($q) {
                $q->withTrashed();
            }])->with(['user.coopEnterprise' => function ($q) {
            $q->withTrashed();
        }
            , 'user' => function ($q) {
                $q->withTrashed();
            }])->where('group_id', '=', $request->get('groupId'))->orderBy('created_at','desc')->get();
        return $models;
    }


    /**
     * 共有者リストを检索
     * @param Request $request
     * @return mixed
     */
    public function getProjectParticipants(Request $request,$projectId=null)
    {
        if ($request->get('projectId')) {
            $projId = $request->get('projectId');
        } else {
            $projId = $projectId;
        }
        $models = projectParticipant::where('project_id', $projId)->get('user_id')->toArray();
        $idArr=[];
        $userId = Auth::id();
        $user = Auth::user();
        foreach ($models as $m){
            $idArr[]=$m['user_id'];
        }
        $createBy=Project::where('id',$projId)->get('created_by')->toArray();
        $enterpriseId=User::where('id',$createBy[0]['created_by'])->withTrashed()->get('enterprise_id')->toArray();
        //協力会社ユーザでも共有者を追加
        $count=EnterpriseParticipant::where('enterprise_id',$enterpriseId[0]['enterprise_id'])->where('user_id',Auth::id())->count();
        if (Auth::user()->enterprise_id != $enterpriseId[0]['enterprise_id']&&!$count) {
            $addMember=false;
        }else{
            $addMember=true;
        }
//        if ($enterpriseId && Auth::user()->enterprise_id !=$enterpriseId[0]['enterprise_id']){
//            $res=User::whereIn('id',$idArr)->with('enterprise','enterpriseCoop')
//                ->get(['id', 'name', 'file','enterprise_id','email','coop_enterprise_id']);
//            return ['otherPerson' => $res,'userId'=>$userId,'addMember'=>$addMember];
//        }

        $createdByModel = User::where('id',$createBy[0]['created_by'])->withTrashed()->first()->toArray();
        $usersArr = [];
        if ($createdByModel['enterprise_id']){
            $usersArr = User::where('enterprise_id', $createdByModel['enterprise_id'])->withTrashed()->get('id');
        } elseif ($createdByModel['coop_enterprise_id']) {
            $usersArr = User::where('coop_enterprise_id', $createdByModel['coop_enterprise_id'])->withTrashed()->get('id');
        }
        $res = [];
        foreach ($usersArr as $userArr) {
            $res[] = $userArr['id'];
        }
        $contactArr = ChatContact::whereIn('from_user_id', $res)->where('contact_agree', '=', 1)
            ->pluck('to_user_id')->toArray();
        $enterpriseParticipants = EnterpriseParticipant::where('enterprise_id', $createdByModel['enterprise_id'])
        ->where('agree', 1)->pluck('user_id')->toArray();
        $users = Account::where('enterprise_id', $createdByModel['enterprise_id'])
            ->get('id')->toArray();
        $enterpriseArr=[];
        foreach ($users as $u) {
            $enterpriseArr[] = $u['id'];
        }

        $enterpriseArr=array_intersect($idArr,$enterpriseArr);
        $participantsArr=array_intersect($idArr,$enterpriseParticipants);
//        $contactArr=array_intersect($idArr,$contactArr);
        $contactArr=array_diff($idArr,$enterpriseArr,$participantsArr);

        //協同組合である職人を外す
        $contactArr=array_values($contactArr);
        $conUid=[];
        foreach ($contactArr as $key =>$val){
            $conUid[]=$val;
        }
        foreach ($participantsArr as $key =>$val){
            if(in_array($val,$conUid)){
                $uidKey = array_search($val,$conUid);
                unset($contactArr[$uidKey]);
            }
        }
        foreach ($enterpriseArr as $key =>$val){
            if(in_array($val,$conUid)){
                $uidKey = array_search($val,$conUid);
                unset($contactArr[$uidKey]);
            }
        }
        //他の
//        $other=array_diff($idArr, $enterpriseArr,$participantsArr,$contactArr);
        $contactArr=User::whereIn('id',$contactArr)->with('enterprise')->get(['id', 'name', 'file','enterprise_id','email'])->toArray();
        $participantsArr=User::whereIn('id',$participantsArr)->with('enterprise','enterpriseCoop')->get(['id', 'name', 'file','enterprise_id','coop_enterprise_id','email'])->toArray();
        $enterpriseArr=User::whereIn('id',$enterpriseArr)->with('enterprise')->get(['id', 'name', 'file','enterprise_id','email'])->toArray();
//        $otherArr=User::whereIn('id',$other)->with('enterprise')->get(['id', 'name', 'file','enterprise_id','email'])->toArray();
        return ['participantsArr' => $participantsArr, 'enterpriseArr' => $enterpriseArr, 'contactArr' => $contactArr,
//                'otherArr'=>$otherArr,
            'userId'=>$userId,'enterpriseId'=>Auth::user()->enterprise_id,'addMember'=>$addMember];
    }

    /**
     * 共有者リストを削除
     * @param Request $request
     * @return mixed
     */
    public function delProjectParticipant(Request $request)
    {
        DB::beginTransaction();
        try {
            ProjectParticipant::where('project_id', $request->post('projectId'))->where('user_id', $request->post('userId'))->delete();
            ChatGroup::where('group_id', $request->post('projectGroupId'))->where('user_id', $request->post('userId'))->delete();
            ChatList::where('group_id',$request->post('projectGroupId'))->where('owner_id', $request->post('userId'))->delete();
            $childGroupArr=Group::where('parent_id',$request->post('projectGroupId'))->get('id')->toArray();
            foreach ($childGroupArr as $c){
                ChatGroup::where('group_id', $c['id'])->where('user_id', $request->post('userId'))->delete();
                ChatList::where('group_id',$c['id'])->where('owner_id', $request->post('userId'))->delete();
            }
            $dashboard = new DashboardController();
            $dashboardDelMag = trans('messages.dashboardProject.deletePartner');
            $dashboardEditMag = trans('messages.dashboardProject.editPartner');
            $proPartnerArr = ProjectParticipant::where('project_id',$request->get("projectId"))->get()->toArray();
            $proArr = Project::find($request->get("projectId"));
            if (Auth::id() != $request->post('userId')){
                $dashboard->addDashboard($request->post("projectId"),4,$proArr->place_name,
                    ($proArr->place_name).'、'.$dashboardDelMag,Auth::id(),$request->post('userId'));
            }

            // 会社の職人except
            // [ドキュメント]招待された職人ユーザーに招待元の企業のドキュメントを表示しない　＃3134
            $chatContactUserIds = $this->getEnterpriseChatContact();

            foreach ($proPartnerArr as $item){
                if (Auth::id() != $item['user_id'] && $item['user_id'] != $request->post('userId') && !in_array($item['user_id'],$chatContactUserIds)){
                    $dashboard->addDashboard($request->post("projectId"),4,$proArr->place_name,
                        ($proArr->place_name).'、'.$dashboardEditMag,Auth::id(),$item['user_id']);
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return $e;
        }
    }

    //会社の職人を取得
    private function getEnterpriseChatContact(){

        $accountIdsArr = $this->getAccountUsers();

        $chatContactIdsArr = DB::table("chatcontacts")
            ->whereIn('from_user_id', $accountIdsArr)
            ->where('contact_agree', 1)
            ->pluck('to_user_id');

        $result = [];
        foreach ($chatContactIdsArr as $item){
            $result[] = $item;
        }

        //自分会社の職人 アップグレード
        $accountIds = DB::table("users")
            ->where('enterprise_id', Auth::user()->enterprise_id)
            ->whereNull('deleted_at')
            ->pluck('id');
        $accountIdsArray = [];
        foreach ($accountIds as $item){
            $accountIdsArray[] = $item;
        }

        $res = array_diff($result,$accountIdsArray);

        return $res;
    }

    //get all users of the enterprise the project belongs
    private function getAccountUsers() {
        $enterpriseId = Auth::user()->enterprise_id;
        $accounts = DB::table("users")
            ->where('enterprise_id', $enterpriseId)
            ->whereNull('deleted_at')
            ->select(['id'])
            ->get();
        $accountIds = [];
        foreach($accounts as $account) {
            $accountIds[] = $account->id;
        }
        return $accountIds;
    }

    /**
     * 協力会社一覧取得
     * @param Request $request
     * @return mixed
     */
    public function getParticipants(Request $request)
    {
        $contactArr = [];
        $enterpriseArr = [];
        $participantsArr = [];
        $userId = Auth::id();
        $words = $request->input('words');
        $projectId = $request->input('projectId');
        $user = Account::where('id', $userId)->first();

        $usersArr = [];
        if (Auth::user()->enterprise_id){
            $usersArr = User::where('enterprise_id', Auth::user()->enterprise_id)->withTrashed()->pluck('id')->toArray();
        } elseif (Auth::user()->coop_enterprise_id) {
            $usersArr = User::where('coop_enterprise_id', Auth::user()->coop_enterprise_id)->withTrashed()->pluck('id')->toArray();
        }

        $toUser = ChatContact::whereIn('from_user_id', $usersArr)->where('contact_agree', '=', 1)
            ->pluck('to_user_id')->toArray();
        //$fromUser= ChatContact::where('to_user_id', Auth::id())->where('contact_agree', '=', 1)->pluck('from_user_id')->toArray();
        //$contactArr = array_values(array_unique(array_merge($toUser, $fromUser)));
        $contactArr=$toUser;
        $enterpriseParticipants = EnterpriseParticipant::where('enterprise_id', $user->enterprise_id)
            ->where('agree', 1)->get('user_id')->toArray();;
        $users = Account::where('enterprise_id', $user->enterprise_id)->whereNotNull('enterprise_id')
            ->where('id', '!=', $userId)->get('id')->toArray();

        $groups = ProjectParticipant::where('project_id', $projectId)->get('user_id')->toArray();



        foreach ($enterpriseParticipants as $participant) {
            $participantsArr[] = $participant['user_id'];
        }
        foreach ($users as $u) {
            $enterpriseArr[] = $u['id'];
        }
        foreach ($groups as $group) {
            $userId = $group['user_id'];
            if (in_array($userId, $participantsArr)) {
                $k = array_keys($participantsArr, $userId, true)[0];
                array_splice($participantsArr, $k, 1);
            }
            if (in_array($userId, $enterpriseArr)) {
                $k = array_keys($enterpriseArr, $userId, true)[0];
                array_splice($enterpriseArr, $k, 1);
            }
            if (in_array($userId, $contactArr)) {
                $k = array_keys($contactArr, $userId, true)[0];
                array_splice($contactArr, $k, 1);
            }
        }

        if ($words) {
            $participantsArr = User::whereIn('id', $participantsArr)->where('name', 'LIKE', "%{$words}%")
                ->with('enterprise')
                ->get(['id', 'name', 'file','enterprise_id']);
            $enterpriseArr = User::whereIn('id', $enterpriseArr)->where('name', 'LIKE', "%{$words}%")
                ->with('enterprise')->whereNotNull('enterprise_id')
                ->get(['id', 'name', 'file','enterprise_id']);
            $contactArr = User::whereIn('id', $contactArr)->where('name', 'LIKE', "%{$words}%")
                ->with('enterprise')
                ->get(['id', 'name', 'file','enterprise_id']);
        } else {
            $participantsArr = User::whereIn('id', $participantsArr)->with('enterprise')->get(['id', 'name', 'file','enterprise_id']);
            $enterpriseArr = User::whereIn('id', $enterpriseArr)->with('enterprise')->get(['id', 'name', 'file','enterprise_id']);
            $contactArr = User::whereIn('id', $contactArr)->with('enterprise')->get(['id', 'name', 'file','enterprise_id']);
        }
        foreach ($contactArr as $key => $value){
            if($value->enterprise_id && Auth::user()->enterprise_id && $value->enterprise_id == Auth::user()->enterprise_id){
                unset($contactArr[$key]);
            }
        }

        //協力会社 can only add self 職人 to the ProjectParticipant
        $projModel = Project::where('id',$projectId)->first()->toArray();
        $projCreatedBy = User::where('id',$projModel['created_by'])->withTrashed()->first()->toArray();
        if (Auth::user()->enterprise_id !== $projCreatedBy['enterprise_id']) {
            $participantsArr = [];
            $enterpriseArr = [];
        }
        return ['participantsArr' => $participantsArr, 'enterpriseArr' => $enterpriseArr, 'contactArr' => $contactArr];
    }

    /**
     * 案件管理一覧「施主詳細情報」
     * @param Request $request
     * @return CustomerOffice[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function showCustomerOffice(Request $request)
    {
        $customer_office_id = $request->get('customer_office_id');
        $customerOffice = customerOffice::with(['people.customerRole', 'billings'])
            ->get()->find($customer_office_id);
        return $customerOffice;
    }

    /**
     * 案件新規登録
     * @param Request $request
     * @return \Exception|\PDOException
     */
    public function store(Request $request)
    {
        if (!Auth::user()->enterprise_id){
            return $this->json("登録中にエラーが発生しました");
        }
        DB::beginTransaction();
        try {
            // 検証
            $errorMsg = $this->requestCheck($request);
            if ("noError" != $errorMsg) {
                return $errorMsg;
            }
            $projectBasis = json_decode($request->get("projectBasis"), true);
            $projectCompany = json_decode($request->get("projectCompany"), true);
            $projectSafety = json_decode($request->get("projectSafety"), true);
            $localeChief = json_decode($request->get("localeChief"), true);
            $tradesChief = json_decode($request->get("tradesChief"), true);
            $hospital = json_decode($request->get("hospital"), true);
            $group = new Group();
            $group->name = $projectBasis["place_name"];
            $group->kind = 0;
            $group->save();
            request()->offsetSet('group_id', $group->id);
            if (Common::upload($request, 'groups')) {
                $group->file = Common::upload($request, 'groups');
                $group->save();
            }

            $chatGroup = new ChatGroup();
            $chatGroup->group_id = $group->id;
            $chatGroup->user_id = Auth::id();
            $chatGroup->admin = 1;
            $chatGroup->save();



            $chatList = new ChatList();
            $chatList->group_id = $group->id;
            $chatList->owner_id = Auth::id();
            $chatList->top = 0;
            $chatList->save();

            $chatLastRead = new ChatLastRead();
            $chatLastRead->group_id = $group->id;
            $chatLastRead->user_id = Auth::id();
            $chatLastRead->message_id = 0;
            $chatLastRead->save();



            $model = new Project();
            //案件基本情報
            $model->fill($projectBasis);
            //管理会社・不動産屋情報
            $model->fill($projectCompany);
            //安全管理情報
            $model->fill($projectSafety);
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
            $model->enterprise_id = Auth::user()->enterprise_id;
            $model->group_id = $group->id;
            if(isset($projectBasis["telOut"])){
                $model->tel=$projectBasis["telOut"];
            }
            if(isset($projectBasis["telIn"])){
                $model->tel_in=$projectBasis["telIn"];
            }
            $model->save();

            foreach ($projectBasis['customer'] as $p){
                $projectCustomer = new ProjectCustomer();
                $projectCustomer->customer_id = $p['id'];
                if ( $p['pivot'] && $p['pivot']['office_id']){
                    $projectCustomer->office_id = $p['pivot']['office_id'];
                }
                $projectCustomer->project_id = $model->id;
                $projectCustomer->save();
            }
            $chatLastRead = new ProjectParticipant();
            $chatLastRead->project_id = $model->id;
            $chatLastRead->user_id = Auth::id();
            $chatLastRead->save();

            request()->offsetSet('project_id', $model->id);
            $vFile = $model->validateImageFile($request);
            if (!$vFile->fails()) {
                $model->subject_image = Common::upload($request, 'projects');
            }
            $model->save();

            if ($localeChief) {
                foreach ($localeChief as $locale) {
                    $localeChief = new ProjectLocaleChief();
                    $localeChief->fill($locale);
                    $localeChief->project_id = $model->id;
                    $localeChief->created_by = Auth::id();
                    $localeChief->updated_by = Auth::id();
                    $localeChief->save();
                }
            }
            if ($tradesChief) {
                foreach ($tradesChief as $trades) {
                    $tradesChief = new ProjectTradesChief();
                    $tradesChief->fill($trades);
                    $tradesChief->project_id = $model->id;
                    $tradesChief->created_by = Auth::id();
                    $tradesChief->updated_by = Auth::id();
                    $tradesChief->save();
                }
            }
            if ($hospital) {
                foreach ($hospital as $key => $hos) {
                    $hospital = new ProjectHospital();
                    $hospital->fill($hos);
                    $hospital->project_id = $model->id;
                    $hospital->created_by = Auth::id();
                    $hospital->updated_by = Auth::id();
                    $hospital->save();
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            return $this->json("登録中にエラーが発生しました");
        }
        return $this->json("", "登録しました");
    }

    /**
     * 案件共有者リストを挿入chatGroupとproject_participants
     * @param Request $request
     * @return \Exception|\PDOException
     */
    public function insertToProjectParticipants(Request $request)
    {

        DB::beginTransaction();
        try {
            // 検証
            $errorMsg = $this->requestCheck($request);
            if ("noError" != $errorMsg) {
                return $errorMsg;
            }
            $enterpriseId=Auth::user()->enterprise_id;
            $participants = json_decode($request->get("participants"), true);
            $arr=User::whereIn('id',$participants)->get(['id','enterprise_id']);
            foreach ($arr as  $resValue) {
                $proPartModel = new ProjectParticipant();
                $proPartModel->project_id = $request->get('projectId');
                $proPartModel->user_id = $resValue->id;
                $proPartModel->save();
                $chatGroupModel = new ChatGroup();
                $chatGroupModel->group_id = $request->get('projectGroupId');
                $chatGroupModel->user_id = $resValue->id;
                if ($enterpriseId==$resValue->enterprise_id){
                    $chatGroupModel->admin = 1;
                }else{
                    $chatGroupModel->admin = 0;
                }
                $chatGroupModel->save();
                $chatListModel = new ChatList();
                $chatListModel->group_id = $request->get('projectGroupId');
                $chatListModel->owner_id = $resValue->id;
                $chatListModel->top = 0;
                $chatListModel->save();
            }
            $dashboard = new DashboardController();
            $proPartnerArr = ProjectParticipant::where('project_id',$request->get("projectId"))->get()->toArray();
            $proArr = Project::find($request->get("projectId"));
            $date = date('m/d H:i');

            // [ドキュメント]招待された職人ユーザーに招待元の企業のドキュメントを表示しない　＃3134
            $chatContactUserIds = $this->getEnterpriseChatContact();

            foreach ($proPartnerArr as $item){
                if (Auth::id() != $item['user_id'] && !in_array($item['user_id'],$chatContactUserIds)){
                    $dashboard->addDashboard($request->get("projectId"),4,
                        $proArr->place_name.'案件を共有されました。（'.$date.'）',
                        '',$item['user_id']);
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            return $this->json("登録中にエラーが発生しました");
        }
        return $this->json("", "登録しました");
    }

    /**
     * input check
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function requestCheck(Request $request)
    {
        $model = new Project();
        if (json_decode($request->get("project"), true)) {
            $model->fill(json_decode($request->get("project"), true));
            $validate = $model->validate();
            if ($validate->fails()) {
                return $this->json($validate->errors()->all());
            }
        }
        if ((json_decode($request->get("projectBasisData"), true))) {
            $model->fill(json_decode($request->get("projectBasisData"), true));
            $validate = $model->validate();
            if ($validate->fails()) {
                return $this->json($validate->errors()->all());
            }
        }
        if ((json_decode($request->get("projectCompanyData"), true))) {
            $model->fill(json_decode($request->get("projectCompanyData"), true));
            $validate = $model->validate();
            if ($validate->fails()) {
                return $this->json($validate->errors()->all());
            }
        }
        if ((json_decode($request->get("projectSafetyData"), true))) {
            $model->fill(json_decode($request->get("projectSafetyData"), true));
            $validate = $model->validate();
            if ($validate->fails()) {
                return $this->json($validate->errors()->all());
            }
        }

        if (json_decode($request->get("localeChief"), true)) {
            foreach (json_decode($request->get("localeChief"), true) as $key => $locale) {
                $errorMsg = $this->inputCheck($request, 'localeChief', $key);
                if ("noError" != $errorMsg) {
                    return $errorMsg;
                }
            }
        }
        if (json_decode($request->get("tradesChief"), true)) {
            foreach (json_decode($request->get("tradesChief"), true) as $key => $trades) {
                $errorMsg = $this->inputCheck($request, 'tradesChief', $key);
                if ("noError" != $errorMsg) {
                    return $errorMsg;
                }
            }
        }
        if (json_decode($request->get("hospital"), true)) {
            foreach (json_decode($request->get("hospital"), true) as $key => $hos) {
                $errorMsg = $this->inputCheck($request, 'hospital', $key);
                if ("noError" != $errorMsg) {
                    return $errorMsg;
                }
            }
        }
        return "noError";
    }

    /**
     * 案件にの進捗状況変更
     * @param Request $request
     * @return \Exception|\PDOException
     */
    public function updateProjectProgressStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $errorMsg = $this->requestCheck($request);
            if ("noError" != $errorMsg) {
                return $errorMsg;
            }
            $model = Project::find($request->get("projectId"));
            $model->updated_by = Auth::id();
            $model->progress_status = $request->get("progress_status");
            $model->update();
            $dashboard = new DashboardController();
            $proArr = ProjectParticipant::where('project_id',$request->get("projectId"))->get()->toArray();
            $date = date('m/d H:i');

            $projectParticipantUserIdsArr = [];
            foreach ($proArr as $item){
                if (Auth::id() != $item['user_id']){
                    $projectParticipantUserIdsArr[] = $item['user_id'];
                }
            }

            //Dashboard data存在
            $dashboardModel = Dashboard::where('related_id',$request->get("projectId"))
                ->whereIn('to_user_id',$projectParticipantUserIdsArr)->get()
                ->toArray();
            //project dashboard [情報が変更されました。] 取得
            $projDashboards = [];           //既存のproject dashboard
            $projDashboardToUserIds = [];   //既存のuser_id
            foreach ($dashboardModel as $dashboardItem)
            {
                if(strpos($dashboardItem['title'],'情報が変更されました。') !== false){
                    $projDashboards[] = $dashboardItem;
                    $projDashboardToUserIds[] = $dashboardItem['to_user_id'];
                }else{
                    //
                }

            }
            //array重複を削除する
            //user_ids
            $projDashboardToUserIds = array_unique($projDashboardToUserIds);

            //ダッシュボードテーブル読み取りフィールドの更新
            foreach ($projDashboards as $dashboardItem){
                $this->updateProjectDashboardReadStatus($dashboardItem['id'],$model->place_name);
            }

            // [ドキュメント]招待された職人ユーザーに招待元の企業のドキュメントを表示しない　＃3134
            $chatContactUserIds = $this->getEnterpriseChatContact();

            foreach ($proArr as $item){
                //dashboard新規
                //check:id != Auth::id() && !既存のuser_id
                if (Auth::id() != $item['user_id'] && !in_array($item['user_id'], $projDashboardToUserIds) && !in_array($item['user_id'],$chatContactUserIds)){
                    $dashboard->addDashboard($request->get("projectId"),4,
                        $model->place_name.'案件の情報が変更されました。（'.$date.'）',
                        '',$item['user_id']);
                }
            }

            DB::commit();
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            return $this->json("変更中にエラーが発生しました");
        }
        return $this->json("", "変更しました");
    }

    /**
     * 案件の情報が変更の場合 ダッシュボードテーブル読み取りフィールドの更新
     * @param $dashboard_id    ダッシュボード更新のID
     * @param $project_name    案件名
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProjectDashboardReadStatus($dashboard_id,$project_name)
    {
        $date = date('m/d H:i');
        DB::beginTransaction();
        try {
            $dashboard = Dashboard::find($dashboard_id);
            $dashboard->title = $project_name.'案件の情報が変更されました。（'.$date.'）';
            $dashboard->sort_time = time();
            $dashboard->read = 0;
            $dashboard->save();
            DB::commit();
            return $this->json("", "変更しました");
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            return $this->json("変更中にエラーが発生しました");
        }
    }

    /**
     * 案件変更
     * @param Request $request
     * @return \Exception|\PDOException
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        $arr = array();
        try {
            // 検証
            $errorMsg = $this->requestCheck($request);
            if ("noError" != $errorMsg) {
                return $errorMsg;
            }
            $project = new Project();
            $model = Project::find($request->get("id"));
            $groupModel = Group::find($model->group_id);
            $updateAllFlag = json_decode($request->get("updateAllFlag"), true);
            $projectBasis = json_decode($request->get("projectBasis"), true);
            $projectCompany = json_decode($request->get("projectCompany"), true);
            $projectSafety = json_decode($request->get("projectSafety"), true);
            $localeChief = json_decode($request->get("localeChief"), true);
            $tradesChief = json_decode($request->get("tradesChief"), true);
            $hospital = json_decode($request->get("hospital"), true);
            $proSafety = json_decode($request->get("proSafety"), true);
            $proBasis = json_decode($request->get("proBasis"), true);
            //案件基本情報と管理会社・不動産屋 情報と安全管理情報を変更
            if ($updateAllFlag) {
                if ($projectBasis) {
                    ProjectCustomer::where('project_id',$request->get("id"))->delete();
                    foreach ($projectBasis['customer'] as $p){
                        $projectCustomer = new ProjectCustomer();
                        $projectCustomer->customer_id = $p['id'];
                        if ($p['pivot'] && $p['pivot']['office_id']){
                            $projectCustomer->office_id = $p['pivot']['office_id'];
                        }
                        $projectCustomer->project_id = intval($request->get("id"));
                        $projectCustomer->save();
                    }
                    unset($projectBasis['customer']);
                    $model->fill($projectBasis);
                    $groupModel->name = $projectBasis["place_name"];
                    request()->offsetSet('group_id', $groupModel->id);
                    $vFile = $project->validateImageFile($request);
                    if (!$vFile->fails()) {
                        $uploadImg = Common::upload($request, 'groups');
                    }
                    if ($uploadImg) {
                        $groupModel->file = $uploadImg;
                    }
                }
                if ($projectCompany) {
                    $model->fill($projectCompany);
                }
                if ($projectSafety) {
                    $model->fill($projectSafety);
                }
                $model->updated_by = Auth::id();
                request()->offsetSet('project_id', $model->id);
                $vFile = $project->validateImageFile($request);
                if (!$vFile->fails()) {
                    $uploadImg = Common::upload($request, 'projects');
                }
                if ($uploadImg) {
                    $model->subject_image = $uploadImg;
                }
                $model->tel=$projectBasis["telOut"];
                $model->tel_in=$projectBasis["telIn"];
                $groupModel->update();
                $model->update();
            } else {
                //案件基本情報を変更
                if ($projectBasis) {
                    //customer
                    ProjectCustomer::where('project_id',$request->get("id"))->delete();
                    foreach ($projectBasis['customer'] as $p){
                        $projectCustomer = new ProjectCustomer();
                        $projectCustomer->customer_id = $p['id'];
                        if ($p['pivot'] && $p['pivot']['office_id']){
                            $projectCustomer->office_id = $p['pivot']['office_id'];
                        }
                        $projectCustomer->project_id = intval($request->get("id"));
                        $projectCustomer->save();
                    }
                    unset($projectBasis['customer']);
                    $model->fill($projectBasis);
                    $model->updated_by = Auth::id();
                    request()->offsetSet('project_id', $model->id);
                    $vFile = $project->validateImageFile($request);
                    if (!$vFile->fails()) {
                        $uploadImg = Common::upload($request, 'projects');
                    }
                    if ($uploadImg) {
                        $model->subject_image = $uploadImg;
                    }
                    $groupModel->name = $projectBasis["place_name"];
                    request()->offsetSet('group_id', $groupModel->id);
                    $vFile = $project->validateImageFile($request);
                    if (!$vFile->fails()) {
                        $uploadImg = Common::upload($request, 'groups');
                    }
                    if ($uploadImg) {
                        $groupModel->file = $uploadImg;
                    }
                    $model->tel=$projectBasis["telOut"];
                    $model->tel_in=$projectBasis["telIn"];
                    $groupModel->update();
                    $model->update();
                }
                //管理会社・不動産屋 情報を変更
                if ($projectCompany) {
                    $model->fill($projectCompany);
                    $model->updated_by = Auth::id();
                    $model->update();
                }
                //安全管理情報を変更
                if ($projectSafety) {
                    $model->fill($projectSafety);
                    $model->updated_by = Auth::id();
                    $model->update();
                }
            }
            if ($localeChief) {
                foreach ($localeChief as $locale) {
                    if (isset($locale['id']) && !empty($locale['id'])) {
                        $localeChief = ProjectLocaleChief::find($locale['id']);
                        $localeChief->fill($locale);
                        $localeChief->updated_by = Auth::id();
                        $localeChief->save();
                        array_push($arr, $locale['id']);
                    } else {
                        $localeChief = new ProjectLocaleChief();
                        $localeChief->fill($locale);
                        $localeChief->project_id = $model->id;
                        $localeChief->created_by = Auth::id();
                        $localeChief->updated_by = Auth::id();
                        $localeChief->save();
                        array_push($arr, $localeChief->id);
                    }
                }
            }
            if ($updateAllFlag) {
                if (count($arr) == 0) {
                    ProjectLocaleChief::where('project_id', '=', $model->id)->delete();
                } else {
                    ProjectLocaleChief::whereNotIn('id', $arr)->where('project_id', '=', $model->id)->delete();
                }
            } else {
                if ($proBasis) {
                    if (count($arr) == 0) {
                        ProjectLocaleChief::where('project_id', '=', $model->id)->delete();
                    } else {
                        ProjectLocaleChief::whereNotIn('id', $arr)->where('project_id', '=', $model->id)->delete();
                    }
                }
            }
            $arr = array();
            if ($tradesChief) {
                foreach ($tradesChief as $trades) {
                    if (isset($trades['id']) && !empty($trades['id'])) {
                        $tradesChief = ProjectTradesChief::find($trades['id']);
                        $tradesChief->fill($trades);
                        $tradesChief->updated_by = Auth::id();
                        $tradesChief->save();
                        array_push($arr, $trades['id']);
                    } else {
                        $tradesChief = new ProjectTradesChief();
                        $tradesChief->fill($trades);
                        $tradesChief->project_id = $model->id;
                        $tradesChief->created_by = Auth::id();
                        $tradesChief->updated_by = Auth::id();
                        $tradesChief->save();
                        array_push($arr, $tradesChief->id);
                    }
                }
            }
            if ($updateAllFlag) {
                if (count($arr) == 0) {
                    ProjectTradesChief::where('project_id', '=', $model->id)->delete();
                } else {
                    ProjectTradesChief::whereNotIn('id', $arr)->where('project_id', '=', $model->id)->delete();
                }
            } else {
                if ($proSafety) {
                    if (count($arr) == 0) {
                        ProjectTradesChief::where('project_id', '=', $model->id)->delete();
                    } else {
                        ProjectTradesChief::whereNotIn('id', $arr)->where('project_id', '=', $model->id)->delete();
                    }
                }
            }
            $arr = array();
            if ($hospital) {
                foreach ($hospital as $hos) {
                    if (isset($hos['id']) && !empty($hos['id'])) {
                        $hospital = ProjectHospital::find($hos['id']);
                        $hospital->fill($hos);
                        $hospital->updated_by = Auth::id();
                        $hospital->save();
                        array_push($arr, $hos['id']);
                    } else {
                        $hospital = new ProjectHospital();
                        $hospital->fill($hos);
                        $hospital->project_id = $model->id;
                        $hospital->created_by = Auth::id();
                        $hospital->updated_by = Auth::id();
                        $hospital->save();
                        array_push($arr, $hospital->id);
                    }
                }
            }
            if ($updateAllFlag) {
                if (count($arr) == 0) {
                    ProjectHospital::where('project_id', '=', $model->id)->delete();
                } else {
                    ProjectHospital::whereNotIn('id', $arr)->where('project_id', '=', $model->id)->delete();
                }
            } else {
                if ($proSafety) {
                    if (count($arr) == 0) {
                        ProjectHospital::where('project_id', '=', $model->id)->delete();
                    } else {
                        ProjectHospital::whereNotIn('id', $arr)->where('project_id', '=', $model->id)->delete();
                    }
                }
            }
            $dashboard = new DashboardController();
            $proArr = ProjectParticipant::where('project_id',$request->get("id"))->get()->toArray();
            $date = date('m/d H:i');

            $projectParticipantUserIdsArr = [];
            foreach ($proArr as $item){
                if (Auth::id() != $item['user_id']){
                    $projectParticipantUserIdsArr[] = $item['user_id'];
                }
            }

            //Dashboard data存在
            $dashboardModel = Dashboard::where('related_id',$request->get("id"))
                ->whereIn('to_user_id',$projectParticipantUserIdsArr)->get()
                ->toArray();
            //project dashboard [情報が変更されました。] 取得
            $projDashboards = [];           //既存のproject dashboard
            $projDashboardToUserIds = [];   //既存のuser_id
            foreach ($dashboardModel as $dashboardItem)
            {
                if(strpos($dashboardItem['title'],'情報が変更されました。') !== false){
                    $projDashboards[] = $dashboardItem;
                    $projDashboardToUserIds[] = $dashboardItem['to_user_id'];
                }else{
                    //
                }

            }
            //array重複を削除する
            //user_ids
            $projDashboardToUserIds = array_unique($projDashboardToUserIds);
            //ダッシュボードテーブル読み取りフィールドの更新
            foreach ($projDashboards as $dashboardItem){
                $this->updateProjectDashboardReadStatus($dashboardItem['id'],$groupModel->name);
            }

            // [ドキュメント]招待された職人ユーザーに招待元の企業のドキュメントを表示しない　＃3134
            $chatContactUserIds = $this->getEnterpriseChatContact();

            foreach ($proArr as $item){
                //dashboard新規
                //check:id != Auth::id() && !既存のuser_id
                if (Auth::id() != $item['user_id'] && !in_array($item['user_id'], $projDashboardToUserIds) && !in_array($item['user_id'],$chatContactUserIds)){
                    $dashboard->addDashboard($request->get("id"),4,
                        $groupModel->name.'案件の情報が変更されました。（'.$date.'）',
                        '',$item['user_id']);
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            return $this->json("変更中にエラーが発生しました");
        }
        return $this->json("", "変更しました");
    }

    public function getImageUrl(Request $request)
    {
        $imageUrl = "";
        $uploadImg = $request->imgUrl;
        if ($uploadImg) {
            $base64_body = substr(strstr($uploadImg, ','), 1);
            $img = base64_decode($base64_body);
            $path = base_path() . Project::IMAGE_PATH;
            if (!file_exists($path)) {
                mkdir($path, 0777);
            }
            $url = date('YmdHis', time()) . rand(100000, 999999) . '.jpg';
            $filename = $path . $url;
            if (file_put_contents($filename, $img, FILE_APPEND)) {
                $imageUrl = $url;
            } else {
                fopen($filename, 'a+');
                unlink($filename);
            }
        }
        return $imageUrl;
    }

    public function getProject(Request $request)
    {
        $model = Project::with(['user' => function($query){
            $query->withTrashed();
        },'userUpdateBy'=> function($query){
            $query->withTrashed();
        }])->where('id', '=', $request->get('id'))->first();
        $enterpriseId=Auth::user()->enterprise_id;
        return ['model'=>$model,'enterpriseId'=>$enterpriseId];
    }

    public function getProjectAndCustomer(Request $request)
    {
        $models = Project::with(['customerOffice'])
            ->with([
                'customer' => function ($q) {
                    $q->whereNull('projects_customers.deleted_at');
                }
            ])->where('id', '=', $request->get('id'))->first()->toArray();
        foreach ($models['customer'] as $model){
            foreach ($models['customer_office'] as $k => $office){
                if($office['deleted_at']){
                    unset($models['customer_office'][$k]);
                }else{
                    if ($model['pivot']['office_id'] == $office['id']){
                        if ($office['name']){
                            $models['customer_office'][$k]['name'] = $model['name'].' '.$office['name'];
                        }else{
                            $models['customer_office'][$k]['name'] = $model['name'];
                        }
                    }
                }
            }
        }
        return $models;
    }

    public function show(Request $request)
    {
        $models = Project::with(['projectLocaleChief', 'projectTradesChief', 'projectHospital','customerOffice.people'])
            ->with([
                'customer' => function ($q) {
                    $q->whereNull('projects_customers.deleted_at');
                }
            ])
            ->where('id', '=', $request->get('id'))->get()->toArray();
        foreach ($models[0]['customer'] as $model){
            foreach ($models[0]['customer_office'] as $k => $office){
                if($office['deleted_at']){
                    unset($models[0]['customer_office'][$k]);
                }else{
                    if ($model['pivot']['office_id'] == $office['id']){
                        if ($office['name']){
                            $models[0]['customer_office'][$k]['name'] = $model['name'].' '.$office['name'];
                        }else{
                            $models[0]['customer_office'][$k]['name'] = $model['name'];
                        }
                    }
                }
            }
            $models[0]['customer_office']=array_values($models[0]['customer_office']);
        }
        return $models;
    }

    /**
     * 検索機能
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getProjectSearch(Request $request)
    {
        $query = new ProjectSearchModel();
        $query->init([
            'searchArray' => $request->input('searchArray'),
            'selectWord' => $request->input('selectWord'),
            'searchType' => $request->input('searchType'),
        ]);
        $models = $query->search()->with('customer', 'customerOffice')->paginate($this->pagination);
        return $models;
    }

    /**
     * 案件を削除
     * @param Request $request
     * @return \Exception|\PDOException
     */
    public function deleteProject(Request $request)
    {
        $id=$request->post('id');
        DB::beginTransaction();
        try {
            $model = Project::find($request->post('id'));
            //解散グループ 容量を増やす
            $groupArr=Group::where('id', '=', $model->group_id)->orWhere('parent_id', '=', $model->group_id)->get();
            $chat = new ChatController();
            foreach ($groupArr as $val){
                $chat->deleteContain($val);
            }
            //doc storage restore
            $this->docStorageRestore($request->post('id'));
            UserStorage::where('project_id',$request->post('id'))->delete();
            $dashboard = new DashboardController();
            $dashboardAddMag = trans('messages.dashboardProject.delete');
            $proPartnerArr = ProjectParticipant::where('project_id',$request->post("id"))->get()->toArray();
            $proArr = Project::find($request->post("id"));

            // [ドキュメント]招待された職人ユーザーに招待元の企業のドキュメントを表示しない　＃3134
            $chatContactUserIds = $this->getEnterpriseChatContact();

            foreach ($proPartnerArr as $item){
                if (Auth::id() != $item['user_id'] && !in_array($item['user_id'],$chatContactUserIds)){
                    $dashboard->addDashboard($request->get("id"),4,$proArr->place_name,
                        ($proArr->place_name).'、'.$dashboardAddMag,Auth::id(),$item['user_id']);
                }
            }
            $groupModel = Group::where('parent_id', '=', $model->group_id)->get();
            if ($groupModel) {
                foreach ($groupModel as $gModel) {
                    ChatGroup::where('group_id', '=', $gModel->id)->delete();
                    ChatLastRead::where('group_id', '=', $gModel->id)->delete();
                    ChatList::where('group_id', '=', $gModel->id)->delete();
                    ChatMessage::where('group_id', '=', $gModel->id)->delete();
                    ChatPerson::where('group_id', '=', $gModel->id)->delete();
                }
            }
            ChatGroup::where('group_id', '=', $model->group_id)->delete();
            ChatLastRead::where('group_id', '=', $model->group_id)->delete();
            ChatList::where('group_id', '=', $model->group_id)->delete();
            //2020-11-2 #2298  チャット情報は削除しないことにした
//            ChatMessage::where('group_id', '=', $model->group_id)->delete();
            ChatPerson::where('group_id', '=', $model->group_id)->delete();
//            ChatNice::where('group_id', '=', $model->group_id)->delete(); // todo テーブルを使用されない
            ChatTask::where('group_id', '=', $model->group_id)->delete();
            Dashboard::where('related_id',$model->group_id)->where('type','1')->delete();
            Group::where('id', '=', $model->group_id)->orWhere('parent_id', '=', $model->group_id)->delete();
            ProjectHospital::where('project_id', '=', $id)->delete();
            ProjectLocaleChief::where('project_id', '=', $id)->delete();
            ProjectParticipant::where('project_id', '=', $id)->delete();
            ProjectTradesChief::where('project_id', '=', $id)->delete();
            ProjectCustomer::where('project_id', '=', $id)->delete();
            $project = Project::destroy($request->post('id'));
            DB::commit();
            return $project;
        } catch (\PDOException $e) {
            DB::rollBack();
            return $e;
        }
    }

    /**
     * 都道府県、区市町村を搜索
     * @param Request $request
     * @return string
     */
    public function zipCloud(Request $request)
    {
        $result = '';
        $url = "http://zipcloud.ibsnet.co.jp/api/search?zipcode=" . $request->get('zipcode');
        if ($url != null) {
            $res = file_get_contents($url);
            $resJson = json_decode($res, true);
            $result = $resJson['results'][0];
        }
        return $result;
    }

    /**
     * @param Request $request
     * @param String $checkName
     * @param String $key
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function inputCheck(Request $request, String $checkName, String $key)
    {
        if ($checkName === 'localeChief') {
            $check = new ProjectLocaleChief();
        } else if ($checkName === 'tradesChief') {
            $check = new ProjectTradesChief();
        } else {
            $check = new ProjectHospital();
        }
        $check->fill(json_decode($request->get($checkName), true)[$key]);
        $validate = $check->validate();
        if ($validate->fails()) {
            return $this->json($validate->errors()->all());
        } else {
            return "noError";
        }
    }

    private function docStorageRestore($projectId) {
        $nodes = Node::findAll($projectId);
        $nodes = array_map(function ($e) {
            return $this->toNodeJson($e);
        }, $nodes);
        
        //find roor dirs
        $rootDirs = [];
        foreach($nodes as $node) {
            if($node['parent'] === null && $node['type'] === 0) {
                $rootDirs[] = $node['id'];
            }
        }

        foreach($rootDirs as $rootId) {
            $this->restoreDocNode($nodes, $rootId, $projectId);
        }
    }

    private function restoreDocNode($nodes, $nodeId, $projectId) {
        $nodeChildNodes = $this->getChildren($nodeId, $nodes);
        
         //used storage will restore
         $storageAll = 0; // enterprise storage
         $storageUser = []; //node include more than one user's storage
        
         //get all nodes' rev
        $doc_db = config('const.db_database_doc');
        $revs = DB::table("$doc_db.revs")
            ->whereIn('node_id', $nodeChildNodes)
            ->whereNull('deleted_at')
            ->select(['file_size', 'user_id_commit'])
            ->get();
        
        foreach($revs as $revItem) {
            $storageAll += $revItem->file_size;
            $userKeyIndex = 'u' . $revItem->user_id_commit;
            if(array_key_exists($userKeyIndex, $storageUser)) {
                $storageUser[$userKeyIndex] += $revItem->file_size;
            } else {
                $storageUser[$userKeyIndex] = $revItem->file_size;
            }
        }

        try {
            DB::beginTransaction();

            // ノード削除
            $node = Node::rmnode($nodeId, $projectId);
            // ロギング
            NodeLog::d($node);

            DB::commit();

            //restore
            $userIndexArray = array_keys($storageUser);
            for($i=0; $i<count($userIndexArray); $i++) {
                $userIdTmp = substr($userIndexArray[$i], 1); //get userid
                $userInfoArr = $this->getStorageCountOtherUser($userIdTmp);
                $pattern = $this->checkPattern($projectId, $userIdTmp);
                $this->changeStorage($userIdTmp, null, $userInfoArr['enterpriseId'], $pattern['otherEnterpriseId'], $projectId, -$storageUser[$userIndexArray[$i]]);
            }
            $this->changeEnterpriseStorage(Auth::user()->enterprise_id, Auth::user()->enterprise_id, -$storageAll);
            
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
        }
    }

    //check pattern
    private function checkPattern($projectId, $userId) {
        $storagePattern = [];

        $projectInfo = $this->getProjectInformation($projectId);
        $userInfo = $this->getStorageCountOtherUser($userId);

        //this doc dir belong to project
        $storagePattern['userId'] = $userId;
        $storagePattern['groupId'] = null;
        
        $storagePattern['enterpriseId'] = $userInfo['enterpriseId'];
        $storagePattern['otherEnterpriseId'] = $projectInfo->enterprise_id;
        $storagePattern['projectId'] = $projectId;

        return $storagePattern;
    }

    //change storage record
    private function changeStorage($userId, $groupId = null, $enterpriseId, $otherEnterpriseId, $projectId, $storage) {
        //get storage count now
        $storageInfo = DB::table('user_storages')
            ->where('user_id', $userId)
            ->where('enterprise_id', $enterpriseId)
            ->where('other_enterprise_id', $otherEnterpriseId)
            ->where('project_id', $projectId)
            ->whereNull('group_id')
            ->where('type', self::STORAGE_TYPE)
            ->first();
        
        $storagePrev = 0;
        
        if($storageInfo) {
            //data exists
            $storagePrev = $storageInfo->doc_storage;
        }

        $whereRule = array(
            'user_id' => $userId,
            'enterprise_id' => $enterpriseId,
            'other_enterprise_id' => $otherEnterpriseId,
            'group_id' => null,
            'project_id' => $projectId,
            'type' => self::STORAGE_TYPE
        );

        $nxtUsedStorage = $storagePrev + $storage;
        if($nxtUsedStorage < 0) {
            $nxtUsedStorage = 0;
        }


        $flag = DB::table('user_storages')
            ->updateOrInsert($whereRule,['doc_storage' => $nxtUsedStorage, 'created_at' => NOW(), 'updated_at' => NOW()]);
        
        return $flag;
    }

    //change enterprise storage record
    private function changeEnterpriseStorage($enterpriseId, $otherEnterpriseId, $storage) {
        $id = $enterpriseId;

        if($otherEnterpriseId) {
            $id = $otherEnterpriseId;
        }

        if($storage > 0) {
            $flag = DB::table('enterprises')
                ->where('id', $id)
                ->increment('usedStorage', $storage);
        } else {
            $flag = DB::table('enterprises')
                ->where('id', $id)
                ->decrement('usedStorage', -$storage);
        }
        
        return $flag;
    }

    //get user information
    private function getStorageCountOtherUser($otherUserId) {
        $storageRefer = [];

        $user = DB::table('users')
            ->where('id', $otherUserId)
            ->whereNull('deleted_at')
            ->first();
        
        $storageRefer['enterpriseId'] = $user->enterprise_id;
        $storageRefer['userId'] = $user->id;

        return $storageRefer;
    }

    //get a node's child nodes all
    private function getChildren($p_id, $array)
    {
        $subs = array();
        foreach ($array as $item) {
            if ($item['parent'] == $p_id) {
                $subs[] = $item['id'];
                $subs = array_merge($subs, $this->getChildren($item['id'], $array));
            }
        }
        return $subs;
    }

    private function toNodeJson($row)
    {
        return [
            'id' => (int)$row->node_id,
            'parent' => is_null($row->node_id_parent) ? null : (int)$row->node_id_parent,
            'name' => $row->node_name,
            'type' => (int)$row->node_type,
            'owner' => $row->owner,
            'locker' => $row->locker,
            'lockUser' => (int)$row->user_id_lock,
            'lockTime' => $row->lock_time,
            'size' => (int)$row->file_size,
            'time' => $row->last_updated,
            'deleted' => (bool)$row->deleted_at,
            'rev_no' => is_null($row->rev_no) ? null : (int)$row->rev_no,
            'file_name' => $row->file_name,
        ];
    }

    //get project information
    private function getProjectInformation($projectId) {
        $info = DB::table('projects')
            ->where('id', $projectId)
            ->whereNull('deleted_at')
            ->first();
        
        if($info) {
            //exists
            return $info;
        } else {
            //not exits or been deleted
            return false;
        }
    }
}
