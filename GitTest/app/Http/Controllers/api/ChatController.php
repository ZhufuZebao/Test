<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Response;
use App\User;
use App\Group;
use App\Chatgroup;
use App\Chatcontact;
use App\Chatmessage;
use App\Chatlastread;
use App\Chatperson;
use App\Chattask;
use App\Chattaskcharge;
use App\Album;
use DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactAgreeSend;
use App\Mail\ContactRequestSend;

class ChatController extends Controller
{
    private $_uploadGroupPath = '/var/www/laravel/shokunin/storage/app/photo/groups/';
    private $_uploadFilePath = '/var/www/laravel/shokunin/storage/app/photo/upload/';
    private $_albumFilePath = '/var/www/laravel/shokunin/storage/app/photo/albums/';

    public function __construct(){
        $this->content = array();
    }

    /**
     * チャットのメンバー（ダイレクトチャット、グループチャット）を取得
     *
     * @return json
     */
    public function getMember(Request $request){

        $kind = $request->input('kind');

        $member= Chatgroup::getMemberGroupList(Auth::id(), $kind);
        if (empty($member)) {
            return 'nodata';
        }

        foreach ($member as $key => $items) {
            $member[$key]->unread_count = Chatmessage::getUnreadCount($items->group_id, Auth::id());
            $member[$key]->member_count = Chatgroup::getMemberCount($items->group_id);
        }
        return response()->json($member);
    }

    /**
     * 未承認メンバーを取得
     *
     * @return json
     */
    public function getUnapprovedMember(){

        $user   = DB::table('users')->where('id', Auth::id())->first();

        // コンタクト未承認一覧を取得
        $uncontacts = Chatcontact::getUnContactList(Auth::id(), $user->email);

        return response()->json($uncontacts);
    }

    /**
     * 未承認ユーザーを承認する
     *
     * @return unknown
     */
    public function setAgreement(Request $request) {

        $user_id = $request->input('user_id');
        $message = $request->input('message');
        $agree   = $request->input('agree');


        $toUser   = DB::table('users')->where('id', Auth::id())->first();
        $fromUser = DB::table('users')->where('id', $user_id)->first();

        // 承認
        if ($agree == 1) {
            DB::beginTransaction();
            try {

                Chatcontact::setAgree($user_id, Auth::id(), $message, $toUser->email);

                // グループを登録
                $group_name = $toUser->name. 'さんと'. $fromUser->name. 'さん';
                $group_id = Group::set($group_name, 1);

                // メンバーを登録
                Chatgroup::set($group_id, [
                        Auth::id() => '1',
                        $user_id   => '1',
                ]);

                // メール送信
                $to = $fromUser->email;
                Mail::to($to)->send(new ContactAgreeSend($toUser->name, $message));

                if (Mail::failures()) {
                    return 'error';
                }

                $data = [
                    'group_id'  => $group_id,
                    'name'      => $group_name
                ];

                DB::commit();
                return response()->json($data);

            } catch (\PDOException $e){
                DB::rollBack();
                return 'error';
            }

        // 拒否
        } else {

            DB::beginTransaction();
            try {

                Chatcontact::setAgree($user_id, Auth::id(), $message, $toUser->email, "2");

                DB::commit();
                return "success";

            } catch (\PDOException $e){
                DB::rollBack();
                return 'error';
            }

        }
    }

    /**
     * コンタクト承認依頼の登録とメール送信
     *
     */
    public function setContact(Request $request) {

        $email      = $request->input('email');
        $message    = $request->input('message');

        $result = Chatcontact::checkAlreadyContact(Auth::id(), $email);
        if (count($result) > 0) {
            return 'already';
        }

        try {
            DB::beginTransaction();
            Chatcontact::set(Auth::id(), $to_user_id=0, $message, $email);

            $fromUser = DB::table('users')->where('id', Auth::id())->first();

            $url = url('/', null, true).'/invitation/'.$fromUser->id.'/chat';
            $to = $email;
            Mail::to($to)->send(new ContactRequestSend($fromUser->name, $message, $url));

            if (Mail::failures()) {
                DB::rollBack();
                return 'error';
            }

            DB::commit();
            return 'success';

        } catch (Exception $e) {
            DB::rollBack();
            return 'exception';
        }
    }

    /**
     * コンタクト承認依頼の登録とメール送信
     *
     */
    public function setContactRequest(Request $request) {

        $user_id = $request->input('user_id');
        $message = $request->input('message');

        DB::beginTransaction();
        Chatcontact::set(Auth::id(), $user_id, $message);

        $toUser   = DB::table('users')->where('id', $user_id)->first();
        $fromUser = DB::table('users')->where('id', Auth::id())->first();

        $url = url('/', null, true).'/invitation/'.$fromUser->id.'/chat';
        $to = $toUser->email;
        Mail::to($to)->send(new ContactRequestSend($fromUser->name, $message, $url));

        if (Mail::failures()) {
            DB::rollBack();
            return 'error';
        }

        DB::commit();

        return 'success';
    }

    /**
     * 既に登録済みのユーザーを検索
     *
     * @return json
     */
    public function searchUser(Request $request) {

        $search_keyword = $request->input('search_keyword');
        $search_target  = $request->input('search_target');

        if ($search_target == 'all') {
            $result = Chatcontact::searchUsers(Auth::id(), $search_keyword);
        } else {
            $result = Chatcontact::searchUsers2(Auth::id(), $search_keyword);
        }

        //if (empty($result)) {
        //    return '';
        //}

        return response()->json($result);
    }

    /**
     * チャットグループにメンバーを追加
     *
     */
    public function addMember(Request $request) {

        $group_id = $request->input('group_id');
        $member   = $request->input('member');

        $tmp = array();
        foreach ($member as $val) {
            $tmp[$val] = '0';
        }

        // メンバーを登録
        try {
            Chatgroup::set($group_id, $tmp);
            return 'success';

        } catch (\PDOException $e){
            return 'error';
        }
    }

    /**
     * チャットグループからメンバーを削除
     *
     */
    public function deleteMember(Request $request) {

        $group_id = $request->input('group_id');
        $member   = $request->input('member');

        $tmp = array();
        foreach ($member as $val) {
            $tmp[$val] = 1;
        }

        // メンバーを削除
        try {
            Chatgroup::deleteUser($group_id, $tmp);
            return 'success';

        } catch (\PDOException $e){
            return 'error';
        }

        return 'error';
    }

    /**
     * チャットグループに登録されているメンバー（自分を含まない）を取得
     *
     * @return json
     */
    public function getGroupChatMember(Request $request) {

        $group_id = $request->input('group_id');

        // チャットメンバーを取得
        $members = Chatgroup::getMembers($group_id, Auth::id());
        if (empty($members)) {
            return 'nodata';
        }

        return response()->json($members);
    }

    /**
     * 過去のチャットメッセージを取得する
     *
     * @return json
     */
    public function getChatMessages(Request $request) {

        $group_id = $request->input('group_id');

        // チャットメッセージを取得
        $messages = Chatmessage::getChatMessage($group_id);
        if (empty($messages)) {
            return 'nodata';
        }

        foreach ($messages as $key => $items) {
            $items->message  = $this->_showHtmlChatMessage($items->message);

            $items->image = '';
            /*
            if ($items->file_name != '') {

                // ディレクトリのパス
                $dir = $this->_uploadFilePath. 'g'. $group_id;

                if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
                    while( ($file = readdir($handle)) !== false ) {
                        if( filetype( $path = $dir . '/'. $file ) == "file" ) {
                            // $file: ファイル名
                            // $path: ファイルのパス
                            if ($file == $items->file_name) {
                                $encoded_data = base64_encode(file_get_contents($path));
                                $items->image = $encoded_data;
                            }
                        }
                    }
                }

            }
            */
            $messages[$key] = $items;
        }

        return response()->json($messages);
    }

    /**
     * チャットメンバー（自分を含む）を取得する
     *
     * @return json
     */
    public function getChatMember(Request $request) {

        $group_id = $request->input('group_id');

        // チャットメンバーを取得
        $members = Chatgroup::getMembers2($group_id);
        if (empty($members)) {
            return 'nodata';
        }

        return response()->json($members);
    }

    /**
     * チャットメッセージをDBに登録する
     *
     */
    public function setMessage(Request $request) {

        $group_id       = $request->input('group_id');
        $message        = $request->input('message');
        $file_name      = $request->input('file_name');
        $message_id     = $request->input('mid');   // 編集の時だけ来る
        $from_group_id  = $request->input('from_group_id');

        if ($message_id > 0) {
            Chatmessage::updateChatMessage($message_id, $message, $file_name);

        } else {
            // チャットメッセージをDBに登録
            $message_id = DB::table('chatmessages')->insertGetId([
                    'group_id'      => $group_id,
                    'from_user_id'  => Auth::id(),
                    'message'       => $message,
                    'file_name'     => $file_name,
                    'created_at'    => date('Y/m/d H:i:s')
            ]);
        }

        // Toの相手をDBに登録
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
            Chatperson::set($group_id, $user_id, Auth::id(), $message_id, null, 1);
        }

        // 返信の相手をDBに登録
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

            Chatperson::set($group_id, $user_id, Auth::id(), $message_id, $re_message_id, 2);
        }

        // 転送の時
        if ($file_name != null && $from_group_id != null) {
            if (strpos($message, '▼ From:') !== false) {
                $dir0 = $this->_uploadFilePath. 'g'. $from_group_id;  // コピー元
                $dir  = $this->_uploadFilePath. 'g'. $group_id;       // コピー先

                //ディレクトリを作成してその中にアップロードしている。
                if (!file_exists($dir)) {
                    //$ret = mkdir($dir, 0755);
                    $ret = mkdir($dir, 0777);
                }
                if (copy($dir0. "/". $file_name, $dir. "/". $file_name)) {
                    chmod($dir. "/". $file_name, 0644);
                    //return "***** ファイル ".  $file_name. " をアップロードしました。";
                } else{
                    //return "***** エラーが発生しました。";
                }
            }
        }

        if (file_exists('/tmp/data.jpg') && $file_name) {
            rename('/tmp/data.jpg', $this->$this->_uploadFilePath. 'g'. $group_id. '/'. $file_name);
        }

        return $message_id;
    }

    /**
     * チャットメッセージをDBから削除する
     *
     */
    public function deleteMessage(Request $request) {

        $message_id = $request->input('message_id');

        $message = DB::table('chatmessages')->where('id', $message_id)->first();

        try {
            DB::table('chatmessages')->where('id', $message_id)->delete();
        } catch (\PDOException $e){
            return 'error';
        }

        if ($message->file_name != '') {
            @unlink($this->$this->_uploadFilePath. 'g'. $message->group_id. '/'. $message->file_name);
        }

        return 'success';
    }

    /**
     * 最後に読んだメッセージIDをDBに記録する
     *
     */
    public function setLastReadMessageId(Request $request) {

        $group_id   = $request->input('group_id');
        $message_id = $request->input('message_id');

        $lastRead = new Chatlastread;

        $already = $lastRead->where('group_id', $group_id)
                            ->where('user_id', \Auth::user()->id)
                            ->first();

        try {
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
        } catch (\PDOException $e){
            return 'error';
        }

        return 'success';
    }

    /**
     * チャットグループから退席する
     *
     */
    public function leaveGroup(Request $request) {

        $group_id   = $request->input('group_id');

        $del[Auth::id()] = 1;

        // メンバーを削除
        try {
            Chatgroup::deleteUser($group_id, $del);
            return 'success';
        } catch (\PDOException $e){
            return 'error';
        }
    }

    /**
     * 登録済みのユーザーをすべて取得
     *
     * @return json
     */
    public function getAllUsers() {

        $users = User::getAllUsers();
        if (empty($users)) {
            return 'nodata';
        }

        return response()->json($users);
    }

    /**
     * チャットグループを新規作成
     *
     * @return unknown
     */
    public function makeGroup(Request $request) {

        $group_name = $request->input('group_name');
        $member     = $request->input('member');

        $tmp = array();
        foreach ($member as $val) {
            $tmp[$val] = '0';
        }
        $member = $tmp;
        $member[\Auth::id()] = '1'; // 管理者

        DB::beginTransaction();
        try {
            // グループを登録
            $group_id = Group::set($group_name, 0);

            // メンバーを登録
            Chatgroup::set($group_id, $member);

            DB::commit();
            //return true;

            return $group_id;

        } catch (\PDOException $e){
            DB::rollBack();
            return 'error';
        }
    }


    /**
     * ファイルをアップロードする
     *
     * @return unknown
     */
    public function uploadGroupImage() {

        $group_id   = $_POST['group_id'];
        $file_name  = $_POST['file_name'];

        if (isset($_FILES["upfile"]) && $_FILES["upfile"]["tmp_name"]) {

            $dir = $this->_uploadGroupPath;

            //ディレクトリを作成してその中にアップロードしている。
            if (!file_exists($dir)) {
                $ret = mkdir($dir, 0777);
                echo "ret=". $ret. '<br>';
            }
            if (move_uploaded_file($_FILES['upfile']['tmp_name'], $dir. "/". $file_name)) {
                chmod($dir. "/". $file_name, 0644);

                DB::table('groups')->where('id', $group_id)
                ->update([
                        'file' => $file_name
                ]);

                //        var_dump($dir. "/". $name);
                //echo "ファイル ".  basename( $_FILES['upfile']['name']). " をアップロードしました。";
                return 'success';

            } else{
                //echo "エラーが発生しました。";
                return 'error';
            }
        }

    }

    /**
     * チャットグループを削除する
     *
     * @return unknown
     */
    public function deleteGroup(Request $request) {

        $group_id   = $request->input('group_id');

        $group = DB::table('groups')->where('id', $group_id)->first();

        DB::beginTransaction();
        try {
            // ダイレクトチャットの場合
            if ($group->kind == '1') {
                $chatGroups = DB::table('chatgroups')->where('group_id', $group_id)->orderBy('id')->get();
                if (!empty($chatGroups)) {
                    $user_id[0] = '';
                    $user_id[1] = '';
                    $i = 0;
                    foreach ($chatGroups as $chatGroup) {
                        $user_id[$i] = $chatGroup->user_id;
                        $i++;
                    }

                    if ($user_id[0] != '' && $user_id[1] != '') {
                        DB::table('chatcontacts')
                        ->where('from_user_id', $user_id[0])
                        ->where('to_user_id', $user_id[1])
                        ->delete();

                        DB::table('chatcontacts')
                        ->where('from_user_id', $user_id[1])
                        ->where('to_user_id', $user_id[0])
                        ->delete();
                    }
                }
            }

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

            /*
            if ( $dirHandle = opendir ( $dir )) {
                while ( false !== ( $fileName = readdir ( $dirHandle ) ) ) {
                    if ( $fileName != "." && $fileName != ".." ) {
                        unlink ( $dir.$fileName );
                    }
                }
                closedir ( $dirHandle );
                return rmdir ( $dir );
            }
            */
            system("rm -rf {$dir}");


            DB::commit();

            return "success";

        } catch (\PDOException $e){
            DB::rollBack();

            return "error";
        }
    }

    /**
     * ファイルをアップロードする
     *
     * @return unknown
     */
    public function uploadFile() {

        $user_id    = $_POST['user_id'];
        $group_id   = $_POST['group_id'];
        $file_name  = $_POST['file_name'];

        $str = urldecode($file_name);
        $file_name  = mb_convert_encoding($str, "UTF-8");


        if (isset($_FILES["upfile"]) && $_FILES["upfile"]["tmp_name"]) {

            $dir = $this->_uploadFilePath. '/g'. $group_id;

            //ディレクトリを作成してその中にアップロードしている。
            if (!file_exists($dir)) {
                //$ret = mkdir($dir, 0755);
                $ret = mkdir($dir, 0777);
                echo "ret=". $ret. '<br>';
            }
            if (move_uploaded_file($_FILES['upfile']['tmp_name'], $dir. "/". $file_name)) {
                chmod($dir. "/". $file_name, 0644);
                //        var_dump($dir. "/". $name);
                //echo "ファイル ".  basename( $_FILES['upfile']['name']). " をアップロードしました。";
                return 'success';

            } else{
                //echo "エラーが発生しました。";
                return 'error';
            }
        }

    }

    /**
     * タスクを登録する
     *
     * @return unknown
     */
    public function saveTask(Request $request) {

        $group_id       = $request->input('group_id');
        $limit_date     = $request->input('limit_date');
        $note           = $request->input('note');
        $member         = $request->input('member');
//print_r($request->all());

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
                foreach ($member as $key => $id) {
                    DB::table('chattaskcharges')->insert([
                            'task_id'       => $task_id,
                            'user_id'       => $id,
                            'created_at'    => date('Y/m/d H:i:s')
                    ]);
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

                return $message_id;

            } catch (\PDOException $e){
                //echo 'まさかのエラー？ '.$e->getMessage();
                DB::rollBack();
                return 'error';
            }
        }
    }

    /**
     * タスク一覧を取得する
     *
     * @return json
     */
    public function getChatTaskList(Request $request) {

        $group_id       = $request->input('group_id');

        $list = Chattask::get($group_id, Auth::id());
        if (empty($list)) {
            return 'nodata';
        }

        foreach ($list as $key => $items) {
            $charge = Chattaskcharge::get($items->id);
            $list[$key]->charge = $charge;
        }

        return response()->json($list);
    }

    /**
     * タスクを取得する
     *
     * @return Request $request
     */
    public function getChatTask(Request $request) {

        $task_id    = $request->input('task_id');

        $task = DB::table('chattasks')->where('id', $task_id)->first();
        if (empty($task)) {
            return 'nodata';
        }

        $charge = Chattaskcharge::get($task_id);
        $task->charge = $charge;

        return response()->json($task);
    }

    /**
     * タスクを削除する
     *
     * @return Request $request
     */
    public function deleteChatTask(Request $request) {

        $task_id   = $request->input('task_id');

        DB::beginTransaction();
        try {
            DB::table('chattaskcharges')->where('task_id', $task_id)->delete();

            DB::table('chattasks')->where('id', $task_id)->delete();

            DB::commit();

            return $message_id;

        } catch (\PDOException $e){
            //echo 'まさかのエラー？ '.$e->getMessage();
            DB::rollBack();
            return 'error';
        }
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
//        $member     = $request->input('member');

        DB::beginTransaction();
        try {
            DB::table('chattasks')->where('id', $task_id)
            ->update([
                    'note'          => $note,
                    'limit_date'    => $limit_date,
            ]);

            DB::commit();
            return 'success';

        } catch (\PDOException $e){
            //echo 'まさかのエラー？ '.$e->getMessage();
            DB::rollBack();
            return 'error';
        }
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
            return 'success';

        } catch (\PDOException $e){
            //echo 'まさかのエラー？ '.$e->getMessage();
            DB::rollBack();
            return 'error';
        }
    }

    /**
     * アルバムを作成する
     *
     * @return unknown
     */
    public function makeAlbum(Request $request) {

        $group_id  = $request->input('group_id');
        $name      = $request->input('name');

        $already = DB::table('albums')
            ->where('group_id', $group_id)
            ->where('user_id', \Auth::user()->id)
            ->where('name', $name)
            ->first();

        if (!$already) {

            DB::beginTransaction();
            try {
                $id = DB::table('albums')->insertGetId([
                    'group_id'      => $group_id,
                    'user_id'       => Auth::id(),
                    'name'          => $name,
                    'created_at'    => date('Y/m/d H:i:s')
                ]);

                /*
                $message = "■アルバム「". $name. "」を追加しました。\n";

                // チャットメッセージをDBに登録
                $message_id = DB::table('chatmessages')->insertGetId([
                        'group_id'      => $group_id,
                        'from_user_id'  => Auth::id(),
                        'message'       => $message,
                        'created_at'    => date('Y/m/d H:i:s')
                ]);
                */

                DB::commit();

            } catch (\PDOException $e){
                //echo 'まさかのエラー？ '.$e->getMessage();
                DB::rollBack();
                return 'error';
            }

        } else {
            $id = $already->id;
        }

        return $id;
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
        $file_name  = $_POST['file_name'];

        if (isset($_FILES["upfile"]) && $_FILES["upfile"]["tmp_name"]) {

            $dir1 = $this->_albumFilePath. '/g'. $group_id;
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
                //echo "ファイル ".  basename( $_FILES['upfile']['name']). " をアップロードしました。";
                return 'success';
            } else{
                //echo "エラーが発生しました。";
                return 'error';
            }
        }
    }

    /**
     * アルバムを取得
     *
     *
     */
    public function getAlbums(Request $request) {

        $group_id   = $request->input('group_id');

/*
        $albums = DB::table('albums')
            ->select('id', 'name')
            ->where('group_id', $group_id)
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
*/
        $albums = Album::get($group_id, Auth::id());

        return response()->json($albums);
    }

    /**
     * アルバム名変更
     *
     * @return unknown
     */
    public function renameAlbum(Request $request) {

        $album_id   = $request->input('album_id');
        $album_name = $request->input('album_name');

        try {
            DB::table('albums')->where('id', $album_id)
                ->update([
                    'name' => $album_name
            ]);
            return 'success';

        } catch (\PDOException $e){
            return 'error';
        }
    }

    /**
     * アルバム削除
     *
     * @return unknown
     */
    public function deleteAlbum(Request $request) {

        $group_id   = $request->input('group_id');
        $album_id   = $request->input('album_id');

        DB::table('albums')->where('id', $album_id)->delete();

        $dir1 = $this->_albumFilePath. '/g'. $group_id;
        $dir2 = $dir1. '/u'. Auth::id();
        $dir  = $dir2. '/a'. $album_id. '/';

        if ( $dirHandle = opendir ( $dir )) {
            while ( false !== ( $fileName = readdir ( $dirHandle ) ) ) {
                if ( $fileName != "." && $fileName != ".." ) {
                    unlink ( $dir. $fileName );
                }
            }
            closedir ( $dirHandle );
            rmdir($dir);

            return 'success';
        }

        return 'error';
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

        $dir1 = $this->_albumFilePath. '/g'. $group_id;
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
            return 'success';
        }

        return 'error';
    }

    /**
     * 位置情報を登録する
     *
     * @return unknown
     */
    public function sendLocation(Request $request) {

        $latitude   = $request->input('latitude');  // 緯度
        $longitude  = $request->input('longitude'); // 軽度
        $altitude   = $request->input('altitude');  // 高度

        $already = DB::table('locations')->where('user_id', Auth::id())->first();

        try {
            if ($already) {

                DB::table('locations')->where('user_id', Auth::id())
                ->update([
                        'latitude'      => $latitude,
                        'longitude'     => $longitude,
                        'altitude'      => $altitude,
                        'updated_at'        => date("Y/m/d H:i:s"),
                ]);

            } else {

                DB::table('locations')->insert([
                        'user_id'       => Auth::id(),
                        'latitude'      => $latitude,
                        'longitude'     => $longitude,
                        'altitude'      => $altitude,
                        'created_at'    => date('Y/m/d H:i:s')
                ]);
            }

            return 'success';

        } catch (\PDOException $e){
            return 'error';
        }
    }

    /**
     * 近くにいるユーザーを検索する
     *
     * @return unknown
     */
    public function searchLocation(Request $request) {

        $latitude   = $request->input('latitude');  // 緯度
        $longitude  = $request->input('longitude'); // 軽度
        $altitude   = $request->input('altitude');  // 高度

        $user_id = Auth::id();
        $sql = <<<EOF
select user_id
from   locations
where power(abs(latitude  - $latitude), 2) + power(abs(longitude - $longitude), 2) < 0.00001
and   user_id <> $user_id
order by power(abs(latitude  - $latitude), 2) + power(abs(longitude - $longitude), 2) LIMIT 1
EOF;

        $data = DB::selectOne($sql);

        if (isset($data->user_id)) {
            $user = User::get($data->user_id);

            if (isset($user[0])) {
                //print_r($data[0]);
                return response()->json($user[0]);
            }
        }
    }

    /**
     * チャットメンバーに追加する
     *
     * @return unknown
     */
    public function addChatMember(Request $request) {

        $user_id    = $request->input('user_id');  // 追加するメンバーのID
        $data;

        if (!Chatgroup::checkDirectChatMember(Auth::id(), $user_id)) {

            DB::beginTransaction();
            try {

                $toUser   = DB::table('users')->where('id', Auth::id())->first();
                $fromUser = DB::table('users')->where('id', $user_id)->first();

                //Chatcontact::setAgree($user_id, Auth::id(), $message, $toUser->email);

                // グループを登録
                $group_name = $toUser->name. 'さんと'. $fromUser->name. 'さん';
                $group_id = Group::set($group_name, 1);

                // メンバーを登録
                Chatgroup::set($group_id, [
                        Auth::id() => '1',
                        $user_id   => '1',
                ]);

                /*
                // メール送信
                $subject = $toUser->name. 'さんから、チャットのコンタクトの承認が来ました。';
                $to = $fromUser->email;
                Mail::to($to)->send(new ContactSent('agree', $subject, $message));
                */

                DB::commit();
                $data = [
                        'group_id'  => $group_id,
                        'name'      => $group_name,
                        'status'    => '0000'
                ];

            } catch (\PDOException $e){
                DB::rollBack();
                $data = [
                    'status'        => '0101',
                    'message'       => $e->getMessage()
                ];
            }
        } else {
            $data = [
                'status'        => '0000',
                'message'       => 'already'
            ];
        }
        return response()->json($data);
    }

    /**
     * 位置情報を削除
     *
     * @return unknown
     */
    public function deleteLocation() {

        try {
            DB::table('locations')->where('user_id', Auth::id())->delete();
            return 'success';

        } catch (\PDOException $e){
            return 'error';
        }
    }

    /**
     * 承認済みのメンバーを取得
     * @return unknown
     */
    public function getDirectMember() {

        $member= Chatgroup::getDirectMember(Auth::id());
        return response()->json($member);
    }

    /**
     * ユーザーの詳細情報を取得する
     *
     * @return JSON
     */
    public function getMemberDetails(Request $request){

        $user_id = $request->input('user_id');
//        if (!$user_id) {
//            $user_id = Auth::id();
//        }

        $data = User::get($user_id);

        if (isset($data[0])) {
            //print_r($data[0]);
            return response()->json($data[0]);
        }

        return 'error';
    }

    public function details(){
        return response()->json(['user' => Auth::user()]);
    }

    /**
     * QRコード生成時に識別コードを発行する
     *
     * @return unknown|NULL
     */
    public function setMyQR() {

        return Chatcontact::setMyQR(Auth::id());
    }


    /**
     * ユーザーがチャットでアップロードした画像を取得する
     */
    public function getUploadFile(Request $request) {

        $group_id = $request->input('group_id');
        $filename = $request->input('file_name');

        //$str = urldecode($filename);
        //S$filename= mb_convert_encoding($str, "UTF-8");

        $dir = $this->_uploadFilePath. 'g'. $group_id. '/';

        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
            while( ($file = readdir($handle)) !== false ) {
                if( filetype( $path = $dir . $file ) == "file" ) {
                    // $file: ファイル名
                    // $path: ファイルのパス
                    if ($file == $filename) {
                        return @readfile($dir. $filename);
                    }
                }
            }
        }

        // ファイルがなかった時
        $filename = $this->_uploadFilePath. 'not_found.png';
        return @readfile($filename);
    }

    /**
     * アルバムでアップロードした画像を取得する
     */
    public function getAlbumFile(Request $request) {
        $group_id = $request->input('group_id');
        $album_id = $request->input('album_id');
        $filename = $request->input('file_name');

        $dir = $this->_albumFilePath. 'g'. $group_id
            . '/u'. Auth::id()
            . '/a'. album_id. '/';

        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
            while( ($file = readdir($handle)) !== false ) {
                if( filetype( $path = $dir. $file ) == "file" ) {
                    // $file: ファイル名
                    // $path: ファイルのパス
                    if ($file == $filename) {
                        return @readfile($dir. $filename);
                    }
                }
            }
        }

        // ファイルがなかった時
        $filename = $this->_uploadFilePath. 'not_found.png';
        return @readfile($filename);
    }


    /**
     * グループの画像を取得する
     */
    public function getGroupPhotoImages(Request $request) {

        $arrayImages = array();
        $tmp = array();

        // ディレクトリのパス
        $dir = $this->_uploadGroupPath;

        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
            while( ($file = readdir($handle)) !== false ) {
                if( filetype( $path = $dir . $file ) == "file" ) {
                    // $file: ファイル名
                    // $path: ファイルのパス
                    $encoded_data = base64_encode(file_get_contents($path));

                    $tmp['filename'] = $file;
                    $tmp['image'] = $encoded_data;
                    $arrayImages[] = $tmp;
                }
            }
        } else {
            return 'error';
        }

        return response()->json($arrayImages);
    }

    /**
     * グループの画像を取得する
     */
    public function getGroupPhoto(Request $request) {
        $id = $request->input('group_id');

        $group = Group::find($id);
        if ($group->file) {
            $filename = $this->_uploadGroupPath. $group->file;
            return @readfile($filename);
        }
    }

    /**
     * チャットメンバー（自分を含む／ダイレクトチャットがあるかどうかも含む）を取得する
     *
     * @return json
     */
    public function getGroupChatAllMember(Request $request) {

        $group_id = $request->input('group_id');

        // チャットメンバーを取得
        $members = Chatgroup::getMembers3(Auth::id(), $group_id);

        return response()->json($members);
    }

    /**
     * メッセージをフォーマットして返す
     *
     * @param   string $msg
     * @param   string $dir
     * @return  string $msg
     */
    private function _showHtmlChatMessage($msg, $dir='')
    {
        $users = new User();
        $qt_mark = array();
        $qt_str  = array();
        $offset = 0;

        $pos = false;
        $pos2 = false;

        // 引用の編集
        while(true) {
            $qt_name = '';
            $qt_time = '';

            $pos = mb_strpos($msg, '[time:', $offset);
            if ($pos === false) {
                break;
            }

            $pos2 = mb_strpos($msg, ']', $pos+1);
            $tmp = mb_substr($msg, $pos+1, $pos2-$pos-1, 'UTF-8');
            $tmp2 = preg_split("/ /", $tmp);
            foreach ($tmp2 as $item) {
                $tmp3 = preg_split("/:/", $item);
                $tmp4[$tmp3[0]] = $tmp3[1];
            }

            if (isset($tmp4['id'])) {
                $qt_name = DB::table('users')->where('id', $tmp4['id'])->value('name');
//                $users->getUserName($pdo, $tmp4['id']);
            }
            if (isset($tmp4['time'])) {
                $qt_time = date('Y-m-d H:i:s', $tmp4['time']);
            }
            $qt_mark[] = $tmp;
            $qt_str[] = '<'. $qt_name. ' '. $qt_time. '>'. "\n";

            $offset = $pos2 + 1;
        }
        //print_r($qt_mark);
        //print_r($qt_str);

        // To の編集
        while (true){
            $pos = mb_strpos($msg, '[To:');
            if ($pos === false) {
                break;
            }
            $tmp = '';
            if ($pos > 0) {
                $tmp .= mb_substr($msg, 0, $pos, 'UTF-8');
            }
            $tmp .= '[TO]';

            $pos2 = mb_strpos($msg, ']', $pos+1);
            $tmp .= mb_substr($msg, $pos2+1, mb_strlen($msg)-$pos2+1, 'UTF-8');

            $msg = $tmp;
        }

        // 返信の編集
        while (true){
            $pos = mb_strpos($msg, '[mid:');
            if ($pos === false) {
                break;
            }
            $tmp = '';
            if ($pos > 0) {
                $tmp .= mb_substr($msg, 0, $pos, 'UTF-8');
            }
            //$tmp .= 'Re:';
            $tmp .= '[RE]';

            $pos2 = mb_strpos($msg, ']', $pos+1);
            $tmp .= mb_substr($msg, $pos2+1, mb_strlen($msg)-$pos2+1, 'UTF-8');

            $msg = $tmp;
        }

        // アップロードファイル
        while (true){
            $pos  = mb_strpos($msg, '[upload]');
            if ($pos === false) {
                break;
            }
            $pos2 = mb_strpos($msg, '[/upload]', $pos+1);
            $file_name = mb_substr($msg, $pos+8, $pos2-$pos-8);

            $type = preg_split('/\./', $file_name);
            //print_r($type);
            //exit();
            $idx = count($type) - 1;
            $extension = mb_strtolower($type[$idx], 'UTF-8');
            //echo 'extension='. $extension. '<br>';
            $preview_flg = false;
            if ($extension == 'jpg' || $extension == 'jpeg' ||
                    $extension == 'png' || $extension == 'gif'
                    ) {
                        $preview_flg = true;
                    }

                    $tmp  = mb_substr($msg, 0, $pos, 'UTF-8');

                    /*
                     $tmp .= '<div class="upload_file">'
                     . '<div class="upload_file_title">☆ファイルをアップロードしました。</div>'
                     . '<div class="upload_file_file">';

                     if ($preview_flg == true) {
                     $tmp .= '<img src="'. asset('/upload/'. $dir. '/'. $file_name). '" width="50" /><br />';
                     }

                     $tmp .=  '</div>'
                     . $file_name. '<br />'
                     . '</div>';
                     */
                    $tmp .= mb_substr($msg, $pos2+9);
                    $msg = $tmp;
        }

        // 引用タグを置換
        $msg = preg_replace('/\[qt\]/', "-------------\n", $msg);
        $msg = preg_replace('/\[\/qt\]/', "\n-------------\n", $msg);

        if (is_array($qt_mark) && !empty($qt_mark) && isset($qt_mark[0]) && $qt_mark[0] != '') {
            foreach ($qt_mark as $key => $mark) {
                $msg = preg_replace('/\['. $mark. '\]/', $qt_str[$key], $msg);
            }
        }

        /*
        // 絵文字を画像に置換
        for ($i = 1; $i <= 10; $i++) {
            $b = '/\[icon:'. sprintf('%03d', $i). '\]/';
            //            $a = '<img src="'. asset('/images/m_icon/k_'. sprintf('%03d', $i). '.gif'). '">';
            $a = '';
            $msg = preg_replace($b, $a, $msg);
        }
*/

        // 改行コードを改行タグに置換
        return $msg; //preg_replace("/\n/", "<br />", $msg);
    }
}
