<?php
/**
 *チャット
 *
 * @author dzx
 */

namespace App\Http\Controllers\Web;

use App\Http\Facades\Common;
use App\Http\Services\FirebaseService;
use App\Models\Account;
use App\Models\ChatContact;
use App\Models\ChatFile;
use App\Models\ChatGroup;
use App\Models\ChatLastRead;
use App\Models\ChatList;
use App\Models\ChatMessage;
use App\Models\ChatMessageChange;
use App\Models\ChatPerson;
use App\Models\ChatTask;
use App\Models\ChatTaskCharge;
use App\Models\ChatTaskSearchModel;
use App\Models\Dashboard;
use App\Models\Enterprise;
use App\Models\EnterpriseParticipant;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use App\Models\UserStorage;
use App\Models\ChatLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use ZipArchive;

class ChatController extends \App\Http\Controllers\Controller
{
    const FILES_PATH_PREFIX = 'files';
    const ACCESS_TYPE_CHAT = 3;

    /**
     * すべてのユーザーに関する情報を入手する
     */
    public function getPersonList()
    {
        $group = Group::whereHas('users', function ($q) {
            $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id','users.file');
        })->with(['users.enterprise' => function ($q) {
            $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
        }, 'users' => function ($q) {
            $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1','users.file');
        }])->with('mine')->with(['users.enterpriseCoop' => function ($q) {
            $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
        }, 'users' => function ($q) {
            $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1','users.file');
        }])->whereHas('chatGroup', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['project' => function ($q) {
            $q->select('projects.id', 'projects.group_id');
        }])->get()->toArray();

        $gro = [];
        $per = [];
        $mineId = Auth::id();
        $middlePersonArr = [];
        foreach ($group as $g) {
            //friend
            if ($g['kind']) {
                foreach ($g['users'] as $res) {
                    if ($res['id'] != $mineId && !in_array($res['id'],$middlePersonArr)) {
                        $per[] = $res;
                        $middlePersonArr[] = $res['id'];
                    }
                }
            } else {
                //group
                if ($g['project']) {
                    foreach ($group as $g1) {
                        if ($g['id'] == $g1['parent_id']) {
                            $g['child'][] = $g1;
                        }
                    }
                }
                if (!$g['parent_id']) {
                    if (Auth::user()->enterprise_id) {
                        $g['addChildGroup'] = true;
                    }
                    $gro[] = $g;
                }
            }
        }
        return ['group' => $gro, 'person' => $per, 'mineId' => $mineId];
    }

    /**
     * 案件グループチャットを取得
     * @return array
     */
    public function getProjectChatList()
    {
        $data = $this->getPersonList();

        $projChatGroups = [];

        if ($data['group']) {
            $chatGroups = $data['group'];

            foreach ($chatGroups as $item)
            {
                //案件グループ を判断する
                if ($item['project']){
                    $projChatGroups[] = $item;
                }
            }
        }

        $per = [];
        $mineId = $data['mineId'];
        return ['group' => $projChatGroups, 'person' => $per, 'mineId' => $mineId];
    }

    /**
     *チェットFileの一覧とファイル名検索を取得
     */
    public function getChatFileList(Request $request)
    {
        $groupIdArr = ChatGroup::query()->where('user_id', Auth::id())->pluck('group_id');
        $chatFiles = ChatFile::whereIn('group_id', $groupIdArr);

        $proj = Project::whereIn('group_id', $groupIdArr)->pluck('group_id')->toArray();
        $groups = Group::whereIn('id', $groupIdArr)->whereNotNull('parent_id')->pluck('id')->toArray();
        $isProj = array_merge($proj,$groups);
        $searchFileWord = Common::escapeDBSelectKeyword($request->get("searchFileWord"));

        if (strlen($searchFileWord) > 0) {
            $chatFiles->where('file_name', "like", "%{$searchFileWord}%");
        }
        $res = $chatFiles->with('userDeleted')->get()->toArray();

        $fromUserId = [];
        foreach ($res as $item){
            $fromUserId[] = $item['upload_user_id'];
        }
        $chatMessageModels = ChatMessage::where('file_name','!=','')->whereIn('group_id', $groupIdArr)
            ->whereIn('from_user_id',$fromUserId)
            ->get(['id','group_id','file_name','from_user_id'])->toArray() ;
        $resArr = [];
        foreach ($res as $k=>$item){
            foreach ($chatMessageModels as $msg){
                $msg['file_name'] = '0'.$msg['file_name'];
                if ($item['upload_user_id'] == $msg['from_user_id'] &&
                    $item['group_id'] == $msg['group_id'] &&
                    strpos($msg['file_name'], $item['file_name'])){
                    if (in_array($msg['group_id'],$isProj)){
                        $res[$k]['isProj'] = true;
                    }else{
                        $res[$k]['isProj'] = false;
                    }
                    $res[$k]['messageId'] = $msg['id'];
                    $resArr[] = $res[$k];
                }
            }
        }
        return $resArr;
    }

    /**
     *　チェットタスクの一覧を取得
     */
    public function getChatTaskList(Request $request)
    {
        $type = $request->get("type");
        $authId = Auth::id();

        $chatTaskNoSelf = ChatTask::where('create_user_id', $authId)
            ->whereNull('complete_date')
            ->orderBy('created_at')
            ->with(['chattaskcharges.users' => function ($q) {
                $q->withTrashed();
            }, 'users' => function ($q) {
                $q->withTrashed();
            }])
            ->with('chatmessages')->get();
        $chatTaskNoOther = ChatTask::whereNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) use($type) {
                $q->where('user_id', Auth::id());
                if ($type){
                    $q->where('check_flag', 0);
                }
            })
            ->orderBy('created_at')
            ->with(['chattaskcharges.users' => function ($q) {
                $q->withTrashed();
            }, 'users' => function ($q) {
                $q->withTrashed();
            }])
            ->with('chatmessages')->get();
        $chatTaskNoAll = ChatTask::whereNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) use($type){
                $q->where(function ($q1){
                    $q1->where('user_id', Auth::id())
                        ->orWhere('create_user_id', Auth::id());
                });
                if ($type){
                    $q->where('check_flag', 0);
                }
            })
            ->orderBy('created_at')
            ->with(['chattaskcharges.users' => function ($q) {
                $q->withTrashed();
            }, 'users' => function ($q) {
                $q->withTrashed();
            }])
            ->with('chatmessages')->get();
        $chatTaskYesSelf = ChatTask::where('create_user_id', $authId)
            ->whereNotNull('complete_date')
            ->orderBy('created_at')
            ->with(['chattaskcharges.users' => function ($q) {
                $q->withTrashed();
            }, 'users' => function ($q) {
                $q->withTrashed();
            }])
            ->with('chatmessages')->get();
        $chatTaskYesOther = ChatTask::whereNotNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) use($type){
                $q->where('user_id', Auth::id());
                if ($type){
                    $q->where('check_flag', 0);
                }
            })
            ->orderBy('created_at')
            ->with(['chattaskcharges.users' => function ($q) {
                $q->withTrashed();
            }, 'users' => function ($q) {
                $q->withTrashed();
            }])
            ->with('chatmessages')->get();
        $chatTaskYesAll = ChatTask::whereNotNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) use ($type){
                $q->where(function ($q1){
                    $q1->where('user_id', Auth::id())
                        ->orWhere('create_user_id', Auth::id());
                });
                if ($type){
                    $q->where('check_flag', 0);
                }
            })
            ->orderBy('created_at')
            ->with(['chattaskcharges.users' => function ($q) {
                $q->withTrashed();
            }, 'users' => function ($q) {
                $q->withTrashed();
            }])
            ->with('chatmessages')->get();
        $name = Auth::user()->name;

        return [
            "chatTaskNoSelf" => $chatTaskNoSelf,
            "chatTaskNoOther" => $chatTaskNoOther,
            "chatTaskNoAll" => $chatTaskNoAll,
            "chatTaskYesSelf" => $chatTaskYesSelf,
            "chatTaskYesOther" => $chatTaskYesOther,
            "chatTaskYesAll" => $chatTaskYesAll,
            'authName' => $name,
        ];
    }

    /**
     * チェットタスク 検索
     * @param Request $request
     * @return array|string
     */
    public function searchChatTask(Request $request)
    {
        $authId = Auth::id();
        $query = new ChatTaskSearchModel();
//        if (!Common::escapeDBSelectKeyword($request->get('q'))){
//            return $this->getChatTaskList();
//        }
        $query->init(['keyword' => Common::escapeDBSelectKeyword($request->get('q')),]);

        $selectTaskNoSelf = $query->search()
            ->where('create_user_id', $authId)
            ->whereNull('complete_date')
            ->orderBy('created_at')
            ->with('chattaskcharges.users', 'users')->get();
        $selectTaskNoOther = $query->search()
            ->whereNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('created_at')
            ->with('chattaskcharges.users', 'users')->get();
        $selectTaskNoAll = $query->search()
            ->whereNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) {
                $q->where('user_id', Auth::id())
                    ->orWhere('create_user_id', Auth::id());
            })
            ->orderBy('created_at')
            ->with('chattaskcharges.users', 'users')->get();
        $selectTaskYesSelf = $query->search()
            ->where('create_user_id', $authId)
            ->whereNotNull('complete_date')
            ->orderBy('created_at')
            ->with('chattaskcharges.users', 'users')->get();
        $selectTaskYesOther = $query->search()
            ->whereNotNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('created_at')
            ->with('chattaskcharges.users', 'users')->get();
        $selectTaskYesAll = $query->search()
            ->whereNotNull('complete_date')
            ->whereHas('chattaskcharges', function ($q) {
                $q->where('user_id', Auth::id())
                    ->orWhere('create_user_id', Auth::id());
            })
            ->orderBy('created_at')
            ->with('chattaskcharges.users', 'users')->get();

        $name = Auth::user()->name;

        return [
            "selectTaskNoAll" => $selectTaskNoAll,
            "selectTaskNoSelf" => $selectTaskNoSelf,
            "selectTaskNoOther" => $selectTaskNoOther,
            "selectTaskYesAll" => $selectTaskYesAll,
            "selectTaskYesSelf" => $selectTaskYesSelf,
            "selectTaskYesOther" => $selectTaskYesOther,
            'authName' => $name,
        ];
    }

    /**
     * チェットタスク 新規
     * @param Request $request
     * @return string
     */
    public function insertChatTask(Request $request)
    {
        $chatTask = new ChatTask();
        $chatTaskCharge = new ChatTaskCharge();
        $authId = Auth::id();
        $taskCreateData = $request->get("taskCreateData");
        $noteTmp = htmlspecialchars(htmlspecialchars($taskCreateData['note']));
        $taskCreateData['note'] = htmlspecialchars_decode($noteTmp);
        $chatTask->fill($taskCreateData);
        $chatTask->notify=$taskCreateData['notify'];
        // 検証
        $taskValidate = $chatTask->taskValidate();
        if (!$taskValidate->fails()) {
            DB::beginTransaction();
            try {
                $chatTask->create_user_id = $authId;
                $chatTask->message_id = 0;
                $chatTask->save();

                $task_id = $chatTask->id;
                // 新規タスクのid取得
                $chatTaskCharge->task_id = $task_id;
                $chatTaskCharge->user_id = $authId;
                $chatTaskCharge->save();

                DB::commit();
                return [$this->json($taskValidate->errors()->all()),'task_id'=>$chatTask->id];
            } catch (\PDOException $e) {
                // データベースエラー
                $error = trans('messages.error.insert');
                DB::rollBack();
                return $this->error($e, $error);
            }
        }
        return $this->json($taskValidate->errors()->all());
    }

    /**
     * チェットタスク 削除
     * @param Request $request
     * @return string
     */
    public function deleteChatTask(Request $request)
    {
        try {
            // 削除データのIdを取得
            $selectId = ChatTask::find($request->get("id"))['id'];
            $groupId = ChatTask::find($request->get("id"))['group_id'];
            $msg = $msg = $request->get("msg");
            $delMessage = '■タスクを削除しました。'.chr(13);
            if (!empty($selectId)) {
                ChatTask::destroy($request->get('id'));
                ChatTaskCharge::where('task_id', $selectId)->delete();
                if ($groupId){
                    $chatMsg = new ChatMessage();
                    $chatMsg->group_id = $groupId;
                    $chatMsg->from_user_id = Auth::id();
                    $chatMsg->message = $delMessage.$msg;
                    $chatMsg->save();
                }
            }
        } catch (\PDOException $e) {
            // データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, $error);
        }
        return $this->json();
    }

    /**
     * チェットタスク 更新
     * @param Request $request
     * @return string
     */
    public function updateChatTask(Request $request)
    {
        DB::beginTransaction();
        try {
            // 更新データのIdを取得
            $selectId = $request->get("id");
            $msg = $request->get("msg");
            $groupId = ChatTask::find($request->get("id"))['group_id'];
            $selectData = ChatTask::where('id', $selectId)->get();
            $completeData = ChatTask::where('id', $selectId)->value('complete_date');
            $messageYes = "■タスクを完了しました。".chr(13);
            $messageNo = "■タスクを未完了に戻しました。".chr(13);
            foreach ($selectData as $item) {
                if (
                    $completeData == null
                    || $completeData == ""
                ) {
                    //完了にする
                    $item->complete_date = date("Y-m-d");
                    if ($groupId){
                        $chatMsg = new ChatMessage();
                        $chatMsg->group_id = $groupId;
                        $chatMsg->from_user_id = Auth::id();
                        $chatMsg->message = $messageYes.$msg;
                        $arr=explode('mid:',$msg);
                        if(count($arr)>=2){
                            $arrMid=explode(']',$arr[1]);
                            $mid=$arrMid[0];
                            $res=ChatMessage::where('id',$mid)->get(['file_name'])->toArray();
                            if($res){
                                $chatMsg->file_name=$res[0]['file_name'];
                            }
                        }
                        $chatMsg->save();
                    }

                } else {
                    //未完了にする
                    $item->complete_date = null;
                    if ($groupId){
                        $chatMsg = new ChatMessage();
                        $chatMsg->group_id = $groupId;
                        $chatMsg->from_user_id = Auth::id();
                        $chatMsg->message = $messageNo.$msg;
                        $arr=explode('mid:',$msg);
                        if(count($arr)>=2){
                            $arrMid=explode(']',$arr[1]);
                            $mid=$arrMid[0];
                            $res=ChatMessage::where('id',$mid)->get(['file_name'])->toArray();
                            if($res){
                                $chatMsg->file_name=$res[0]['file_name'];
                            }
                        }
                        $chatMsg->save();
                    }
                }
                $item->save();
            }
            DB::commit();
            foreach ($selectData as $item) {
                $idArr = ChatGroup::where('group_id', $groupId)->get('user_id')->toArray();
                $group = Group::find($groupId);
                $fireBase = new FirebaseService();
                if (!count($idArr)) {
                    $idArr[] = Auth::id();
                }
                foreach ($idArr as $toId) {
                    if ($toId['user_id'] != Auth::id()) {
                        $fireBase->pushTaskMessage(Auth::user(), $toId['user_id'], $group, $item->id,
                            $item->note, 'message');
                    }
                }
            }
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            $error = trans('messages.error.update');
            return $this->error($e, $error);
        }
        return $this->json();
    }

    /**
     * 検索会社名
     * @param Request $request
     * @return mixed
     */
    public function getEnterpriseName(Request $request)
    {
        try {
            $model = Enterprise::find($request->get('enterpriseId'));
            return $this->json('', $model);
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }

    /**
     * チャットの記録を調べます
     * @param Request $request
     * @return mixed
     */
    public function getChatList(Request $request)
    {
        try {
           $groupId=$request->get('groupId');
            //get jump-to message tag
            $jumpTag = $request->input('jumpTag',0);
            $limit=(int)config('web.pageSize.chat.content');//ページあたりの数
            $lastOffset=$request->get('lastOffset');//最後に読み込まれたID
            $word = Common::escapeDBSelectKeyword($request->get('word'));
            $num=$this->read_num($request->get('groupId'));//未読数を決定する
            if($num>$limit){
                $limit=$num;
            }
            $min_id=0;
            if(!$lastOffset){
                $min_id=ChatMessage::where('message', 'LIKE', "%{$word}%")->where('group_id', '=', $groupId)->value('id');
            }
            //照会社内,協力会社チームチャット情報
            $rule = ChatMessage::with([
                'user.enterprise' => function ($q) {
                    $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator');
                    $q->withTrashed();
                },
                'user' => function ($q) {
                    $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.file', 'users.email');
                    $q->withTrashed();
                }
            ])->with([
                'user.coopEnterprise' => function ($q) {
                    $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator');
                    $q->withTrashed();
                },
                'user' => function ($q) {
                    $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.file', 'users.email');
                    $q->withTrashed();
                }
            ])->where('group_id', '=', $groupId)
            ->orderBy('chatmessages.id','desc');

            if($jumpTag > 0){
                //get num
                $countSum =ChatMessage::where('group_id',$groupId)
                    ->where('id','>=',$jumpTag)
                    ->count();
                if($countSum >= $limit) {
                    $models = $rule->limit($countSum + 1);
                } else {
                    $models = $rule->limit($limit);
                }
            }else{
                $models = $rule->limit($limit);
            }

            if ($word || $word == '0') {
                if($lastOffset){
                    $models = $models->where('message', 'LIKE', "%{$word}%")->where('chatmessages.id','<',$lastOffset)->get();
                }else{
                    $models = $models->where('message', 'LIKE', "%{$word}%")->get();
                }
            } else {
                if($lastOffset){
                    $models = $models->where('chatmessages.id','<',$lastOffset)->get();
                }else{
                    $models = $models->get();
                }
                if (count($models) != 0) {
                    $chatMessage = $models[0];
                    $chat = ChatLastRead::where('group_id', '=', $chatMessage->group_id)->where('user_id', '=',
                        Auth::id())->first();
                    if ($chat) {
                        if($chat->message_id<$chatMessage->id){
                            $chat->message_id = $chatMessage->id;
                            $chat->save();
                        }
                    } else {
                        $chatLastRead = new ChatLastRead();
                        $chatLastRead->group_id = $chatMessage->group_id;
                        $chatLastRead->user_id = Auth::id();
                        $chatLastRead->message_id = $chatMessage->id;
                        $chatLastRead->save();
                    }
                }
            }
            $dateTime = '';
            $dateTimePre = '';
            $length = count($models);
            for ($i = 0; $i < $length; $i++) {
                //ファイル処理
                if ($models[$i]->file_name) {
                    $files = explode(',', $models[$i]->file_name);
                    $firstFileName = explode(':', $files[0])[0];
                    //firstFileSize
                    $models[$i]->fileSize = round(Common::getFileSize($models[$i]->group_id,
                                $firstFileName) / 1024, 2) . 'KB';
                }
                //引用
                $models[$i]->isQuote = false;
                if (strstr($models[$i], '----------')) { //todo  引用コード無効，フロント解析
                    $models[$i]->isQuote = true;
                }
                $models[$i]->isClicked = false;

                $dateTime = explode(' ', $models[$i]->created_at)[0];
                if ($i == $length - 1) {
                    $dateTimePre = explode(' ', $models[$i]->created_at)[0];
                } else {
                    $dateTimePre = explode(' ', $models[$i + 1]->created_at)[0];
                }


                if ($dateTime != $dateTimePre) {
                    $models[$i]->showDate = true;
                    $models[$i]->dateSpan = date('Ymd',strtotime($models[$i]->created_at));
                    continue;
                }

                if ($i == $length - 1) {
                    $models[$i]->showDate = true;
                    $models[$i]->dateSpan = date('Ymd',strtotime($models[$i]->created_at));
                    continue;
                }

                $models[$i]->showDate = false;
                $models[$i]->dateSpan = date('Ymd',strtotime($models[$i]->created_at));
            }
            $models=$models->toArray();
            $arr = array_column($models, 'id');
            $models= $this->getDelId($models,$groupId);
            array_multisort($arr, SORT_ASC, $models);
            $enterpriseId = 0;
            //案件チャット
            $parentId = $request->get('parentId');
            if ($parentId) {
                $project = Project::where('group_id', $parentId)->with('user')->get('created_by')->toArray();
                $enterpriseId = $project[0]['user']['enterprise_id'];
                if (!$enterpriseId) {
                    $enterpriseId = $project[0]['user']['coop_enterprise_id'];
                }
            }
            if ($enterpriseId == Auth::user()->enterprise_id && $enterpriseId) {
                $adminArea = true;
            } else {
                if ($enterpriseId == Auth::user()->coop_enterprise_id && $enterpriseId) {
                    $adminArea = true;
                } else {
                    $adminArea = false;
                }
            }
            //get all users belong to this group
            $groupUsers = DB::table('chatgroups')
                ->where('group_id',$request->get('groupId'))
                // ->whereNull('deleted_at')
                // ->whereNotIn('user_id',[Auth::id()])
                ->select(['user_id'])
                ->get()
                ->toArray();
            $groupUsersArr = [];
            foreach($groupUsers as $userItem) {
                $groupUsersArr[] = $userItem->user_id;
            }
            //get all users' lastread
            $lastReads = DB::table('chatlastreads')
                ->where('group_id',$request->get('groupId'))
                ->whereIn('user_id',$groupUsersArr)
                ->select(['user_id','message_id'])
                ->get()
                ->toArray();
            Dashboard::where('related_id', $request->get('groupId'))
                ->where(function ($q) {
                    $q->where('type', 0);
                    $q->orWhere('type', 1);
                })->where('to_user_id', Auth::id())->update(['read'=>1]);
            $chatlike=$this->getChatLike($groupId);
            return $this->json('', ['models' => $models, 'adminArea' => $adminArea, 'lastReads' => $lastReads,'chatlike'=>$chatlike]);
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }

    /**
     *
     * @param $models
     * @param $groupId
     */
    public  function getDelId($models,$groupId){
        //すべてのユーザーを取得
        $userId = DB::table('chatgroups AS cg')
            ->leftJoin('users AS u','u.id','=','cg.user_id' )
            ->where('group_id', '=', $groupId)
            ->pluck('u.id')->toArray();
        //ユーザを削除するデータを取得する
        $userDel = DB::table('chatgroups AS cg')
            ->leftJoin('users AS u','u.id','=','cg.user_id' )
            ->where('group_id', '=', $groupId)
            ->whereNotNull('u.deleted_at')
            ->pluck('u.id')->toArray();

        //協力関係が削除されているかどうかを確認する
        $enterpriseId= Auth::user()->enterprise_id;
        $enterpriseParticipant=DB::table('enterprise_participants')->whereIn('user_id', $userId)
            ->where('enterprise_id', '=', $enterpriseId)
            ->whereNotNull('deleted_at')
            ->where('agree', '=', config('const.AGREE'))
            ->whereNotNull('deleted_at')
            ->pluck('user_id')->toArray();
        //従業員の関係が削除されているかどうかを確認します
        $chatContacts=DB::table('chatcontacts')->whereIn('to_user_id', $userId)
            ->where('contact_agree', '=', config('const.AGREE'))
            ->whereNotNull('deleted_at')
            ->pluck('to_user_id')->toArray();
        //ユーザーが削除されているかどうかを確認する
        foreach ($models as $k=>$v){
            $models[$k]['del']='';
            if(in_array($v['user']['id'],$userDel)||
                in_array($v['user']['id'],$enterpriseParticipant)||
                in_array($v['user']['id'],$chatContacts)){
                $models[$k]['del']='delete';
            }
        }
        return $models;


    }
    //未読数を決定する
    private function read_num($group_id){
        // get the last messages sent by user
        $message_id=ChatLastRead::where('user_id',Auth::id())->where('group_id',$group_id)->value('message_id');
        if(!$message_id){
            $message_id = 0;
        }
        $num=ChatMessage::where('id','>',$message_id)->where('group_id',$group_id)->count();
        return $num;
    }
    /**
     * チャットCall通知を送信
     * @param Request $request
     * @return mixed
     */
    public function pushChatCall(Request $request)
    {
        $group_id = $request->input('group_id');
        $call_status = $request->input('call_status');
        $idArr = ChatGroup::where('group_id', $group_id)->get('user_id')->toArray();
        $group = Group::find($group_id);
        $fireBase = new FirebaseService();
        $dashboard = new DashboardController();
        foreach ($idArr as $toId) {
            if ($toId['user_id'] != Auth::id()) {
                if(in_array($call_status,['join','video_join'])) {
                    $title = 'さんから通話着信あり';
                    if ($call_status == 'video_join') {
                        $title = 'さんからビデオ着信あり';
                    }
                    $type = $this->isProjectGroup($group);
                    $date = date('m/d H:i');
                    if ($group->kind) {
                        $name = ' （' . Auth::user()->name . '、' . $date . '）';
                    } else {
                        $name = ' （' . $group->name . '、' . $date . '）';
                    }
                    $title = $title . $name;
                    $dashboard->addDashboard(
                        $group_id, $type, (Auth::user()->name) . $title, '', $toId['user_id']
                    );
                }
                $fireBase->pushChatMessage(Auth::user(), $toId['user_id'], $group, $call_status);
            }
        }
    }

    private function isProjectGroup($group){
        //案件子の群=1
        if ($group->parent_id){
            $res = 1;
        }else{
            //案件群=1，チャット群=0
            $res = Project::where('group_id',$group->id)->count();
        }
        return $res;
    }

    private function uploadImg($request, $group_id)
    {
        $uploadFileName = Common::upload($request, $group_id);
        $fileNameArr = explode('.', $uploadFileName);
        $ext = $fileNameArr[count($fileNameArr) - 1];
        if (strtoupper($ext) == 'PDF') {
            //convert pdf to get cover
            $this->convertSinglePage($group_id, $uploadFileName, 1);
        }
        return $uploadFileName;
    }

    public function uploadFiles(Request $request){
        try {
            $group_id = $request->post("group_id");
            $uploadFileName = $this->uploadImg($request,$group_id);
            $fileSize= (Common::getFileSize($group_id,$uploadFileName))/1024;
            if(!$uploadFileName||!$fileSize){
                return $this->error('', trans('messages.error.upload'));
            }
        } catch (\PDOException $e) {
            DB::rollBack();
            return $this->error($e, trans('messages.error.upload'));
        }
        return $this->json('',['fileName'=>$uploadFileName,'fileSize'=>$fileSize]);
    }

    /**
     * チャット情報を挿入
     * @param Request $request
     * @return mixed
     */
    public function setChatMessage(Request $request)
    {
        DB::beginTransaction();

        $model = new ChatMessage();
        $model->group_id = json_decode($request->get("group_id"), true);
        $model->from_user_id = Auth::id();
        $model->task_id = json_decode($request->get("task_id"), true);
        $messageTmp = htmlspecialchars(htmlspecialchars(json_decode($request->get("message"), true)));
        $model->message = htmlspecialchars_decode($messageTmp);
        $fileName = json_decode($request->get("quoteFileName"), true);
        $fileNameForward = json_decode($request->get("forwardFileName"), true);
        $toIdArr = json_decode($request->get("toIdArr"), true);
        $toNameArr = json_decode($request->get("toNameArr"), true);
        //message forward
        $from_group_id = $request->input('from_group_id', 0);
        $uploadFileName = json_decode($request->get('fileName'));
        $fileSize=0;
        if ($fileName) {//引用
            $model->file_name = $fileName;
        }

        // 転送の時 且 ソースグループ
        $fromSize=0;
        if ($fileNameForward && $from_group_id) {
            if (strpos($model->message, '▼ From:') !== false) {
                $files = explode(',', $fileNameForward);
                foreach ($files as $key => $value){
                    $name = explode(':', $value)[0];
                    $fileNamePre = sprintf('upload/g%s/%s', $from_group_id, $name);// コピー元
                    $fileNameNxt = sprintf('upload/g%s/%s', $model->group_id, $name);// コピー先
                    $disk = Storage::disk(config('web.imageUpload.disk'));
                    if ($disk->exists($fileNamePre)) {
                        $file = $disk->get($fileNamePre);
                        $disk->put($fileNameNxt, $file);
                        $fileSize= $disk->size($fileNameNxt);
                        $fromSize+=$fileSize;
                        DB::table('chat_files')->insert([
                            'upload_user_id'   => Auth::id(),
                            'group_id'         => $model->group_id,
                            'file_name'        => $name,
                            'file_size'        => $fileSize,
                            'created_at'       => date("Y/m/d H:i:s")
                        ]);
                    }
                    $ext = explode('.',$name);
                    if(end($ext)=='pdf'||end($ext)=='PDF'){
                        $imageNamePure = $name . '.png';
                        $pageNamePre = sprintf('upload/g%s/%s', $from_group_id, $imageNamePure);// コピー元
                        $pageNameNxt = sprintf('upload/g%s/%s', $model->group_id, $imageNamePure);// コピー先
                        if ($disk->exists($pageNamePre)) {
                            $file = $disk->get($pageNamePre);
                            $disk->put($pageNameNxt, $file);
                        }
                    }
                }

            }
        }
        //転送の時 to グループ
        if($fileNameForward){
            $model->file_name = $fileNameForward;
            $files = explode(',', $fileNameForward);
            foreach ($files as $file){
                $ext = Storage::disk(config('web.imageUpload.disk'));
                $name = explode(':', $file)[0];//fileName
                $pageNamePre = sprintf('upload/g%s/%s', $from_group_id, $name);// コピー元
                $pageNameNxt = sprintf('upload/g%s/%s', $model->group_id, $name);// コピー先
                if ($from_group_id && $ext->exists($pageNamePre) && !$ext->exists($pageNameNxt)) {
                    $file = $ext->get($pageNamePre);
                    $ext->put($pageNameNxt, $file);
                }
            }
        }
        $v = $model->validate();
        if (!$v->fails()) {
            try {
                $idArr = ChatGroup::where('group_id', $model->group_id)->get('user_id')->toArray();
                $group = Group::where('id', $model->group_id)->get();
                //#3258 [チャット]グループチャットでメンション無しの場合、通知を非表示
                if(count($toIdArr) > 0) {//@他人の情報があります
                    $idArray = array_unique(array_filter($toIdArr));
                    foreach ($idArray as $toId){
                        $fireBase = new FirebaseService();
                        if ($toId!= Auth::id()) {
                            $fireBase->pushChatMessage(Auth::user(), $toId, $group[0]);
                        }
                    }
                }elseif (count($idArr) < 3){//一対一で話します
                   $arr = ['[icon:102]', '[icon:101]'];
                    //音声通話やビデオ通話ではなく && 1対1のチャットはプッシュメッセージです
                    if (!in_array($model->message, $arr)) {
                            $fireBase = new FirebaseService();
                            foreach ($idArr as $toId) {
                                if ($toId['user_id'] != Auth::id()) {
                                    $fireBase->pushChatMessage(Auth::user(), $toId['user_id'], $group[0]);
                                }
                            }
                        }
               }
                if($fileNameForward == ''){//転送の時ありません
                    if ($request->get('fileName')) {
                        foreach ($uploadFileName as $file){
                            if($file){
                                $filePath = sprintf('upload/g%s/%s', $model->group_id, $file);
                                $size = Storage::disk(config('web.imageUpload.disk'))->size($filePath);//size(B)
                                $fileSize+=$size;
                                //pdf png は存在しますか
                                $fileNameArr = explode('.', $file);
                                $ext = $fileNameArr[count($fileNameArr) - 1];
                                if (strtoupper($ext) == 'PDF') {
                                    $s3Path  = sprintf('upload/g%s/%s', $model->group_id, $file . '.png');
                                    if(!(Storage::disk(config('web.imageUpload.disk'))->exists($s3Path))){
                                        $this->convertSinglePage($model->group_id, $file, 1);
                                    }
                                }

                                $chatFile = new ChatFile();
                                //ファイルサイズ 'B'
                                $chatFile->group_id = $model->group_id;
                                $chatFile->upload_user_id = Auth::id();
                                $chatFile->file_name = $file;
                                $chatFile->file_size = $size;
                                $chatFile->save();
                                if ($model->file_name) {
                                    $model->file_name = $model->file_name.','.$file.':'.$size;
                                }else{
                                    $model->file_name = $file.':'.$size;
                                }
                            }
                        }
                    }
                }
                $model->save();
                //@メッセージ または Re 1:@ 2:Re
                $isToOrRe = json_decode($request->get("isToOrRe"), true);
                if ($model->message != null && mb_strlen($model->message) > 4) {
                    if ($isToOrRe === 1 && count($toNameArr) > 0) {
                        for ($i = 0; $i < count($toNameArr); $i++) {
                            if (strstr($model->message, '[To]All') || strstr($model->message, $toNameArr[$i])) {
                                $chatPerson = new ChatPerson();
                                $chatPerson->from_user_id = Auth::id();
                                $chatPerson->group_id = $model->group_id;
                                $chatPerson->user_id = $toIdArr[$i];
                                $chatPerson->message_id = $model->id;
                                $chatPerson->flag = 1;
                                $chatPerson->save();
                            }
                        }
                    } else {
                        if ($isToOrRe === 2 && '[Re]' === substr($model->message, 0, 4)) {
                            $chatPerson = new ChatPerson();
                            $chatPerson->from_user_id = Auth::id();
                            $chatPerson->group_id = $model->group_id;
                            $chatPerson->user_id = json_decode($request->get("toUserId"), true);
                            $chatPerson->re_message_id = $model->id;
                            $chatPerson->message_id = json_decode($request->get("toMessageId"), true);
                            $chatPerson->flag = 2;
                            $chatPerson->save();
                        }
                    }
                }
                $chat = ChatLastRead::where('group_id', '=', $model->group_id)->where('user_id', '=',
                    $model->from_user_id)->first();
                if ($chat) {
                    $chat->message_id = $model->id;
                    $chat->save();
                } else {
                    $chatLastRead = new ChatLastRead();
                    $chatLastRead->group_id = $model->group_id;
                    $chatLastRead->user_id = $model->from_user_id;
                    $chatLastRead->message_id = $model->id;
                    $chatLastRead->save();
                }
                //dashboard
                //#3008 1対1のチャット or グループ TO
                if (($group[0]['kind'] == 1) || ($group[0]['kind'] == 0) && strstr($model->message, '[To]')) {
                    $arr = ['[icon:102]', '[icon:101]'];
                    if (!in_array($model->message, $arr)) {
                        $this->setDashboard($model->message, $model->group_id, $toIdArr);
                    }
                }
                //容量統計
                $result=$this->capacity($model->message,$fileSize+$fromSize,$group[0]);
                if($result == 'error'){
                    return $this->error('', trans('messages.error.storageLimit'));
                }
                DB::commit();
                return $this->json('', $model);
            } catch
            (\PDOException $e) {
                DB::rollBack();
                return $this->error($e, trans('messages.error.system'));
            }
        }
        return $this->json($v->errors()->all());
    }
    //クレジット容量
    public function capacity($msg,$fileSize,$group){
        //総容量
        $allSize=strlen($msg)+$fileSize;
        $user_id=Auth::id();
        $userStorage=UserStorage::where('user_id', $user_id)->where('group_id',$group->id)->first();
        if(!$userStorage){
            $capacity=UserStorage::userCapacity($group,$user_id);
            if(!$capacity['type']){
                return 'success';
            }
            $userStorage=new UserStorage();
            $userStorage->group_id=$group->id;
            $userStorage->user_id=$user_id;
            $userStorage->enterprise_id=$capacity['enterprise_id'];
            $userStorage->other_enterprise_id=$capacity['other_enterprise_id'];
            $userStorage->project_id=$capacity['project_id'];
            $userStorage->type=$capacity['type'];
        }
        //クレジット会社容量
        $enterprise_id=$userStorage->other_enterprise_id;
        $enterprise = Enterprise::where('id', $enterprise_id)->first();
        if($enterprise) {
            $enterprise->usedStorage += $allSize;
            if($enterprise->storage*1024*1024*1024 < $enterprise->usedStorage){
                return 'error';
            }
            $enterprise->save();
            $userStorage->chat_storage=$userStorage->chat_storage+strlen($msg);
            $userStorage->chat_file_storage=$userStorage->chat_file_storage+$fileSize;
            $userStorage->save();
        }
        return 'success';
    }


    //削除容量
    public function delCapacity($group,$message_id){
        $user_id=Auth::id();
        $userStorage=UserStorage::where('user_id', $user_id)->where('group_id',$group->id)->first();
        if(!$userStorage){
            $capacity=UserStorage::userCapacity($group,$user_id);
            if(!$capacity['type']){
                return 'success';
            }
            $userStorage=new UserStorage();
            $userStorage->group_id=$group->id;
            $userStorage->user_id=$user_id;
            $userStorage->enterprise_id=$capacity['enterprise_id'];
            $userStorage->other_enterprise_id=$capacity['other_enterprise_id'];
            $userStorage->project_id=$capacity['project_id'];
            $userStorage->type=$capacity['type'];
        }
        //メッセージ容量とファイル容量
        $model=Chatmessage::where('id',$message_id)->first();
        $messageSize = strlen($model->message);
        $fileArr=explode(",",$model->file_name);
        $fileSize=0;
        foreach ($fileArr as $key=>$value){
            $file=explode(":",$value);
            if($file){
                $fileSizeVal=ChatFile::where('file_name',$file[0])->where('group_id',$group->id)->value('file_size');
                if(!$fileSizeVal){
                    $fileSizeVal = Common::getFileSize($group->id, $file[0]);
                }
                $fileSize+=$fileSizeVal;
            }
        }
        $allSize=$messageSize+$fileSize;
        //解放された会社容量
        $enterprise_id=$userStorage->other_enterprise_id;
        $enterprise = Enterprise::where('id', $enterprise_id)->first();
        if($enterprise) {
            if ($enterprise->usedStorage > $allSize) {
                $enterprise->usedStorage = $enterprise->usedStorage - $allSize;
            } else {
                $enterprise->usedStorage = 0;
            }
            $enterprise->save();
            //解放された容量
            if($userStorage->chat_storage > strlen($model->message)){
                $userStorage->chat_storage=$userStorage->chat_storage-strlen($model->message);
            }else{
                $userStorage->chat_storage=0;
            }
            if($userStorage->chat_file_storage > $fileSize){
                $userStorage->chat_file_storage=$userStorage->chat_file_storage-$fileSize;
            }else{
                $userStorage->chat_file_storage=0;
            }
            $userStorage->save();
        }
    }

    public function updateCapacity($group,$oldMsg,$newMsg){
        $user_id=Auth::id();
        $userStorage=UserStorage::where('user_id', $user_id)->where('group_id',$group->id)->first();
        if(!$userStorage){
            $capacity=UserStorage::userCapacity($group,$user_id);
            if(!$capacity['type']){
                return 'success';
            }
            $userStorage=new UserStorage();
            $userStorage->group_id=$group->id;
            $userStorage->user_id=$user_id;
            $userStorage->enterprise_id=$capacity['enterprise_id'];
            $userStorage->other_enterprise_id=$capacity['other_enterprise_id'];
            $userStorage->project_id=$capacity['project_id'];
            $userStorage->type=$capacity['type'];
        }
        $oldMessageSize = strlen($oldMsg);
        $newMessageSize=strlen($newMsg);
        //解放された会社容量
        $enterprise_id=$userStorage->other_enterprise_id;
        $enterprise=Enterprise::where('id',$enterprise_id)->first();
        if($enterprise) {
            $enterprise->usedStorage = $enterprise->usedStorage - $oldMessageSize + $newMessageSize;
            if ($enterprise->usedStorage < 0) {
                $enterprise->usedStorag = 0;
            }
            if($enterprise->usedStorage > $enterprise->storage*1024*1024*1024){
                return 'error';
            }
            $enterprise->save();
            //解放された容量
            $userStorage->chat_storage=$userStorage->chat_storage-$oldMessageSize+$newMessageSize;
            if($userStorage->chat_storage < 0){
                $userStorage->chat_storage=0;
            }
            $userStorage->save();
        }
        return 'success';
    }
    private function getSubstr($string, $start, $length) {
        if (mb_strlen($string, 'utf-8') > $length) {
            $str = mb_substr($string, $start, $length, 'utf-8');
            return $str;
        } else {
            return $string;
        }
    }
    private function setDashboard($msg, $group_id, $toIdArr = [])
    {
        //スタンプが存在する場合の処理
        if(strpos($msg, '[icon')!==false&&strpos($this->getSubstr($msg,0,10), '[')!==false){
            $res=explode(']',$msg);
            if(count($res)>1){
                $msgItem=$res[0].']';
            }else{
                $msgItem=$this->getSubstr($msg,0,10);
            }
        }else{
            $msgItem=$this->getSubstr($msg,0,10);
        }
        $temp = '';
        if ($msg) {
            if (strpos($msgItem, '[qt][time:')!==false) {//引用
                $title = 'メッセージ から引用しました。';
            } elseif (strpos( $msgItem,'■タスクを追加')!==false) {//task - 新规
                $title = 'タスクを完了しました。';
            } elseif (strpos($msgItem,'■タスクを未完了')!==false) {//task - 完了
                $title = 'タスクを未完了に戻しました。';
            } elseif (strpos($msgItem,'■タスクを削除')!==false) {//task - 削除
                $title = 'タスクを削除しました。';
            } else {//メッセージ
                $title = $msgItem;
                if (strlen($msg) > 10) {
                    $temp = '...';
                }
            }
        } else {
            $title = 'ファイルを受信しました。';
        }

        $group = Group::where('id', $group_id)->first();
        $projectGroup = Project::where('group_id', $group_id)->count();
        if ($group->parent_id || $projectGroup) {
            $type = 1;
        } else {
            $type = 0;
        }
        $date = date('m/d H:i');
        if ($group->kind){
            $name = '（'.Auth::user()->name.'、'.$date.'）';
        }else{
            $name = '（'.$group->name.'、'.$date.'）';
        }
        $title = $title . $temp . $name;
        $dashboard = new DashboardController();
        $chatGroups = ChatGroup::where('group_id',$group_id)->where('user_id','!=',Auth::id())
            ->pluck('user_id')->toArray();
        if ($toIdArr && count($toIdArr)>0) {
            // [To] chat
            foreach ($toIdArr as $toIdArrItem){
                $dashboard->addDashboard($group_id, $type, $title, Auth::user()->name.':', $toIdArrItem);
            }
        } else {
            foreach ($chatGroups as $toUserId){
                $dashboard->addDashboard($group_id, $type, $title, Auth::user()->name.':', $toUserId);
            }
        }
    }

    //convert single page
    private function convertSinglePage($groupId, $fileName, $pageNum = 1)
    {
        set_time_limit(0);
        $pdfPath = sprintf('upload/g%s/%s', $groupId, $fileName);

        if (!Storage::disk(config('web.pdfUpload.disk'))->exists($pdfPath)) {
            $pdfStr = Storage::disk(config('web.imageUpload.disk'))->get($pdfPath);
            Storage::disk(config('web.pdfUpload.disk'))->put($pdfPath, $pdfStr);
        }

        $pathToPdf = Storage::disk(config('web.pdfUpload.disk'))->url($pdfPath);

        $pathToWhereImageShouldBeStored = $pathToPdf . '.png';

        $pdf = new Pdf($pathToPdf);
        $pdf->setPage($pageNum)->saveImage($pathToWhereImageShouldBeStored);
        //cover file must store to local,then send to s3 if need
        $s3Path = $pdfPath = sprintf('upload/g%s/%s', $groupId, $fileName . '.png');
        $contentImg = file_get_contents($pathToWhereImageShouldBeStored);
        if(!(Storage::disk(config('web.imageUpload.disk'))->exists($s3Path))){
            Storage::disk(config('web.imageUpload.disk'))->put($s3Path, $contentImg);
        }
    }

    /**
     * チャット情報を改修
     * @param Request $request
     * @return mixed
     */
    public function updateMessage(Request $request)
    {
        try {
            $message=$request->get("message");
            //メッセージ容量
            $model = ChatMessage::where('id', '=', $request->get('id'))->first();
            $group=Group::where('id',$model->group_id)->first();
            //ストレージ容量を更新する
            $result=$this->updateCapacity($group,$model->message,$message);
            if($result == 'error'){
                return $this->error('', trans('messages.error.storageLimit'));
            }
            $model->message = $request->get("message");
            $model->save();
            //メッセージ変更ストレージ
            $changeModel=new ChatMessageChange();
            $changeModel->group_id=$model->group_id;
            $changeModel->message_id=$model->id;
            $changeModel->message=$model->message;
            $changeModel->change_type=1;
            $changeModel->save();
            return $this->json('', $model);
        } catch (\PDOException $e) {
            DB::rollBack();
            return $this->error($e, trans('messages.error.system'));
        }
    }

    /**
     * チャット情報を削除
     * @param Request $request
     * @return mixed
     */
    public function delChatMessage(Request $request)
    {
        try {
            $chatMessage = ChatMessage::where('id', '=', $request->get('id'))->get()->toArray();
            $group=Group::where('id',$chatMessage[0]['group_id'])->first();
            $this->delCapacity($group,$request->get('id'));
            if (count($chatMessage) && $chatMessage[0]['file_name'] && $chatMessage[0]['from_user_id']){
                //複数のファイルの削除
                $chatFileNamesSplit = explode(",",$chatMessage[0]['file_name']);
                foreach ($chatFileNamesSplit as $k =>$v){
                    //「:」を押して、チャットメッセージ内のファイル名を分割します
                    $chatFileNameSplit=explode(":",$v);
                    // $chatFileNameSplit[0] -> file name
                    // $chatFileNameSplit[1] -> file 容量
                    $chatFileName = $chatFileNameSplit[0];
                    ChatFile::where('group_id',$chatMessage[0]['group_id'])->where('file_name',$chatFileName)
                        ->where('upload_user_id',$chatMessage[0]['from_user_id'])->delete();
                }
            }
            ChatMessage::where('id', '=', $request->get('id'))->delete();
            //メッセージ変更ストレージ
            $changeModel=new ChatMessageChange();
            $changeModel->group_id=$chatMessage[0]['group_id'];
            $changeModel->message_id=$chatMessage[0]['id'];
            $changeModel->message=$chatMessage[0]['message'];
            $changeModel->change_type=2;
            $changeModel->save();
            return $this->json();
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }

    /**
     * ユーザーを検索
     * @return array
     */
    public function getSearchPersonList()
    {
        $enterprise = User::where('enterprise_id', Auth::user()->enterprise_id)
            ->whereNotNull('enterprise_id')
            ->where('id', '!=', Auth::id())->get();

        $participant = User::whereHas('enterpriseParticipants', function ($q) {
            $q->where('enterprise_id', Auth::user()->enterprise_id)->where('agree', '=', '1');
        })->orWhere(function ($q){
            $enterpriseParticipant = EnterpriseParticipant::where('user_id',Auth::id())->where('agree', '=', '1')->get()->toArray();
            $res = [];
            foreach ($enterpriseParticipant as $item){
                $res[] = $item['enterprise_id'];
            }
            $q->whereIn('enterprise_id',$res)->pluck('id')->toArray();
        })->where('id', '!=', Auth::id())->get();
        $usersArr = [];
        if (Auth::user()->enterprise_id){
            $usersArr = User::where('enterprise_id', Auth::user()->enterprise_id)->withTrashed()->get('id');
        } elseif (Auth::user()->coop_enterprise_id) {
            $usersArr = User::where('coop_enterprise_id', Auth::user()->coop_enterprise_id)->withTrashed()->get('id');
        }
        $res = [];
        foreach ($usersArr as $user) {
            $res[] = $user['id'];
        }
        $toUser = ChatContact::whereIn('from_user_id', $res)->where('contact_agree', '=', 1)
            ->pluck('to_user_id')->toArray();
        //$fromUser= ChatContact::where('to_user_id', Auth::id())->where('contact_agree', '=', 1)->pluck('from_user_id')->toArray();
        //$usersIdArray = array_unique(array_merge($toUser, $fromUser));
        $usersIdArray=$toUser;
        $group = User::whereIn('id', $usersIdArray)->where('id', '!=', Auth::id())->get()->toArray();
        foreach ($group as $key => $value){
            if($value['enterprise_id'] == Auth::user()->enterprise_id){
                unset($group[$key]);
            }
        }
        $group=array_values($group);
        return ['enterprise' => $enterprise, 'participants' => $participant, 'group' => $group];
    }

    /**
     * 案件グループ以外のチャット連絡先リスト取得
     * @param Request $request
     * @return mixed
     */
    public function getContactsWithoutProjectGroup(Request $request)
    {
        // 個人チャット数を取得
        $directChatCount = DB::table('chatgroups as cg')
        ->select('cg.id')
        ->join('groups as g', function ($join) {
            $join->on('cg.group_id', '=', 'g.id')
            ->where('cg.user_id', '=', Auth::id());
        })->join('chatgroups as cog', function ($join) {
            $join->on('cog.group_id', '=', 'g.id')
            ->where('cog.user_id', '!=', Auth::id());
        })->where('g.kind', '=', 1)->count();

        // グループチャット数を取得
        $groupChatCount = DB::table('chatgroups as cg')
        ->select('cg.id')
        ->join('groups as g', function ($join) {
            $join->on('cg.group_id', '=', 'g.id')
            ->where('cg.user_id', '=', Auth::id())->whereNull('g.parent_id');
        })->leftJoin('projects as p', 'p.group_id', '=', 'g.id')
        ->where('g.kind', '=', 0)->whereNull('p.id')->count();

        $sqlContacts = <<<EOF
    SELECT t.chatgroup_id,
    t.id group_id,
    t.`name` group_name,
    t.kind group_kind,
    t.file group_file,
    t.parent_id,
    ifnull(cm.max_id, 0) max_id,
    t.user_id,
    u.name user_name,
    u.file user_file,
    u.enterprise_id
FROM (
        SELECT g.*,
            cog.id chatgroup_id,
            cog.user_id
        FROM chatgroups cg
            JOIN groups g ON cg.group_id = g.id
            AND cg.user_id = ?
            AND g.deleted_at IS NULL
            JOIN chatgroups cog ON g.id = cog.group_id
            AND cog.user_id != ?
            AND cog.deleted_at IS NULL
        WHERE g.kind = 1
        AND cog.deleted_at IS NULL
        UNION
        SELECT g.*,
            cg.id chatgroup_id,
            cg.user_id
        FROM chatgroups cg
            JOIN groups g ON cg.group_id = g.id
            AND g.parent_id is null
            AND cg.user_id = ?
            AND g.deleted_at IS NULL
        WHERE g.kind = 0
        AND cg.deleted_at IS NULL
    ) t
    LEFT JOIN (
        SELECT `group_id`,
            MAX(id) AS max_id
        FROM `chatmessages`
        WHERE chatmessages.deleted_at IS NULL
        GROUP BY `group_id`
    ) cm ON cm.group_id = t.id
    LEFT JOIN projects p ON p.group_id = t.id
    JOIN users u on t.user_id = u.id
    AND u.deleted_at is null
WHERE p.id is null 
AND p.deleted_at is NULL
ORDER BY cm.max_id desc,
    t.id
EOF;

        $paramContacts = [Auth::id(), Auth::id(), Auth::id()];
        // データサイズを取得
        $size = $request->post('size', config('web.pageSize.chat.contact'));

        if (is_int($size) && $size > 0) {
            // データサイズ＞グループ数の場合、中断
            if ($size > $groupChatCount + $directChatCount + config('web.pageSize.chat.contact')) {
                return [];
            }
            $sqlContacts .= ' LIMIT ?';
            $paramContacts[] = $size;
        }

        $contacts = DB::select($sqlContacts, $paramContacts);

        // 未読数を取得のSQL文
        // SELECT COUNT(*) AS num,
        //     chatmessages.group_id
        // FROM chatmessages
        //     LEFT JOIN chatlastreads ON chatmessages.group_id = chatlastreads.group_id
        //     AND chatlastreads.user_id = 1
        // WHERE chatmessages.id > ifnull(chatlastreads.message_id, 0)
        //     AND chatmessages.group_id IN (1, 2, 3)
        //     AND chatmessages.from_user_id <> 1
        // GROUP BY chatmessages.group_id

        $chatGroups = array_column($contacts, 'group_id');
        // 上記の未読数を取得のSQL文より、Laravelの書き方
        $lastReadMsg = DB::table('chatmessages as cm')
        ->selectRaw(' COUNT(cm.id) AS num, cm.group_id')
        ->leftJoin('chatlastreads as cl', function ($join) {
            $join->on('cm.group_id', '=', 'cl.group_id')
            ->where('cl.user_id', '=', Auth::id());
        })->where('cm.from_user_id', '<>', Auth::id())->whereRaw('cm.id > IFNULL(cl.message_id, 0)')
        ->whereIn('cm.group_id', $chatGroups)->groupBy('cm.group_id')->get()->toArray();

        $chatList = [];
        $userSet = [];
        $group_id_arr = [];
        // 既存WEB画面用データ組み合わせ
        foreach ($contacts as $item) {
            // 重複ユーザーのチャットを非表示
            if (in_array($item->user_id, $userSet) && $item->user_id != Auth::id() ||in_array($item->group_id, $group_id_arr)) {
                continue;
            } else {
                $userSet[] = $item->user_id;
                $group_id_arr[] = $item->group_id;
            }
            $object = [];
            $object['id'] = $item->chatgroup_id;
            $object['user_id'] = $item->user_id;
            $object['group_id'] = $item->group_id;
            // ユーザー情報
            $account = [];
            $account['id'] = $item->user_id;
            $account['name'] = $item->user_name;
            $account['file'] = $item->user_file;
            $account['enterprise_id'] = $item->enterprise_id;
            $object['account'] = $account;
            // チャットグループ情報
            $group = [];
            $group['id'] = $item->group_id;
            $group['name'] = $item->group_name;
            $group['file'] = $item->group_file;
            $group['parent_id'] = $item->parent_id;
            $group['kind'] = $item->group_kind;
            $object['group'] = $group;
            $chatList[] = $object;
        }
        return ['chatList' => $chatList, 'lastReadMsg' => $lastReadMsg];
    }

    /**
     * おしゃべり
     * @return mixed
     * @deprecated 旧ソースで、新メソッドgetContactsWithoutProjectGroup確認の後、削除予定
     */
    public function ChatPersonList()
    {
        $sql = <<<EOF
select  count(*) as num,group_id
from    chatmessages
where   chatmessages.id > IFNULL((
select  message_id
from    chatlastreads
where   chatmessages.group_id = group_id
and     user_id =?
),0)
and chatmessages.group_id in (
select  distinct group_id
from    chatgroups
where   user_id =?)
and     chatmessages.from_user_id <>?
GROUP BY chatmessages.group_id
EOF;
        $lastReadMsg = DB::select($sql, [Auth::id(), Auth::id(), Auth::id()]);
        DB::beginTransaction();
        $groupIdArr = [];
        for ($m = 0; $m < count($lastReadMsg); $m++) {
            $groupIdArr[] = $lastReadMsg[$m]->group_id;
        }
        $chatGroups = ChatGroup::where('user_id', Auth::id())->pluck('group_id')->toArray();
        $arr = ChatGroup::where(function ($q) {
            $q->where(function ($q1) {
                $q1->whereHas('group', function ($q2) {
                    $q2->where('kind', 0);
                });
            });
            $q->orWhere(function ($q1) {
                $q1->whereHas('group', function ($q2) {
                    $q2->where('kind', 1);
                });
                $q1->where('user_id', '!=', Auth::id());
            });
        })
        ->whereIn('group_id', $chatGroups)->with('account', 'group.mine')
            ->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->get()->toArray();

        $msg = ChatMessage::whereIn('group_id',$chatGroups)->select('group_id', DB::raw('max(id) as max_id'))
            ->groupBy('id')->get()->toArray();
        //グループの親レベル情報を取得する
        $res = [];
        $groupArr = [];
        $middleArr = [];
        for ($i = 0; $i < count($arr); $i++) {
            $arr[$i]['num'] = 0;
            $arr[$i]['max_id'] = 0;
            foreach ($lastReadMsg as $item){
                if ($arr[$i]['group_id'] == $item->group_id){
                    $arr[$i]['num'] = $item->num;
                }
            }
            foreach ($msg as $item){
                if ($arr[$i]['group_id'] == $item['group_id']){
                    $arr[$i]['max_id'] = $item['max_id'];
                }
            }
            if ($arr[$i]['group']['parent_id']) {
                $arr[$i]['parent'] = $arr[$i]['group']['mine'];
            }
            if (!in_array($arr[$i]['group_id'],$groupArr) && $arr[$i]['account'] &&
                (($arr[$i]['group']['kind'] ==1 && !in_array($arr[$i]['user_id'],$middleArr))
                    ||($arr[$i]['group']['kind'] == 0))){
                $groupArr[] = $arr[$i]['group_id'];
                if ($arr[$i]['group']['kind'] == 1){
                    $middleArr[] = $arr[$i]['user_id'];
                }
                $res[] = $arr[$i];
            }
        }
        $last_nums = array_column($res,'max_id');
        array_multisort($last_nums,SORT_DESC,$res);
        //project group
        $v=Project::whereIn('group_id',$chatGroups)->pluck('group_id')->toArray();

        $result = [];
        foreach ($res as $item)
        {
            if (in_array($item['group_id'],$v) || $item['group']['parent_id']){
                //
            } else {
                $result[] = $item;
            }
        }
        $lastReadMsgRes = [];
        foreach ($lastReadMsg as $item){
            if (in_array($item->group_id,$chatGroups)){
                $lastReadMsgRes[] = $item;
            }
        }

        return ['chatList' => $result, 'lastReadMsg' => $lastReadMsgRes];
    }

    public function getProjGroupsChatList()
    {
        $sql = <<<EOF
select  count(*) as num,group_id
from    chatmessages
where   chatmessages.id > IFNULL((
select  message_id
from    chatlastreads
where   chatmessages.group_id = group_id
and     user_id =?
AND chatlastreads.deleted_at IS NULL
),0)
and chatmessages.group_id in (
select  distinct group_id
from    chatgroups
where   user_id =?
and chatgroups.deleted_at IS NULL
)
and     chatmessages.from_user_id <>?
and chatmessages.deleted_at IS NULL
GROUP BY chatmessages.group_id
EOF;
        // unread messages
        $lastReadMsg = DB::select($sql, [Auth::id(), Auth::id(), Auth::id()]);
        //parent group
        $groupId = Project::whereHas('projectParticipant',function ($q){
            $q->where('user_id',Auth::id());
        })->pluck('group_id')->toArray();

        $projects = Project::whereIn('group_id',$groupId)->get(['group_id','enterprise_id'])->toArray();
        $groups = Group::where(function ($q) use ($groupId){
                $q->whereIn('groups.id', $groupId)->orWhereIn('parent_id',$groupId);
            })->whereHas('users',function ($q){
                $q->where('users.id',Auth::id());
            })->with(['users.enterprise' => function ($q) {
                $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
            }, 'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1', 'users.file');
            }])->with(['users.enterpriseCoop' => function ($q) {
                $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
            }, 'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1', 'users.file');
            }])->get(['id','name','parent_id','file'])->toArray();

        $resultChatGroupIds = [];
        foreach ($groups as $key=>$group){
            $resultChatGroupIds[] = $group['id'];
        }

        $msg = ChatMessage::whereIn('group_id',$resultChatGroupIds)->select('group_id', DB::raw('max(id) as max_id'))
            ->groupBy('id')->get()->toArray();

        foreach ($groups as $key=>$group){
            $group['enterprise_id'] = 0;
            $group['num'] = 0;
            $group['child'] = [];
            $group['max_id'] = 0;
            foreach ($projects as $project){
                if ($group['id'] == $project['group_id']){
                    $group['enterprise_id'] = $project['enterprise_id'];
                }
            }
            foreach ($lastReadMsg as $item){
                if ($group['id'] == $item->group_id){
                    $group['num'] = $item->num;
                }
            }
            foreach ($groups as $g){
                $g['max_id'] = 0;
                foreach ($msg as $item){
                    if ($g['id'] == $item['group_id']){
                        $g['max_id'] = $item['max_id'];
                    }
                }
                if (($group['id'] == $g['parent_id'] || $group['id'] == $g['id'])){
                    $g['parent_name'] = $group['name'];
                    if ($group['max_id'] < $g['max_id']){
                        $group['max_id'] = $g['max_id'];
                    }
                    $group['child'][] = $g;
                }
                $start_time_ts = array_column($group['child'], 'max_id');//索引付け
                array_multisort($start_time_ts, SORT_DESC, $group['child']);//結果セットのランキング
            }
            $groups[$key] = $group;
        }
        foreach ($groups as $key=>$item){
            foreach ($item['child'] as $k1=>$group){
                foreach ($group['users'] as $k2=>$groupUser){
                    if ($groupUser['id'] == Auth::id()){
                        if ($groupUser['enterprise_id'] == $item['enterprise_id'] && $item['enterprise_id']){
                            $groups[$key]['child'][$k1]['users'][$k2]['pivot']['admin'] = 1;
                        }else{
                            $groups[$key]['child'][$k1]['users'][$k2]['pivot']['admin'] = 0;
                            //案件子グループ新規を制限
                            foreach ($groups[$key]['users'] as $k3=>$userItem){
                                if ($userItem['id'] == $groupUser['id']){
                                    $groups[$key]['users'][$k3]['pivot']['admin'] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        $res = [];
        foreach ($groups as $item){
            if (!$item['parent_id']){
                $res[] = $item;
            }
        }
        $lastReadMsgRes=[];
        foreach ($lastReadMsg as $item){
            if (in_array($item->group_id,$resultChatGroupIds)){
                $lastReadMsgRes[] = $item;
            }
        }
        $start_time_ts = array_column($res, 'max_id');//索引付け
        array_multisort($start_time_ts, SORT_DESC, $res);//結果セットのランキング

        return $this->json('', ['group'=>$res,'lastReadMsg' => $lastReadMsgRes]);
    }

    /**
     * グループの親レベル情報を取得する
     * @param Int $group_id
     * @return Array グループの親情報を含む配列
     */
    private function get_parent_group($group_id)
    {
        $ChatGroupModels = Group::query()->where('id', $group_id)->get();
        $arr = $ChatGroupModels->toArray();
        return $arr[0];
    }

    /**
     * グループを作成
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setGroup(Request $request)
    {
        $user = json_decode($request->post("person"), true);
        $parentId = $request->post("parentId");
        $group = new Group();
        $group->name = $request->post('groupName');
        $group->kind = 0;
        if ($parentId) {
            $group->parent_id = intval($parentId);
        }
        $g = $group->groupValidate();
        $vFile = $group->validateImageFile($request);
        try {
            if (!$vFile->fails() && !$g->fails()) {

                DB::beginTransaction();
                $group->save();
                // dashboard add date
                $dashboard = new DashboardController();
                $gro = Group::find($group->id);
                request()->offsetSet('group_id', $group->id);
                $uploadImg = Common::upload($request, 'groups');
                if ($uploadImg) {
                    $gro->file = $uploadImg;
                }
                $gro->update();

                $chatGroup = new ChatGroup();
                $chatGroup->user_id = Auth::id();
                $chatGroup->group_id = $group->id;
                $chatGroup->admin = 1;
                $chatGroup->save();

                $enterpriseId = Auth::user()->enterprise_id;
                $user = array_unique($user);
                $arr = User::whereIn('id', $user)->get(['id', 'enterprise_id']);
                $type = $this->isProjectGroup($group);
                $date = date('m/d H:i');
                foreach ($arr as $k => $v) {
                    //addDashboard($related_id, $type, $title, $content, $fromUserId, $toUserId)
                    $dashboard->addDashboard(
                        $group->id,$type,'グループチャット（'.$group->name.'）が追加されました。'.'（'.$date.'）','',
                        $v->id);
                    $chatGroup = new ChatGroup();
                    $chatGroup->user_id = $v->id;
                    $chatGroup->group_id = $group->id;
                    if ($enterpriseId == $v->enterprise_id) {
                        $chatGroup->admin = 1;
                    } else {
                        $chatGroup->admin = 0;
                    }
                    $chatGroup->save();

                    $chatList = new ChatList(); // todo APP削除 chatlists,削除コード
                    $chatList->owner_id = $v->id;
                    $chatList->group_id = $group->id;
                    $chatList->top = 0;
                    $chatList->save();

                    $chatLastRead = new ChatLastRead();
                    $chatLastRead->group_id = $group->id;
                    $chatLastRead->user_id = $v->id;
                    $chatLastRead->message_id = 0;
                    $chatLastRead->save();
                }
                $chatLastRead = new ChatLastRead();
                $chatLastRead->group_id = $group->id;
                $chatLastRead->user_id = Auth::id();
                $chatLastRead->message_id = 0;
                $chatLastRead->save();

                $chatList = new ChatList();  // todo todo APP削除 chatlists,削除コード
                $chatList->owner_id = Auth::id();
                $chatList->group_id = $group->id;
                $chatList->top = 0;
                $chatList->save();

            }
            if (!$group->id) {
                $error = trans('messages.error.insert');
                return $this->json($error);
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            $error = trans('messages.error.insert');
            return $this->error($e, [$error]);
        }
        $res = Group::where('id',$group->id)->with(['users' => function ($q) {
            $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.file');
        }])->first();
        $errors = array_merge($g->errors()->all(), $vFile->errors()->all());
        return $this->json($errors, ['gId' => $group->id,'group'=>$res]);
    }

    /**
     * 聊天联系人を削除する
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delChatList(Request $request)
    {
        $groupId = $request->post('id');
        try {
            ChatList::where('owner_id', Auth::id())->where('group_id', $groupId)->delete();
        } catch (\PDOException $e) {
            //データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, [$error]);
        }
        return $this->json();
    }

    /**
     * 置き場聊天联系人
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topChatList(Request $request)
    {
        $groupId = $request->post('id');
        try {
            $chatList = ChatList::where('owner_id', Auth::id())->where('group_id', $groupId)->get();
            foreach ($chatList as $notice) {
                if ($notice->top !== 1) {
                    $notice->top = 1;
                } else {
                    $notice->top = 0;
                    $notice->save();
                    $notice->top = 1;
                }
                $notice->save();
            }
        } catch (\PDOException $e) {
            //データベースエラー
            $error = trans('messages.error.update');
            return $this->error($e, [$error]);
        }
        return $this->json();
    }

    /**
     * ファイルのダウンロード
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadFile(Request $request)
    {
        $index = 1;
        $files = public_path() . str_replace('/shokunin', '', $request->get('path'), $index);
        $name = basename($files);
        return response()->download($files, $name, $headers = ['Content-Type' => 'application/zip;charset=utf-8']);
    }

    /**
     *チャットリストを追加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setChatList(Request $request)
    {
        //todo  #2005変更,コード無効
        $userId = $request->post('userId');
        $groupId = $request->post('groupId');
        $res = ChatList::where('user_id', $userId)->where('group_id', $groupId)->count();
        if (!$res) {
            $count = ChatList::where('owner_id', Auth::id())->where('group_id', $groupId)->count();
            try {
                if ($count < 1) {
                    $chatList = new ChatList();

                    $chatList->owner_id = Auth::id();
                    if ($groupId) {
                        $chatList->group_id = intval($groupId);
                    }
                    if ($userId) {
                        $chatList->user_id = intval($userId);
                    }
                    $chatList->top = 0;
                    $chatList->save();
                } else {
                    $beingList = ChatList::where('owner_id', Auth::id())->where('group_id', $groupId)->get();
                    foreach ($beingList as $notice) {
                        $notice->save();
                    }
                }
            } catch (\PDOException $e) {
                //データベースエラー
                $error = trans('messages.error.update');
                return $this->error($e, [$error]);
            }
        }
        return $this->json();
    }

    /**
     * 仲間ですか
     * @param Request $request
     * @return mixed
     */
    public function getIsFriend(Request $request)
    {
        $flag = 2;
        $fromId = Auth::id();
        $toId = $request->get('id');
        $model = ChatContact::where(function ($query) use ($fromId, $toId) {
            $query->where('from_user_id', '=', $fromId)->where('to_user_id', '=', $toId)->where('contact_agree', '=', 1);
        })->orWhere(function ($query) use ($fromId, $toId) {
            $query->where('from_user_id', '=', $toId)->where('to_user_id', '=', $fromId)->where('contact_agree', '=', 1);
        })->count();
        if ($model > 0) {
            $flag = 1;
        }
        return $flag;
    }

    public function getGroupContact(Request $request)
    {
        $groupArr = [];
        $contactArr = [];
        $enterpriseArr = [];
        $participantsArr = [];
        $userId = Auth::id();
        $groupId = $request->get('groupId');
        $user = Account::where('id', $userId)->first();

        $enterpriseParticipants = EnterpriseParticipant::where('enterprise_id', $user->enterprise_id)
            ->where('agree', 1)->get('user_id')->toArray();
        $users = [];
        if($user->enterprise_id){
            $users = Account::where('enterprise_id', $user->enterprise_id)
                ->pluck('id')->toArray();
        }elseif ($user->coop_nterprise_id){
            $users = Account::where('coop_enterprise_id', $user->coop_enterprise_id)
                ->pluck('id')->toArray();
        }
        $groups = ChatGroup::where('group_id', $groupId)->get('user_id')->toArray();
        $contact = ChatContact::whereIn('from_user_id',$users)->where('contact_agree',1)->pluck('to_user_id')->toArray();

        foreach ($enterpriseParticipants as $participant) {

            $participantsArr[] = $participant['user_id'];
        }
        $enterpriseArr = $users;
        foreach ($groups as $g) {
            $groupArr[] = $g['user_id'];
        }
        $enterpriseArr = array_intersect($enterpriseArr, $groupArr);

        $participantsArr = array_intersect($participantsArr, $groupArr);

        $contactArr = array_intersect($contact, $groupArr);

        $participantsArr = User::whereIn('id', $participantsArr)->with('enterprise')->get(['id', 'name', 'file', 'enterprise_id', 'email']);
        $enterpriseArr = User::whereIn('id', $enterpriseArr)->with('enterprise')->get(['id', 'name', 'file', 'enterprise_id', 'email']);
        $contactArr = User::whereIn('id', $contactArr)->with('enterprise')->get(['id', 'name', 'file', 'enterprise_id', 'email'])->toArray();
        foreach ($contactArr as $key => $value){
            if($value['enterprise_id'] == Auth::user()->enterprise_id){
                unset($contactArr[$key]);
            }
        }
        $contactArr=array_values($contactArr);
        return ['participantsArr' => $participantsArr, 'enterpriseArr' => $enterpriseArr, 'contactArr' => $contactArr];

    }

    public function getGroupUser(Request $request)
    {
        $words = $request->get('words');
        $models = ChatGroup::with('user')->whereHas('user', function ($q) use ($words) {
            $q->where('name', 'LIKE', "%{$words}%");
        })->where('group_id', '=', $request->get('id'))->get();
        foreach ($models as $model) {

            if ($model->admin == 1) {
                $model->isBoss = 1;
            } else {
                $model->isBoss = 2;
            }
        }
        return $this->json("", $models);
    }

    public function fetchChatUserList(Request $request)
    {
        $arr = [];
        $userId = Auth::id();
        $enterpriseId = Auth::user()->enterprise_id;
        $words = $request->get('words');
        $enterprise = User::where('enterprise_id', $enterpriseId)->where('name', 'LIKE', "%{$words}%")->whereNotNull('enterprise_id')->get();
        if(count($enterprise)<1){
            $enterprise=User::where('id', Auth::id())->where('name', 'LIKE', "%{$words}%")->get();
        }
        $participantsArr = [];
        $enterpriseParticipants = EnterpriseParticipant::where('enterprise_id', Auth::user()->enterprise_id)
            ->where('agree', 1)->get('user_id')->toArray();
        foreach ($enterpriseParticipants as $participant) {
            $participantsArr[] = $participant['user_id'];
        }
        $participants = User::whereIn('id', $participantsArr)->where('name', 'LIKE', "%{$words}%")
            ->with('enterprise')
            ->get();

        $usersArr = [];
        if (Auth::user()->enterprise_id){
            $usersArr = User::where('enterprise_id', Auth::user()->enterprise_id)->withTrashed()->get('id');
        } elseif (Auth::user()->coop_enterprise_id) {
            $usersArr = User::where('coop_enterprise_id', Auth::user()->coop_enterprise_id)->withTrashed()->get('id');
        }
        $res = [];
        foreach ($usersArr as $user) {
            $res[] = $user['id'];
        }
        $toUser = ChatContact::whereIn('from_user_id', $res)->where('contact_agree', 1)
            ->pluck('to_user_id')->toArray();
        //$fromUser= ChatContact::where('to_user_id', Auth::id())->where('contact_agree', '=', 1)->pluck('from_user_id')->toArray();
        $usersIdArray = $toUser;
        $friends = User::whereIn('id', $usersIdArray)->where('id', '!=', Auth::id())
            ->where('name', 'LIKE', "%{$words}%")->get()->toArray();
        foreach ($friends as $key => $value){
            if($value['enterprise_id'] == Auth::user()->enterprise_id){
                unset($friends[$key]);
            }
        }
        $friends=array_values($friends);
        return ['enterprise' => $enterprise, 'participants' => $participants, 'friends' => $friends, 'id' => $userId];
    }

    /**
     * チャットグループに入っていない友達を調べます
     * @param Request $request
     * @return array
     */
    public function getGroupFriend(Request $request)
    {
        $enterpriseArr = [];
        $participantsArr = [];
        $usersArr = array();
        $userId = Auth::id();
        $words = $request->get('words');
        $groupId = $request->get('groupId');
        $user = Account::where('id', $userId)->first();

        if (Auth::user()->enterprise_id){
            $usersArr = User::where('enterprise_id', Auth::user()->enterprise_id)->withTrashed()->get('id');
        } elseif (Auth::user()->coop_enterprise_id) {
            $usersArr = User::where('coop_enterprise_id', Auth::user()->coop_enterprise_id)->withTrashed()->get('id');
        }
        $res = [];
        foreach ($usersArr as $userArr) {
            $res[] = $userArr['id'];
        }
        $contactArr = ChatContact::whereIn('from_user_id', $res)->where('contact_agree', '=', 1)
            ->pluck('to_user_id')->toArray();
        $enterprises= EnterpriseParticipant::where('user_id', $userId)->where('agree', 1)->whereNotNull('enterprise_id')->get('enterprise_id')->toArray();
        $part= Account::whereIn('enterprise_id', $enterprises)
            ->where('id', '!=', $userId)->get('id')->toArray();
        $enterpriseParticipants = EnterpriseParticipant::where('enterprise_id', $user->enterprise_id)
            ->where('agree', 1)->get('user_id')->toArray();
        $users = Account::where('enterprise_id', $user->enterprise_id)->whereNotNull('enterprise_id')
            ->where('id', '!=', $userId)->get('id')->toArray();

        $groups = ChatGroup::where('group_id', $groupId)->get('user_id')->toArray();

        foreach ($enterpriseParticipants as $participant) {
            $participantsArr[] = $participant['user_id'];
        }
        foreach ($part as $parts) {
            $participantsArr[] = $parts['id'];
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
        if ($request->get('parentId')) {
            $groupIdArrFn = [];
            $groupsFn = ChatGroup::where('group_id', $request->get('parentId'))->get('user_id')->toArray();
            foreach ($groupsFn as $group) {
                $groupIdArrFn[] = $group['user_id'];
            }
            $enterpriseArr = array_intersect($enterpriseArr, $groupIdArrFn);
            $participantsArr = array_intersect($participantsArr, $groupIdArrFn);
            $contactArr = array_intersect($contactArr, $groupIdArrFn);
        }

        if ($words) {
            $participantsArr = User::whereIn('id', $participantsArr)->where('name', 'LIKE', "%{$words}%")
                ->with('enterprise')
                ->get(['id', 'name', 'file', 'enterprise_id']);
            $enterpriseArr = User::whereIn('id', $enterpriseArr)->where('name', 'LIKE', "%{$words}%")
                ->with('enterprise')->whereNotNull('enterprise_id')
                ->get(['id', 'name', 'file', 'enterprise_id']);
            $contactArr = User::whereIn('id', $contactArr)->where('name', 'LIKE', "%{$words}%")
                ->with('enterprise')
                ->get(['id', 'name', 'file', 'enterprise_id'])->toArray();
        } else {
            $participantsArr = User::whereIn('id', $participantsArr)->with('enterprise')->get(['id', 'name', 'file', 'enterprise_id']);
            $enterpriseArr = User::whereIn('id', $enterpriseArr)->whereNotNull('enterprise_id')->with('enterprise')->get(['id', 'name', 'file', 'enterprise_id']);
            $contactArr = User::whereIn('id', $contactArr)->with('enterprise')->get(['id', 'name', 'file', 'enterprise_id'])->toArray();
        }
        foreach ($contactArr as $key => $value){
            if($value['enterprise_id'] == Auth::user()->enterprise_id){
                unset($contactArr[$key]);
            }
        }
        $contactArr=array_values($contactArr);
        return ['participantsArr' => $participantsArr, 'enterpriseArr' => $enterpriseArr, 'contactArr' => $contactArr];
    }

    /**
     * 友達はチャットグループに入ります
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setFriendToGroup(Request $request)
    {
        $userIdArr = $request->get("userIdArr");
        try {
            $group = Group::where('id', $request->get("groupId"))->get();
            $parentId = $group->toArray();
            $authIdArr = User::where('enterprise_id', Auth::user()->enterprise_id)->get('id')->toArray();
            $toAuthArr = User::whereIn('id', $userIdArr)->get()->toArray();
            $midArr = [];
            foreach ($authIdArr as $id) {
                $midArr[] = $id['id'];
            }
            $chatList = new ChatList();
            $user_id=Auth::id();
            DB::beginTransaction();
            $dashboard = new DashboardController();
            $type = $this->isProjectGroup($group[0]);
            $date = date('m/d H:i');
            foreach ($toAuthArr as $item) {
                $num = DB::table('chatgroups')->where('group_id', $request->get("groupId"))->where('user_id', $item['id'])
                    ->whereNull('deleted_at')->count();
                //addDashboard($related_id, $type, $title, $content, $fromUserId, $toUserId)
                //addDashboard(group_id, 0, 'グループチャット（△△△）に○○さんが追加されました。', '', $fromUserId, $toUserId)
                $dashboard->addDashboard(
                    $request->get("groupId"),$type,'グループチャット（'.$parentId[0]['name'].'）に'.$item['name'].'さんが追加されました。'.'（'.$date.'）','',
                    $item['id']);
                if($num<1){
                    $chatGroup = new ChatGroup();
                    $chatGroup->group_id = $request->get("groupId");
                    if ($parentId && in_array($item['id'], $midArr)) {
                        $chatGroup->admin = 1;
                    } else {
                        $chatGroup->admin = 0;
                    }
                    $chatGroup->user_id = $item['id'];
                    $chatGroup->save();
                    $countNum = DB::table('chatlists')->where('group_id', $chatGroup->group_id)->where('owner_id', $chatGroup->user_id)
                        ->whereNull('deleted_at')->count();
                    if($countNum<1){
                        $chatList->owner_id =$chatGroup->user_id; // todo APP削除 chatlists,削除コード
                        $chatList->group_id =$chatGroup->group_id;
                        $chatList->top = 0;
                        $chatList->save();
                    }
                }
            }
            DB::commit();
            return $this->json();
        } catch (\PDOException $e) {
            DB::rollBack();
            $error = trans('messages.error.insert');
            return $this->error($e, [$error]);
        }
    }

    /**
     * チャットグループを終了
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delInGroup(Request $request)
    {
        try {
            $groupId = $request->get("groupId");
            $userId = $request->get("userId");
            $adminNum=ChatGroup::where('group_id', '=', $groupId)->where('admin', '=', 1)->count();
            $userAdmin=ChatGroup::where('group_id', '=', $groupId)->where('user_id', '=', $userId)->first();
            $user=User::where('id', '=', $userId)->first();
            if($adminNum<=1&&$userAdmin->admin){
                $error=$user->name.'さんを削除すると、このグループを操作できる人がいなくなります。
                そのため、'.$user->name.'さんはこのグループから削除できません。';
                return $this->error('', [$error]);
            }
            $tasks=chattask::where('group_id',$groupId)->pluck('id')->toArray();
            Chattaskcharge::whereIn('task_id',$tasks)->where('user_id',$userId)->delete();
            ChatGroup::where('group_id', '=', $groupId)->where('user_id', '=', $userId)->delete();
            ChatList::where('group_id', '=', $groupId)->where('owner_id', '=', $userId)->delete();  //todo APP削除 chatlists,削除コード
            ChatLike::where('group_id', '=', $groupId)->where('user_id', '=', $userId)->delete();
            $enterpriseMember = ChatGroup::whereHas('user', function ($q) {
                $q->where('enterprise_id', Auth::user()->enterprise_id);
            })->where('group_id', $groupId)->get('id')->toArray();
        } catch (\PDOException $e) {
            $error = trans('messages.error.delete');
            return $this->error($e, [$error]);
        }
        return $this->json("", count($enterpriseMember));
    }

    /**
     * メンバーにタスクを追加します
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUserTask(Request $request)
    {
        try {
            $task = $request->get("task");
            $chatTask = new ChatTask();
            $chatTask->group_id = $task['groupId'];
            $chatTask->create_user_id = Auth::id();
            $chatTask->limit_date = $task['taskDate'];
            $chatTask->notify = $task['notify'];
            if ($task['taskContent']){
                $noteTmp = htmlspecialchars(htmlspecialchars($task['taskContent']));
                $chatTask->note = htmlspecialchars_decode($noteTmp);
            }else{
                $chatTask->note = '';
            }
            $chatTask->message_id = $task['msgId'];
            $chatTask->save();

            $chatTaskCharges = new ChatTaskCharge();
            $chatTaskCharges->task_id = $chatTask->id;
            $chatTaskCharges->user_id = $task['userId'];
            $chatTaskCharges->save();
            return $this->json('', [
                "id" => $chatTask->id,
                "msg" => $chatTask->note,
            ]);
        } catch (\PDOException $e) {
            $error = trans('messages.error.insert');
            return $this->error($e, [$error]);
        }
    }

    public function updateUserTask(Request $request)
    {
        try {
            $model = ChatTask::where('id', '=', $request->get("id"))->first();
            $model->message_id = $request->get('messageId');
            if (!$model->note){
                $model->note = '';
            }
            $model->save();
        } catch (\PDOException $e) {
            $error = trans('messages.error.update');
            return $this->error($e, [$error]);
        }
    }

    /**
     * グループを解散
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearGroup(Request $request)
    {
        DB::beginTransaction();
        try {
            $groupId = $request->get('groupId');
            $group = Group::where('id',$groupId)->first();
            $chatGroup = ChatGroup::where('group_id',$groupId)->where('user_id','!=',Auth::id())->pluck('user_id');
            $dashboard = new DashboardController();
            $type = $this->isProjectGroup($group);
            $date = date('m/d H:i');
            foreach ($chatGroup as $id){
                $dashboard->addDashboard(
                    $groupId, $type, 'グループチャット（'.$group->name.'）が削除されました。'.'（'.$date.'）',
                    '', $id);
            }
            //解散グループ 容量を増やす
            $this->deleteContain($group);
            Group::where('id', '=', $groupId)->delete();
            ChatGroup::where('group_id', '=', $groupId)->delete();
            ChatList::where('group_id', '=', $groupId)->delete();
            ChatTask::where('group_id', '=', $groupId)->delete();
            //2020-11-2 #2298  チャット情報は削除しないことにした
//            ChatMessage::where('group_id', '=', $groupId)->delete();
            DB::commit();
            return $this->json("", true);
        } catch (\PDOException $e) {
            DB::rollBack();
            $error = trans('messages.error.delete');
            return $this->error($e, [$error]);
        }
    }
    /**
     * 解散グループ 容量を増やす
     * @param Request $request
     * @return array
     */
    public function deleteContain($group){
        if($group){
            $storage=UserStorage::where('group_id', '=', $group->id)->get();
            DB::beginTransaction();
            try {
                foreach ($storage as $key => $value) {
                    //解放された会社容量
                    $enterprise_id = $value->other_enterprise_id;
                    $enterprise = Enterprise::where('id', $enterprise_id)->first();
                    $allSize = $value->doc_storage + $value->chat_storage + $value->chat_file_storage;
                    if ($enterprise) {
                        if ($enterprise->usedStorage > $allSize) {
                            $enterprise->usedStorage = $enterprise->usedStorage - $allSize;
                        } else {
                            $enterprise->usedStorage = 0;
                        }
                        $enterprise->save();
                    }
                }
                UserStorage::where('group_id', '=', $group->id)->delete();
                DB::commit();
            }catch (\PDOException $e) {
                DB::rollBack();
            }
        }
    }
    /**
     * 名前、グループ名で検索
     * @param Request $request
     * @return array
     */
    public function searchChatPeopleAndGroup(Request $request)
    {
        $keyword = $request->get('q');
        if (!$keyword) {
            return $this->getPersonList();
        }
        $groupArr = Group::with(['users.enterprise' => function ($q) {
            $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
        }, 'users' => function ($q) {
            $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1');
        }])
            ->with('mine')
            ->with(['users.enterpriseCoop' => function ($q) {
                $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
            }, 'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1');
            }])
            ->whereHas('chatGroup', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->with(['project' => function ($q) {
                $q->select('projects.id', 'projects.group_id');
            }])->whereHas('chatGroup', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->where(function ($q) use ($keyword) {
                $q->where('kind', 0);
                $q->where('groups.name', 'like', "%{$keyword}%");
            })->get()->toArray();

        $userArr = User::where('name', 'like', "%{$keyword}%")->where('id', '!=', Auth::id())->get('id')->toArray();
        $idArr = [];
        foreach ($userArr as $u) {
            $idArr[] = $u['id'];
        }
        $chatMineGroup = ChatGroup::where('user_id', Auth::id())->get('group_id')->toArray();
        $chatSearchGroup = ChatGroup::whereIn('user_id', $idArr)->get('group_id')->toArray();
        $mineIdArr = [];
        foreach ($chatMineGroup as $u) {
            $mineIdArr[] = $u['group_id'];
        }
        $searchIdArr = [];
        foreach ($chatSearchGroup as $u) {
            $searchIdArr[] = $u['group_id'];
        }
        $res = array_intersect($mineIdArr, $searchIdArr);

        $personArr = Group::whereIn('id', $res)->with(['users.enterprise' => function ($q) {
            $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
        }, 'users' => function ($q) {
            $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1');
        }])
            ->with(['users.enterpriseCoop' => function ($q) {
                $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
            }, 'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.email', 'users.telno1');
            }])->whereHas('users', function ($q) use ($idArr) {
                $q->whereIn('users.id', $idArr);
            })
            ->where('kind', 1)->get()->toArray();
        $gro = [];
        $per = [];
        $mineId = Auth::id();
        foreach ($personArr as $g) {
            //friend
            $middleRes = [];
            foreach ($g['users'] as $res) {
                if ($res['id'] != $mineId && !in_array($res['id'],$middleRes)) {
                    $per[] = $res;
                    $middleRes[] = $res['id'];
                }
            }
        }
        foreach ($groupArr as $g) {
            //group
            if ($g['project']) {
                foreach ($personArr as $g1) {
                    if ($g['id'] == $g1['parent_id']) {
                        $g['child'][] = $g1;
                    }
                }
            }
            if (!$g['project'] && $g['parent_id']) {
                $g['search'] = true;
            }
            if (Auth::user()->enterprise_id) {
                $g['addChildGroup'] = true;
            }
            $gro[] = $g;
        }

        $per = $this->array_unique_fb($per);

        return ['group' => $gro, 'person' => $per];
    }

    /**
     * IDに基づいて重複を削除する
     * @param        $array
     * @param string $key
     *
     * @return array
     */
    private function array_unique_fb($array,$key='id'){

        $resArray = [];

        foreach ($array as $value) {
            if(isset($resArray[$value[$key]])){
                //存在の場合
                //削除
                unset($value[$key]);
            }else{
                $resArray[$value[$key]] = $value;
            }
        }

        return $resArray;
    }


    public function createGroup(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = Auth::id();
            $userId = $request->get('userId');
            $chatGroup = ChatGroup::whereHas('mine',function ($q) use ($userId){
                $q->where('user_id', $userId);
            })->where('user_id', $id)->get()->toArray();

            if (count($chatGroup)){
                $chatList = ChatList::where('owner_id', $id)->where('user_id', $userId)->get()->toArray();

                if (count($chatList)) {
                    ChatList::where('owner_id', $id)->where('user_id', $userId)->delete();
                }
                $chatList = new ChatList();
                $chatList->owner_id = $id;
                $chatList->user_id = $userId;
                $chatList->group_id = $chatGroup[0]['group_id'];
                $chatList->top = 0;
                $chatList->save();
                $thisGroupId = $chatGroup[0]['group_id'];
            } else {
                $user = Account::where('id', $userId)->first('name');
                $group = new Group();
                $group->kind = 1;
                $group->name = $user->name;
                $group->save();
                $thisGroupId = $group->id;

                $chatGroupFrom = new ChatGroup();
                $chatGroupFrom->user_id = $id;
                $chatGroupFrom->group_id = $group->id;
                $chatGroupFrom->admin = 1;
                $chatGroupFrom->save();

                $chatGroupTo = new ChatGroup();
                $chatGroupTo->user_id = $userId;
                $chatGroupTo->group_id = $group->id;
                $chatGroupTo->admin = 0;
                $chatGroupTo->save();

                $chatList = new ChatList();
                $chatList->owner_id = $id;
                $chatList->user_id = $userId;
                $chatList->group_id = $group->id;
                $chatList->top = 0;
                $chatList->save();
            }
            $backObj = ChatList::where('owner_id', Auth::id())->where('group_id', $thisGroupId);
            $backObj = $backObj->with(['account', 'group'])->first();
            DB::commit();
            return $this->json('', $backObj);
        } catch (\PDOException $e) {
            DB::rollBack();
            $error = trans('messages.error.create');
            return $this->error($e, [$error]);
        }
    }

    public function setVideoPic(Request $request)
    {
        try {
            //#2538 delete all local cache
//            $this->deleteCache();
            $fileKey = $request->fileKey ?? 'file';
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $ext = $file->getClientOriginalExtension();
                $formatArr = ['JPG', 'JPEG', 'GIF', 'PNG'];
                if (in_array(strtoupper($ext), $formatArr)) {
                    $fileName = Common::upload($request, $request->get("groupId"));
                    return $this->json('', $fileName);
                } else {
                    return $this->json('FormatError', '');
                }
            } else {
                return $this->json('FormatError', '');
            }

        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }

    //upload pdf file and get its path,first page,page num
    public function setVideoPdf(Request $request)
    {
        try {
            //#2538 delete all local cache
            $this->deleteCache();
            $fileKey = $request->fileKey ?? 'file';
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $ext = $file->getClientOriginalExtension();
                $formatArr = ['PDF'];
                if (in_array(strtoupper($ext), $formatArr)) {
                    $fileName = Common::upload($request, $request->get("groupId"));
                    //convert pdf to image
                    set_time_limit(0);
                    $pdfPath = sprintf('upload/g%s/%s', $request->get("groupId"), $fileName);
                    $imageNamePure = $fileName . '_' . 1 . '.png';
                    $imgPath = sprintf('upload/g%s/%s', $request->get("groupId"), $imageNamePure);
                    $imageName = $pdfPath . '_' . 1 . '.png';

                    $imagePath = Storage::disk(config('web.pdfUpload.disk'))->url($imageName);
                    $pathToPdf = Storage::disk(config('web.pdfUpload.disk'))->url($pdfPath);

                    try {
                        $pdf = new Pdf($pathToPdf);
                        $maxPage = $pdf->getNumberOfPages();
                        $pdf->setPage(1)->saveImage($imagePath);

                        //if need to transfer this file to s3,then send it
                        if (!(Storage::disk(config('web.imageUpload.disk'))->exists($imgPath))) {
                            $contentPdf = file_get_contents($pathToPdf);
                            Storage::disk(config('web.imageUpload.disk'))->put($pdfPath, $contentPdf);
                            $contentImg = file_get_contents($imagePath);
                            Storage::disk(config('web.imageUpload.disk'))->put($imgPath, $contentImg);
                        }

                        $arr['uploadedFileName'] = $fileName;
                        $arr['maxPage'] = $maxPage;
                        $arr['imagePath'] = $imageNamePure;
                        return $this->json('', $arr);
                    } catch (\PDOException $e) {
                        return $this->error($e, trans('messages.error.system'));
                    }
                    return $this->json('', $fileName);
                } else {
                    return $this->json('FormatError', '');
                }
            }else{
                return $this->json('FormatError', '');
            }
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }

    /**
     * 現地時間が1日を超えるローカルファイルを削除する
     * User:
     * Date: 2020/11/6 11:29
     */
    public function deleteCache()
    {
        //get path
        $path = Storage::disk(config('web.pdfUpload.disk'))->allFiles('upload');
        for ($i=0;$i<count($path);$i++){
            $fileInfo[$i]['modify'] = Storage::disk(config('web.pdfUpload.disk'))->lastModified($path[$i]);
            $fileInfo[$i]['path'] = $path[$i];
//            TODO: if env APP_UPLOAD_DISK = local
//            $fileInfo[$i]['exists'] = DB::table('chat_files')->where('file_name', basename($path[$i]))->exists();
//            if (((time() - $fileInfo[$i]['modify']) > 86400) and $fileInfo[$i]['exists']==false){
            if (((time() - $fileInfo[$i]['modify']) > 86400)){
                Storage::disk(config('web.pdfUpload.disk'))->delete($fileInfo[$i]['path']);
            }
        }
    }

    //get single page of a pdf file
    public function getPdfPage(Request $request)
    {
        $pdfFileName = $request->input('pdfFileName');
        $groupId = $request->get("groupId");
        $page = $request->input('page');
        try {
            $fileName = $this->convertSinglePagePath($groupId, $pdfFileName, $page);
            return $this->json('', $fileName);
        } catch (\PDOException $e) {
            return $this->error($e, trans('messages.error.system'));
        }
    }

    private function convertSinglePagePath($groupId, $fileName, $pageNum)
    {
        set_time_limit(0);
        $pdfPath = sprintf('upload/g%s/%s', $groupId, $fileName);
        $pathOrg = $fileName . '_' . $pageNum . '.png';
        $imgPath = sprintf('upload/g%s/%s', $groupId, $pathOrg);
        $imageName = $pdfPath . '_' . $pageNum . '.png';
        $imagePath = Storage::disk(config('web.pdfUpload.disk'))->url($imageName);

        //check if this file exists
        $exists = Storage::disk(config('web.imageUpload.disk'))->exists($imageName);

        if ($exists) {
            return $pathOrg;
        } else {
            if (!Storage::disk(config('web.pdfUpload.disk'))->exists($pdfPath)) {
                $pdfStr = Storage::disk(config('web.imageUpload.disk'))->get($pdfPath);
                Storage::disk(config('web.pdfUpload.disk'))->put($pdfPath, $pdfStr);
            }

            $pathToPdf = Storage::disk(config('web.pdfUpload.disk'))->url($pdfPath);

            try {
                $pdf = new Pdf($pathToPdf);
                $pdf->setPage($pageNum)->saveImage($imagePath);
                //if need to send this file to s3,then do it
                if (!(Storage::disk(config('web.imageUpload.disk'))->exists($imgPath))) {
                    $contentImg = file_get_contents($imagePath);
                    Storage::disk(config('web.imageUpload.disk'))->put($imgPath, $contentImg);
                }
                return $pathOrg;
            } catch (\PDOException $e) {
                return $this->error($e, trans('messages.error.system'));
            }
        }
    }



    public function getGroupFileList(Request $request)
    {
        $searchFileWord = Common::escapeDBSelectKeyword($request->get("searchFileWord"));
        $ChatMessageModels =ChatFile::where('group_id', $request->get('groupId'))->where('file_name', '!=', '');
        if ($searchFileWord || $searchFileWord == '0') {
            $ChatMessageModels->where('file_name', 'LIKE', "______________%{$searchFileWord}%");
        }
        $ChatMessageModels = $ChatMessageModels->get(['id','file_name', 'created_at']);
        foreach ($ChatMessageModels as $ChatMessageModel) {
            $ChatMessageModel->name = basename($ChatMessageModel->file_name);
            $ChatMessageModel->fileSize = round(Common::getFileSize($request->get('groupId'), $ChatMessageModel->file_name) / 1024, 2) . 'KB';
            $ChatMessageModel->isPic = false;
            if ($this->isImage($ChatMessageModel->name)) {
                $ChatMessageModel->isPic = true;
            }
        }
        return $ChatMessageModels;
    }

    function isImage($fileName)
    {
        $arr = explode('.', $fileName);
        $fileType = ['jpg', 'jpeg', 'gif', 'bmp', 'png', 'JPG', 'JPEG', 'GIF', 'BMP', 'PNG'];
        if (!in_array($arr[count($arr) - 1], $fileType)) {
            return false;
        }
        return true;
    }

    function isPdf($fileName)
    {
        $arr = explode('.', $fileName);
        $fileType = ['pdf'];
        if (!in_array($arr[count($arr) - 1], $fileType)) {
            return false;
        }
        return true;
    }

    public function getFileSize(Request $request)
    {
        $groupIdArr = ChatGroup::query()->where('user_id', Auth::id())->pluck('group_id');
        $chatFiles = ChatFile::whereIn('group_id', $groupIdArr)->get();
        return $chatFiles;
    }

    public
    function updateLastRead(Request $request)
    {
        $groupId = $request->get('groupId');
        $id = $request->get('id');
        $messageId = $request->get('messageId');
        $lastRead = ChatLastRead::where('group_id', '=', $groupId)->where('user_id', '=', $id)->first();
        if (isset($lastRead)){
            $lastRead->message_id = $messageId;
            $lastRead->save();
        }
        Dashboard::where('related_id', $request->get('groupId'))
            ->where(function ($q) {
                $q->where('type', 0);
                $q->orWhere('type', 1);
            })->where('to_user_id',Auth::id())->update(['read'=>1]);

        return ['messageId' => $messageId];
    }

    public
    function getNewChatList(Request $request)
    {
        try {
            $groupId = $request->get('groupId');
            $chatListObj = ChatList::where('owner_id', Auth::id())->where('group_id', $groupId);
            $chatListObj = $chatListObj->with('account', 'group')->first();
            if ($chatListObj) {
                return $this->json('', $chatListObj);
            } else {
                $kind = Group::where('id', $groupId)->first('kind');
                $chatList = new ChatList();
                $chatList->owner_id = Auth::id();
                $chatList->group_id = $groupId;
                $chatList->top = 0;
                if ($kind->kind == 0) {
                    $groups = ChatGroup::where('group_id', $groupId)->get();
                    foreach ($groups as $group) {
                        if ($group->user_id != $chatList->owner_id) {
                            $chatList->user_id = $group->user_id;
                        }
                    }
                }
                $chatList->save();
                $model = ChatList::where('owner_id', Auth::id())->where('group_id', $groupId);
                $model = $model->with('account', 'group')->first();
                return $this->json('', $model);
            }
        } catch (\PDOException $e) {
            $error = trans('messages.error.create');
            return $this->error($e, [$error]);
        }
    }

    public function editChatTask(Request $request){
        $taskCreateData = $request->get("taskCreateData");
        $chatTask = ChatTask::find($taskCreateData['id']);
        $noteTmp = htmlspecialchars(htmlspecialchars($taskCreateData['note']));
        $taskCreateData['note'] = htmlspecialchars_decode($noteTmp);
        $chatTask->fill($taskCreateData);
        $chatTask->notify=$taskCreateData['notify'];
        // 検証
        $taskValidate = $chatTask->taskValidate();
        if (!$taskValidate->fails()) {
            DB::beginTransaction();
            try {
                $chatTask->save();
                DB::commit();
            } catch (\PDOException $e) {
                // データベースエラー
                $error = trans('messages.error.insert');
                DB::rollBack();
                return $this->error($e, $error);
            }
        }
        return $this->json($taskValidate->errors()->all());
    }

    /**
     * チャットグループの情報を変更します。modify chat group information
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editGroup(Request $request){
        $group = new Group();
        $group->name = $request->get("name");
        $group->kind = 0;
        $g = $group->groupValidate();
        $vFile = $group->validateImageFile($request);
        try {
            if (!$vFile->fails() && !$g->fails()) {

                DB::beginTransaction();
                $gro = Group::find($request->get("id"));
                request()->offsetSet('group_id', $request->get("id"));
                $uploadImg = Common::upload($request, 'groups');
                if ($uploadImg) {
                    $gro->file = $uploadImg;
                }else{
                    $gro->file = $request->get("img");
                }
                $gro->name = $request->get("name");
                $gro->update();
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            $error = trans('messages.error.insert');
            return $this->error($e, [$error]);
        }
        $errors = array_merge($g->errors()->all(), $vFile->errors()->all());
        return $this->json($errors,['img'=>$uploadImg,'name'=>$gro->name]);
    }

    public function getGroup(Request $request){
        $userId = $request->get("userId");
        //get 1vs1 group
        $res = ChatGroup::where('user_id',$userId)->whereHas('mine',function ($q){
            $q->where('user_id',Auth::id());
        })->whereHas('group',function ($q){
            $q->where('kind',1);
        })->pluck('group_id');

        return $res;
    }
    public function getGroupDetailUser(Request $request){
        $groupId = $request->get("id");
        $kind = $request->get("kind");
        $group = Group::where('id',$groupId)->with([
            'users.enterprise' => function ($q) {
                $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
            },
            'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.file', 'users.email', 'users.telno1');
            }
        ])->with([
            'users.enterpriseCoop' => function ($q) {
                $q->select('enterprises.id', 'enterprises.name', 'enterprises.cooperator', 'enterprises.tel');
            },
            'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.file', 'users.email', 'users.telno1');
            }
        ])->get()->toArray();
        $res = [];
        $res['users'] = [];
        foreach ($group[0]['users'] as $item){
            if ($kind && $item['id'] == Auth::id()){

            }else{
                $res['users'][] = $item;
            }
        }

        //#2152【チャット】連絡先に同一人物が2人表示されている
        // user_idを取り除くidの重複値
        $tmp['users'] = [];
        $ids = array();
        foreach($res['users'] as $_arr){
            if(!isset($ids[$_arr['id']])){
                $ids[$_arr['id']] = $_arr['id'];
                $tmp['users'][] = $_arr;
            }
        }
        $res['users'] = $tmp['users'];

        return $this->json('',['group'=>$res]);
    }

    public function getPro(Request $request){
        $groupId = $request->get("id");
        $group = null;
        $num = Project::where('group_id',$groupId)->count();
        if ($num){
            $group = Group::where('id',$groupId)->first();
            $group->parent_id = $group->id;
        }else{
            $group = Group::where('id',$groupId)->whereNotNull('parent_id')->first();
        }

        return $this->json('',['group'=>$group]);
    }
    /**
     * 「いいね」機能の追加
     */
    public function chatLike(Request $request){
        $message_id=$request->input('message_id');
        $group_id=$request->input('group_id');
        $user_id=Auth::id();
        $likeInsert = '';
        $chatLike=ChatLike::where('user_id', $user_id)->where('group_id',$group_id)->first();
        $chatMessage=Chatmessage::where('id',$message_id)->first();
        $group = Group::find($group_id);
        $fireBase = new FirebaseService();
        if(!$chatLike){
            $chatLike=new ChatLike();
            $chatLike->user_id=$user_id;
            $chatLike->group_id=$group_id;
            $chatLike->message_id=$message_id;
            $likeInsert = 'success';
            if ($chatMessage['from_user_id'] != Auth::id()) {
                $fireBase->pushChatMessage(Auth::user(), $chatMessage['from_user_id'], $group ,'message_like');
            }
        }else{
            $midArr=explode(',',$chatLike->message_id);
            if(!in_array($message_id,$midArr)){
                //edit add
                if($chatLike->message_id){
                    $chatLike->message_id=$chatLike->message_id.','.$message_id;
                }else{
                    $chatLike->message_id=$message_id;
                }
                $likeInsert = 'success';
                if ($chatMessage['from_user_id'] != Auth::id()) {
                    $fireBase->pushChatMessage(Auth::user(), $chatMessage['from_user_id'], $group ,'message_like');
                }
            }else{
                //edit cancle
                $midArr=array_diff($midArr,[$message_id]);
                $chatLike->message_id=implode(',',$midArr);
                $likeInsert = 'cancle';
            }
        }
        $chatLike->save();
        return $likeInsert;
    }

    public function getChatLike($groupId){
        $chatlike=ChatLike::where('group_id',$groupId)
            ->join('users','chatlikes.user_id','=','users.id')
            ->get(['user_id','message_id','users.name','users.file'])->toArray();
        return $chatlike;
    }
    /**
     * タスクは存在しますか
     * */
    public function getTask(Request $request){
        $id=$request->input('id');
        $charge = ChatTaskCharge::where('task_id',$id)->where('user_id',Auth::id())->count();
        return $charge;

    }

    public function downloadBatchSelectedChatFile(Request $request)
    {
        //storage setting
        $diskSite = Storage::disk(config('web.imageUpload.disk'));

        //get all nodes from chatfile
        //---------------chat file -----------------
        $chatfileIds = $request->input('selected');

        $files = DB::table("chat_files")
            ->leftJoin("users", "users.id", "=", "chat_files.upload_user_id")
            ->select([
                "chat_files.id",
                "chat_files.group_id",
                "chat_files.file_name",
                "chat_files.file_size",
                "chat_files.created_at",
                "users.name as uploadUserName"
            ])
            ->whereIn("chat_files.id", $chatfileIds)
            ->whereNull("chat_files.deleted_at")
            ->whereNull("users.deleted_at")
            ->get()->toArray();

        $nodeChatDocument = [];
        foreach ($files as $file) {
            $nodeChatDocument[] = $this->getSingleChatGroupFilePath($file->group_id,$file->file_name);
        }

        //no file
        if (count($nodeChatDocument) == 0) {
            return response()->json(['nofile']);
        }

        //create zip file
        $zip = new ZipArchive();

        //create file in public dir
        $prueName = 'batchDownload' . time() . '.zip';
        $zipName = 'public/' . $prueName;
        $diskLocal = Storage::disk('local');
        $diskLocal->put($zipName, '');
        $zip->open(storage_path('app/' . $zipName), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($nodeChatDocument as $fileChatDownLoad) {
            $exi = $diskSite->exists($fileChatDownLoad);
            if ($exi) {
                $fileContent = $diskSite->get($fileChatDownLoad);
                $zip->addFromString(explode('/',$fileChatDownLoad)[2], $fileContent);
            }
        }
        $statusExi = $zip->getNameIndex(0);
        $status = $zip->close();
        $filePathDL = $diskLocal->url( $zipName);

        if ($status && $statusExi) {
            return response()->download($filePathDL, $prueName, $headers = ['Content-Type' => 'application/octet-stream'])
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(['nofile']);
        }
    }

    private function getSingleChatGroupFilePath($groupId,$fileName)
    {
        //make s3 path
        $fileS3Path = sprintf('upload/g%s/%s', $groupId, $fileName);
        return $fileS3Path;
    }
}
