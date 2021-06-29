<?php
/**
 * チャットページのコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Album;
use App\Chatcontact;
use App\Group;
use App\Chatgroup;
use App\Chatmessage;
use App\Chatperson;
use App\Chatlastread;
use App\Chatnews;
use App\Chatsetting;
use App\Chattask;
use App\User;
//use L5Redis;
use DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactSent;

class ChatController extends \App\Http\Controllers\Controller
{

    private $_lastGroupId = null;

    private $_lastDirectId = null;

    private $_userFilePath = '/var/www/laravel/shokunin/storage/app/photo/users/';
    private $_uploadFilePath = '/var/www/laravel/shokunin/storage/app/photo/upload/';
    private $_albumFilePath = '/var/www/laravel/shokunin/storage/app/photo/albums/';

    /**
     * チャットトップページ
     *
     */
    public function index(Request $request, $group_id='')
    {
        if (\Auth::check()) {

            // ユーザー情報を取得
            $toUser   = DB::table('users')->where('id', \Auth::id())->first();

            // コンタクト未承認一覧を取得
            $uncontacts = Chatcontact::getUnContactList(\Auth::id(), $toUser->email);

            // チャットメンバーを取得
            $chatGroupList= Chatgroup::getMemberGroupList(Auth::id());

            // 未読数を取得
            foreach ($chatGroupList as $key => $items) {
                $cnt = Chatmessage::getUnreadCount($items->group_id, Auth::id());
                $chatGroupList[$key]->unread_count = $cnt;
            }

            // 検索キーワード
            $keyword = $request->input('keyword');

            $result = null;
            if ($keyword) {
                $result = Chatcontact::searchUsers(\Auth::id(), $keyword);

                $request->flash();
            }

            if ($group_id == '') {
                $group_id = $request->input('group_id');
            }
            if ($group_id == '') {
                // 最後にチャットしたグループとユーザーを取得
                $group_id  = Chatmessage::lastChatGroup(Auth::id());
            }
            if ($group_id == '') {
                if (is_array($chatGroupList) && !empty($chatGroupList)) {
                    $group_id = $chatGroupList[0]->group_id;
                }
            }

            $messages = null;
            $groups = null;
            $members = null;
            $members2 = null;
            $readMessageId = null;
            $newFlg = null;
            $mode = \Request::input('mode');

            if ($group_id != '') {
                $groups = DB::table('groups')->where('id', $group_id)->first();
                if (empty($groups)) {
                    return redirect('/chat');
                }

                // チャットメッセージを取得
                $messages = Chatmessage::getChatMessage($group_id);

                $data = \Request::all();
                //print_r($data);

                $result  = null;
                $result2 = null;
                if ($mode == 'search') {

                    // 検索キーワード
                    $keyword = \Request::input('keyword');

                    if ($keyword) {
                        $result = Chatgroup::searchUsers($group_id, $keyword);
                        \Request::flash();
                    }

                } else if ($mode == 'message_search') {

                    // 検索キーワード
                    $keyword2 = \Request::input('keyword2');

                    if ($keyword2) {
                        $messages = Chatmessage::searchMessage($group_id, $keyword2);
                        \Request::flash();
                    }
                }

                // チャットメンバーを取得（自分を含まない）
                $members = Chatgroup::getMembers($group_id, \Auth::id());

                // チャットメンバーを取得（自分を含む）
                $members2 = Chatgroup::getMembers2($group_id);

                // 最後に既読したIDを取得
                $readMessageId = Chatlastread::get($group_id, \Auth::id());

                // 最後にチャットしたグループとユーザーを取得
                $this->_lastGroupId  = Chatmessage::lastChatGroup(\Auth::user()->id);
                $this->_lastDirectId = Chatmessage::lastChatUser(\Auth::user()->id);

                // TODO:ちょっとドンくさいのでひとまず保留
                $newFlg = (!empty($messages) && $messages[count($messages)-1]->id > $readMessageId)
                ? ' new' : '';
            }

            $file = DB::table('users')->where('id', \Auth::id())->value('file');

            // 音設定を取得
            $setting = new Chatsetting;
            $sound = $setting->where('name', 'sound')->value('value');

            return view('/chat/index', [
                    'uncontacts'    => $uncontacts,
                    'chatGroupList' => $chatGroupList,

                    'group_id'      => $group_id,
                    'from_user_id'  => \Auth::user()->id,
                    'messages'      => $messages,
                    'groups'        => $groups,
                    'members'       => $members,
                    'members2'      => $members2,
                    'dir'           => 'g'. $group_id,
                    'readMessageId' => $readMessageId,
                    'result'        => $result,
                    'mode'          => $mode,
                    'sound'         => $sound,

                    'lastGroupId'   => $this->_lastGroupId,
                    'lastDirectId'  => $this->_lastDirectId,
                    'newFlg'        => $newFlg,
                    'file'          => $file,
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * チャットコンタクト承認依頼の登録とメール送信
     *
     * @param Request $request
     */
    public function contact(Request $request)
    {
        $to_user_id = $request->input('to_user_id1');
        $message    = $request->input('message1');

        Chatcontact::set(\Auth::id(), $to_user_id, $message);

        $toUser   = DB::table('users')->where('id', $to_user_id)->first();
        $fromUser = DB::table('users')->where('id', \Auth::id())->first();

        $subject = $fromUser->name. 'さんから、チャットのコンタクト承認依頼が来ました。';
        $to = $toUser->email;
        Mail::to($to)->send(new ContactSent('contact', $subject, $message));

        return redirect('/chat');
    }

    /**
     * チャットコンタクト承認の登録とメール送信
     *
     * @param Request $request
     */
    public function agree(Request $request)
    {
        $from_user_id = $request->input('from_user_id');
        $message      = $request->input('message2');

        $toUser   = DB::table('users')->where('id', \Auth::id())->first();
        $fromUser = DB::table('users')->where('id', $from_user_id)->first();

        Chatcontact::setAgree($from_user_id, \Auth::id(), $message, $toUser->email);

        // グループを登録
        $group_id = Group::set($toUser->name. 'さんと'. $fromUser->name. 'さん', 1);

        // メンバーを登録
        Chatgroup::set($group_id, [
                \Auth::id()     => '1',
                $from_user_id   => '1',
        ]);

        $subject = $toUser->name. 'さんから、チャットのコンタクトの承認が来ました。';
        $to = $fromUser->email;
        Mail::to($to)->send(new ContactSent('agree', $subject, $message));

        return redirect('/chat');
    }

    /**
     * チャット設定のページ
     *
     */
    public function setting(Request $request)
    {
        // コンタクト承認一覧を取得
        $contacts= Chatcontact::getContactList(\Auth::id());

        $toUser   = DB::table('users')->where('id', \Auth::id())->first();

        // コンタクト未承認一覧を取得
        $uncontacts = Chatcontact::getUnContactList(\Auth::id(), $toUser->email);

        $others = User::getAllUsers();

        $setting = new Chatsetting;
        $sound = $setting->where('name', 'sound')->value('value');

        // 最後にチャットしたグループとユーザーを取得
        $this->_lastGroupId  = Chatmessage::lastChatGroup(\Auth::user()->id);
        $this->_lastDirectId = Chatmessage::lastChatUser(\Auth::user()->id);

        // 検索キーワード
        $keyword = $request->input('keyword');

        $result = null;
        if ($keyword) {
            $result = Chatcontact::searchUsers(\Auth::id(), $keyword);

            $request->flash();
        }

        return view('/chat/setting', [
                'from_user_id'  => \Auth::user()->id,
                'contacts'      => $contacts,
                'uncontacts'    => $uncontacts,
                'sound'         => $sound,
                'others'        => $others,
                'result'        => $result,

                'lastGroupId'   => $this->_lastGroupId,
                'lastDirectId'  => $this->_lastDirectId,
        ]);
    }

    /**
     * コンタクト追加の登録とメール送信
     *
     * @param Request $request
     */
    public function contactadd(Request $request)
    {
        $email      = $request->input('email');
        $message    = $request->input('message1');

        $validator = $this->validator($request->all())->validate();

        Chatcontact::set(\Auth::id(), $to_user_id=0, $message, $email);

        $fromUser = DB::table('users')->where('id', \Auth::id())->first();

        $subject = $fromUser->name. 'さんから、チャットのコンタクト承認依頼が来ました。';
        $to = $email;
        Mail::to($to)->send(new ContactSent('contact', $subject, $message));

        return redirect('/chat');
    }

    /**
     * 入力検証
     *
     * @param   array   $request
     * @return  結果
     */
    protected function validator($request)
    {
        $validator = Validator::make($request, [
                'email'    => 'required|email|max:255|unique:users',
        ], [
                'email.required'    => 'メールアドレスを入力してください。',
                'email.max'         => 'メールアドレスは255文字以内で入力してください。',
                'email.unique'      => 'メールアドレスが既に登録されています。',
        ]);

        return $validator;
    }

    /**
     * チャットグループの新規作成
     *
     */
    public function groupadd(Request $request)
    {
        $group_name = $request->input('group_name');
        $member     = $request->input('member');

        $tmp = array();
        foreach ($member as $id => $val) {
            if ($val) {
                $tmp[$id] = '0';
            }
        }
        $member = $tmp;
        $member[\Auth::id()] = '1'; // 管理者

        // グループを登録
        $group_id = Group::set($group_name, 0);

        // メンバーを登録
        Chatgroup::set($group_id, $member);

        return redirect('/chat');
    }

    /**
     * チャットのメッセージをデータベースに登録
     *
     * @param   int     $group_id   グループID
     */
    public function createMessage($group_id)
    {
        $this->middleware('auth');

        if (\Auth::check()) {
            $data = \Request::all();
            $data['group_id']       = $group_id;
            $data['from_user_id']   = \Auth::user()->id;

            $name = null;
            if (isset($_FILES["file"]) && $_FILES["file"]["tmp_name"]) {
                list($file_name, $file_type) = explode(".", $_FILES['file']['name']);
                //ファイル名を日付と時刻にしている。
                //$name = $file_name. '_'. date("YmdHis"). '.'. $file_type;
                //$name = $file_name. '_'. $data['timeStamp']. '.'. str_replace('JPG', 'jpg', $file_type);
                $name = $file_name. '_'. $data['timeStamp']. '.'. $file_type;
                $dir = $this->_uploadFilePath. 'g'. $group_id;

                //ディレクトリを作成してその中にアップロードしている。
                if (!file_exists($dir)) {
                    $ret = mkdir($dir, 0755);
                }
                if (move_uploaded_file($_FILES['file']['tmp_name'], $dir. "/". $name)) {
                    chmod($dir."/".$name, 0644);
                    var_dump($dir. "/". $name);
                }
            }

            $data['file_name'] = $name;

            if ($data['mid'] > 0) {
                Chatmessage::updateChatMessage($data['mid'], $data['message'], $data['file_name']);

            } else {
                // チャットメッセージをDBに登録
                //Chatmessage::create($data);
                $message_id = DB::table('chatmessages')->insertGetId([
                        'group_id'      => $group_id,
                        'from_user_id'  => $data['from_user_id'],
                        'message'       => $data['message'],
                        'file_name'     => $data['file_name'],
                        'created_at'    => date('Y/m/d H:i:s')
                ]);
            }

            // Toの相手をDBに登録
            $message = $data['message'];
            $idArray = array();
            $offset = 0;
            while (true){
                $pos = mb_strpos($message, '[To:', $offset);
                if ($pos === false) {
                    break;
                }
                $pos2 = mb_strpos($message, ']', $pos+1);
                $idArray[] = mb_substr($message, $pos+4, $pos2-$pos-4, 'UTF-8');
                $offset = $pos2+1;
            }

            foreach ($idArray as $key => $user_id) {
                Chatperson::set($group_id, $user_id, \Auth::id(), $message_id, null, 1);
            }

            // 返信の相手をDBに登録
            $message = $data['message'];
            $idArray = array();
            $offset = 0;
            while (true){
                $pos = mb_strpos($message, '[mid:', $offset);
                if ($pos === false) {
                    break;
                }
                $pos2 = mb_strpos($message, ']', $pos+1);
                $idArray[] = mb_substr($message, $pos+1, $pos2-$pos-1, 'UTF-8');
                $offset = $pos2+1;
            }

            foreach ($idArray as $key => $item) {
                $tmp = preg_split('/ /', $item);
                $tmp2 = preg_split('/:/', $tmp[0]);
                $tmp3 = preg_split('/:/', $tmp[1]);
                $re_message_id = $tmp2[1];
                $user_id       = $tmp3[1];

                Chatperson::set($group_id, $user_id, \Auth::id(), $message_id, $re_message_id, 2);
            }

        } else {
            //            return redirect('/login');
        }
    }

    /**
     * ユーザー名を返す（Ajaxで呼ばれる）
     *
     * @param   int     $id     ユーザーID
     * @return  string  $name   ユーザー名
     */
    public function getUserName($id)
    {
        $name = User::getUserName($id);

        return $name;
    }

    /**
     * チャットメッセージを削除する
     *
     * @param   int     $id     メッセージID
     */
    public function deleteMessage($id)
    {
        $message = DB::table('chatmessages')->where('id', $id)->first();

        DB::table('chatmessages')->where('id', $id)->delete();

        if ($message->file_name != '') {
            @unlink('/var/www/html/shokunin/upload/g'. $message->group_id. '/'. $message->file_name);
        }

        return $message->toJson();
    }

    /**
     * 既読したメッセージIDをデータベースに登録
     *
     * @param   int     $group_id       グループID
     * @param   int     $message_id     メッセージID
     */
    public function updateReadId($group_id, $message_id)
    {
        $lastRead = new Chatlastread;

        $already = $lastRead->where('group_id', $group_id)
        ->where('user_id', \Auth::user()->id)
        ->first();

        if ($already) {
            $lastRead->where('group_id', $group_id)
            ->where('user_id', \Auth::user()->id)
            ->update([
                    'message_id' => $message_id
            ]);

        } else {
            $lastRead->group_id     = $group_id;
            $lastRead->user_id      = Auth::id();
            $lastRead->message_id   = $message_id;
            $lastRead->save();
        }

        //        $already = Chatlastread::set($group_id, Auth::id(), $message_id);

        return $already->toJson();
    }

    /**
     * チャットニュース
     *
     */
    public function news()
    {
        $news = Chatnews::get();

        // 最後にチャットしたグループとユーザーを取得
        $this->_lastGroupId  = Chatmessage::lastChatGroup(\Auth::user()->id);
        $this->_lastDirectId = Chatmessage::lastChatUser(\Auth::user()->id);

        return view('/chat/news', [
                'news'          => $news,
                'lastGroupId'   => $this->_lastGroupId,
                'lastDirectId'  => $this->_lastDirectId,
        ]);
    }

    /**
     * チャットグループに招待する
     *
     * @param   int     $gid    グループID
     * @param   int     $lk     リンク番号
     */
    public function invite($gid, $lk)
    {
        $groups = DB::table('groups')->where('id', $gid)->first();

        if (\Auth::check()) {
            $lk = '0';
        } else {
            $lk = '001';
        }

        return view('/chat/invite', [
                'link'  => $lk,
                'group' => $groups,
        ]);
    }

    /**
     * メンバー追加処理
     *
     * @param   int     $group_id   グループID
     */
    public function addUser($group_id)
    {
        $data = \Request::all();

        $add[$data['uid']] = '0';

        // メンバーを登録
        Chatgroup::set($group_id, $add);

        return $data->toJson();
    }

    /**
     * メンバー削除処理
     *
     * @param   int     $group_id   グループID
     */
    public function deleteUser($group_id)
    {
        $data = \Request::all();

        $del[$data['uid']] = 1;

        // メンバーを削除
        Chatgroup::deleteUser($group_id, $del);

        return $data->toJson();
    }

    /**
     * 設定を登録する
     *
     * @param Request $request
     * @return unknown
     */
    public function registsetting(Request $request)
    {
        $sound = $request->input('sound');

        $setting = new Chatsetting;

        $already = $setting->where('name', 'sound')->first();

        if ($already) {
            $setting->where('name', 'sound')
            ->update([
                    'value' => $sound,
            ]);

        } else {
            $setting->name  = 'sound';
            $setting->value = $sound;
            $setting->save();
        }

        return redirect('/chatsetting');
    }

    /**
     * チャットグループから退席する
     *
     */
    public function leaveGroup(Request $request) {

        $group_id   = $request->input('group_id');

        if (Auth::check()) {

            $del[Auth::id()] = 1;

            // メンバーを削除
            Chatgroup::deleteUser($group_id, $del);

            return redirect('/chat');
        }

        return redirect('/login');
    }

    /**
     * チャットグループを削除する
     *
     * @return unknown
     */
    public function deleteGroup(Request $request) {

        $group_id   = $request->input('group_id');

        if (Auth::check()) {

            DB::beginTransaction();
            try {
                // チャットメッセージを削除
                DB::table('chatmessages')->where('group_id', $group_id)->delete();

                // チャットグループを削除
                DB::table('albums')->where('group_id', $group_id)->delete();

                // チャットグループを削除
                DB::table('chatgroups')->where('group_id', $group_id)->delete();

                // グループを削除
                DB::table('groups')->where('id', $group_id)->delete();

                // アルバムの写真を削除
                $dir = $this->_albumFilePath. 'g'. $group_id. '/';

                system("rm -rf {$dir}");

                DB::commit();

                //return "Delete OK!!";

            } catch (\PDOException $e){
                DB::rollBack();

                //return "Delete Error!! : ". $e->getMessage();
            }

            return redirect('/chat');
        }

        return redirect('/login');
    }

    /**
     * タスクを登録する
     *
     * @return Request $request
     */
    public function saveTask(Request $request) {

        $group_id       = $request->input('group_id');
        $limit_date     = $request->input('limit_date');
        $note           = $request->input('note');
        $member         = $request->input('member');
//print_r($request->all());
//exit();

        if (Auth::check()) {

            $validator = Validator::make($request->all(), [
                    'limit_date'    => 'required|date',
                    'member'        => 'required',
            ], [
                    'limit_date.required'   => '有効期限を正しく選択してください。',
                    'limit_date.date'       => '有効期限を正しく選択してください。',
                    'member.required'       => 'メンバーを選択してください。',
            ])->validate();

            $request->flash();
            /*
             print_r($member);
             exit();
             */
            if (is_array($member)) {

                DB::beginTransaction();
                try {
                    // タスクを登録
                    $task_id = DB::table('chattasks')->insertGetId([
                            'group_id'          => $group_id,
                            'create_user_id'    => Auth::id(),
                            'limit_date'        => $limit_date,
                            'note'              => $note,
                            'created_at'        => date('Y/m/d H:i:s')
                    ]);

                    // 担当者を登録
                    foreach ($member as $id => $item) {
                        if ($item == 1) {
                            DB::table('chattaskcharges')->insert([
                                    'task_id'       => $task_id,
                                    'user_id'       => $id,
                                    'created_at'    => date('Y/m/d H:i:s')
                            ]);
                        }
                    }

                    $message = "■タスクを追加しました。\n"
                            . $note;

                            // チャットメッセージをDBに登録
                            $message_id = DB::table('chatmessages')->insertGetId([
                                    'group_id'      => $group_id,
                                    'from_user_id'  => Auth::id(),
                                    'message'       => $message,
                                    'created_at'    => date('Y/m/d H:i:s')
                            ]);

                            DB::commit();

                } catch (\PDOException $e){
                    //echo 'まさかのエラー？ '.$e->getMessage();
                    DB::rollBack();
                }
            }

            return redirect('/chat/'. $group_id);
        }

        return redirect('/login');
    }

    /**
     * タスク一覧
     *
     * @param   int $group_id
     * @return  unknown
     */
    public function chatTaskList($group_id) {

        if (!Auth::check()) {
            return redirect('/login');
        }
        // チャットメンバーを取得
        $chatGroupList= Chatgroup::getMemberGroupList(Auth::id());

        $groups = DB::table('groups')->where('id', $group_id)->first();

        $list = Chattask::get($group_id, Auth::id());

        // チャットメンバーを取得（自分を含まない）
        $members = Chatgroup::getMembers($group_id, \Auth::id());

        // チャットメンバーを取得（自分を含む）
        $members2 = Chatgroup::getMembers2($group_id);

        return view('/chat/tasklist', [
                'user_id'       => Auth::id(),
                'chatGroupList' => $chatGroupList,
                'groups'        => $groups,
                'group_id'      => $group_id,
                'list'          => $list,
                'members'       => $members,
                'members2'      => $members2,
                'dir'           => 'g'. $group_id,
        ]);
    }

    /**
     * タスクを削除する
     *
     * @return Request $request
     */
    public function deleteChatTask(Request $request) {

        $task_id   = $request->input('task_id');
        $group_id   = $request->input('group_id');

//return print_r($request->all());
//exit();

        if (Auth::check()) {

            DB::table('chattaskcharges')->where('task_id', $task_id)->delete();

            DB::table('chattasks')->where('id', $task_id)->delete();

            $message = "■タスクを削除しました。\n". $note;

            // チャットメッセージをDBに登録
            $message_id = DB::table('chatmessages')->insertGetId([
                    'group_id'      => $group_id,
                    'from_user_id'  => Auth::id(),
                    'message'       => $note,
                    'created_at'    => date('Y/m/d H:i:s')
            ]);

            return redirect('/chattasklist/gid/'. $group_id);
        }

        return redirect('/login');
    }

    /**
     * タスクを更新する
     *
     * @return unknown
     */
    public function updateChatTask(Request $request) {

        $task_id    = $request->input('task_id');
        $note       = $request->input('note');
        $limit_date = $request->input('limit_date');
        $group_id   = $request->input('group_id');
        //        $member     = $request->input('member');
//print_r($request->all());
//exit();

        if (Auth::check()) {

            DB::table('chattasks')->where('id', $task_id)
            ->update([
                    'note'          => $note,
                    'limit_date'    => $limit_date,
            ]);

            $message = "■タスクを更新しました。\n". $note;

            // チャットメッセージをDBに登録
            $message_id = DB::table('chatmessages')->insertGetId([
                    'group_id'      => $group_id,
                    'from_user_id'  => Auth::id(),
                    'message'       => $note,
                    'created_at'    => date('Y/m/d H:i:s')
            ]);

            return redirect('/chattasklist/gid/'. $group_id);
        }

        return redirect('/login');
    }

    /**
     * タスクを完了させる
     *
     * @return unknown
     */
    public function completeChatTask(Request $request) {

        $group_id  = $request->input('group_id');
        $task_id   = $request->input('task_id');
        $note      = $request->input('note');

        if (Auth::check()) {

            DB::beginTransaction();
            try {

                DB::table('chattasks')->where('id', $task_id)
                ->update([
                        'complete_date' => date('Y/m/d'),
                ]);

                $message = "■タスクを完了しました。\n". $note;

                // チャットメッセージをDBに登録
                $message_id = DB::table('chatmessages')->insertGetId([
                        'group_id'      => $group_id,
                        'from_user_id'  => Auth::id(),
                        'message'       => $message,
                        'created_at'    => date('Y/m/d H:i:s')
                ]);

                DB::commit();

            } catch (\PDOException $e){
                //echo 'まさかのエラー？ '.$e->getMessage();
                DB::rollBack();
            }

            return redirect('/chattasklist/gid/'. $group_id);
        }

        return redirect('/login');
    }

    /**
     * アルバム一覧
     *
     * @param unknown $group_id
     * @return unknown
     */
    public function album($group_id) {

        if (!Auth::check()) {
            return redirect('/login');
        }
        // チャットメンバーを取得
        $chatGroupList= Chatgroup::getMemberGroupList(Auth::id());

        $groups = DB::table('groups')->where('id', $group_id)->first();

        $albums = Album::get($group_id, Auth::id());
//print_r($albums);

        return view('/chat/album', [
                'user_id'       => Auth::id(),
                'chatGroupList' => $chatGroupList,
                'groups'        => $groups,
                'group_id'      => $group_id,
                'albums'        => $albums,
        ]);
    }


    /**
     * アルバムを作成する
     *
     * @return unknown
     */
    public function makeAlbum(Request $request) {

        $group_id   = $request->input('group_id');
        $album_name = $request->input('album_name');

        if (!Auth::check()) {
            return redirect('/login');
        }

        $already = DB::table('albums')
            ->where('group_id', $group_id)
            ->where('user_id',  \Auth::user()->id)
            ->where('name',     $album_name)
            ->first();

        if (!$already) {

            DB::beginTransaction();
            try {
                $id = DB::table('albums')->insertGetId([
                        'group_id'      => $group_id,
                        'user_id'       => Auth::id(),
                        'name'          => $album_name,
                        'created_at'    => date('Y/m/d H:i:s')
                ]);

                 $message = "■アルバム「". $album_name. "」を追加しました。\n";

                 // チャットメッセージをDBに登録
                 $message_id = DB::table('chatmessages')->insertGetId([
                 'group_id'      => $group_id,
                 'from_user_id'  => Auth::id(),
                 'message'       => $message,
                 'created_at'    => date('Y/m/d H:i:s')
                 ]);

                DB::commit();

            } catch (\PDOException $e){
                //echo 'まさかのエラー？ '.$e->getMessage();
                DB::rollBack();
            }

        } else {
            $id = $already->id;
        }

        return redirect('/chatalbum/gid/'. $group_id);
    }

    /**
     * アルバムにファイルを追加する
     *
     * @return unknown
     */
    public function addFilesToAlbum() {

        $group_id   = $_POST['group_id'];
        $user_id    = $_POST['user_id'];
        $album_id   = $_POST['album_id'];
//        $file_name  = $_POST['file_name'];

        if (isset($_FILES["upfile"]) && $_FILES["upfile"]["tmp_name"]) {

            $file_name = $_FILES["upfile"]["name"];

            $dir1 = $this->_albumFilePath. 'g'. $group_id;

            $dir2 = $dir1. '/u'. $user_id;
            $dir3 = $dir2. '/a'. $album_id;

            //ディレクトリを作成してその中にアップロードしている。
            if (!file_exists($dir1)) {
                //$ret = mkdir($dir, 0755);
                $ret = mkdir($dir1, 0777);
                echo "ret=". $ret. '<br>';
            }
            if (!file_exists($dir2)) {
                $ret = mkdir($dir2, 0777);
            }
            if (!file_exists($dir3)) {
                $ret = mkdir($dir3, 0777);
            }

            if (move_uploaded_file($_FILES['upfile']['tmp_name'], $dir3. "/". $file_name)) {
                chmod($dir3. "/". $file_name, 0644);
                //        var_dump($dir. "/". $name);
//                echo "ファイル ".  basename( $_FILES['upfile']['name']). " をアップロードしました。";
            } else{
//                echo "エラーが発生しました。";
            }
        }

        return redirect('/chatalbum/gid/'. $group_id);
    }

    /**
     * アルバム削除
     *
     * @return unknown
     */
    public function deleteAlbum(Request $request) {

        $group_id   = $request->input('group_id');
        $album_id   = $request->input('album_id');

        if (!Auth::check()) {
            return redirect('/login');
        }

        $already = DB::table('albums')->where('id', $album_id)->first();

        DB::table('albums')->where('id', $album_id)->delete();

        $dir1 = $this->_albumFilePath. 'g'. $group_id;
        $dir2 = $dir1. '/u'. Auth::id();
        $dir  = $dir2. '/a'. $album_id. '/';

        if (file_exists($dir)) {
            if ( $dirHandle = opendir ( $dir )) {
                while ( false !== ( $fileName = readdir ( $dirHandle ) ) ) {
                    if ( $fileName != "." && $fileName != ".." ) {
                        unlink ( $dir. $fileName );
                    }
                }
                closedir ( $dirHandle );
                rmdir($dir);
            }
        }

        $message = "■アルバム「". $already->name . "」を削除しました。\n";

        // チャットメッセージをDBに登録
        $message_id = DB::table('chatmessages')->insertGetId([
                'group_id'      => $group_id,
                'from_user_id'  => Auth::id(),
                'message'       => $message,
                'created_at'    => date('Y/m/d H:i:s')
        ]);

        return redirect('/chatalbum/gid/'. $group_id);
    }

    /**
     * アルバムから写真削除
     *
     * @return unknown
     */
    public function deletePhoto(Request $request) {

        $group_id   = $request->input('group_id');
        $album_id   = $request->input('album_id');
        $file_name  = $request->input('file_name');

        if (!Auth::check()) {
            return redirect('/login');
        }

        $already = DB::table('albums')
        ->where('id', $album_id)->first();

        $dir1 = $this->_albumFilePath. 'g'. $group_id;
        $dir2 = $dir1. '/u'. Auth::id();
        $dir  = $dir2. '/a'. $album_id. '/';

        $tmp = explode('/', $file_name);
        if (count($tmp) > 0) {
            $file_name = $tmp[count($tmp)-1];
        }

        if ( $dirHandle = opendir ( $dir )) {
            while ( false !== ( $fileName = readdir ( $dirHandle ) ) ) {

                if ( $fileName == $file_name ) {
                    unlink ( $dir. $fileName );
                    break;
                }
            }
            closedir ( $dirHandle );
        }

        $message = "アルバム「". $already->name. "」から写真を1枚削除しました。\n";

        // チャットメッセージをDBに登録
        $message_id = DB::table('chatmessages')->insertGetId([
                'group_id'      => $group_id,
                'from_user_id'  => Auth::id(),
                'message'       => $message,
                'created_at'    => date('Y/m/d H:i:s')
        ]);

        return redirect('/chatalbum/gid/'. $group_id);
    }

    /**
     * アルバム名変更
     *
     * @return unknown
     */
    public function renameAlbum(Request $request) {

        $group_id   = $request->input('group_id');
        $album_id   = $request->input('album_id');
        $album_name = $request->input('new_album_name');

        if (!Auth::check()) {
            return redirect('/login');
        }

        $already = DB::table('albums')
        ->where('id', $album_id)->first();

        DB::table('albums')->where('id', $album_id)
        ->update([
                'name' => $album_name
        ]);

        $message = "■アルバム「". $already->name. "」を「". $album_name. "」に変更しました。\n";

        // チャットメッセージをDBに登録
        $message_id = DB::table('chatmessages')->insertGetId([
                'group_id'      => $group_id,
                'from_user_id'  => Auth::id(),
                'message'       => $message,
                'created_at'    => date('Y/m/d H:i:s')
        ]);

        return redirect('/chatalbum/gid/'. $group_id);
    }

    /**
     * ユーザーの画像を表示する
     */
    public function photo($id)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = User::find($id);
        if ($user->file) {
            //$filename = $this->_userFilePath. $id. '.jpg';
            $filename = $this->_userFilePath. $user->file;

            ob_start();
            header("Content-type: image/jpeg name=$filename");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-Length: ".@filesize($filename));
            header("Expires: 0");
            @readfile($filename);
            $buffer = ob_get_contents();
            ob_end_clean();
            //exit;
            return $buffer;
        }
    }

    /**
     * 添付画像を表示する
     */
    public function uploadImage($group_id, $file_name, $extension)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $dir = $this->_uploadFilePath. 'g'. $group_id. '/';
        $filename = $file_name. '.'. $extension;

        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
            while( ($file = readdir($handle)) !== false ) {
                if( filetype( $path = $dir . $file ) == "file" ) {
                    // $file: ファイル名
                    // $path: ファイルのパス
                    if ($file == $filename) {
                        ob_start();
                        header("Content-type: image/jpeg name=$filename");
                        header("Content-Disposition: attachment; filename=$filename");
                        header("Content-Length: ".@filesize($dir. '/'. $filename));
                        header("Expires: 0");
                        @readfile($dir. '/'. $filename);
                        $buffer = ob_get_contents();
                        ob_end_clean();
                        return $buffer;
                    }
                }
            }
        }

        // ファイルがなかった時
        $filename = $this->_uploadFilePath. 'not_found.png';
        ob_start();
        header("Content-type: image/jpeg name=$filename");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Length: ".@filesize($filename));
        header("Expires: 0");
        @readfile($filename);
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    /**
     * アルバムの画像を表示する
     */
    public function albumImage($group_id, $album_id, $file_name, $extension)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $dir = $this->_albumFilePath. 'g'. $group_id. '/u'. Auth::id(). '/a'. $album_id. '/';
        $filename = $file_name. '.'. $extension;

        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
            while( ($file = readdir($handle)) !== false ) {
                if( filetype( $path = $dir . $file ) == "file" ) {
                    // $file: ファイル名
                    // $path: ファイルのパス
                    if ($file == $filename) {
                        ob_start();
                        header("Content-type: image/jpeg name=$filename");
                        header("Content-Disposition: attachment; filename=$filename");
                        header("Content-Length: ".@filesize($dir. '/'. $filename));
                        header("Expires: 0");
                        @readfile($dir. '/'. $filename);
                        $buffer = ob_get_contents();
                        ob_end_clean();
                        //exit;
                        return $buffer;
                    }
                }
            }
        }

        // ファイルがなかった時
        $filename = $this->_uploadFilePath. 'not_found.png';
        ob_start();
        header("Content-type: image/jpeg name=$filename");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Length: ".@filesize($filename));
        header("Expires: 0");
        @readfile($filename);
        $buffer = ob_get_contents();
        ob_end_clean();
    }
}
