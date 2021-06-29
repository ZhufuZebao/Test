<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Services\FirebaseService;
use App\Models\Group;
use App\Models\Firebaseids;
use App\Models\Account;
class FirebaseNotificationController extends Controller
{

    /**
     * @var array
     */
    private $content = '';

    public function __construct()
    {
        $this->content = array();
    }

    public function saveFirebaseID(Request $request)
    {
        $firebaseId = $request->input('id');
        $chatApp = "3";
        //共有IDを持つユーザーを削除
        Firebaseids::where('firebase_id', $firebaseId)->whereNotIn('user_id',[Auth::id()])
            ->where("app_kind", $chatApp)->delete();
        $already =Firebaseids::where('user_id', Auth::id())
            ->where("app_kind", $chatApp)
            ->first();

        DB::beginTransaction();
        try {
            if ($already) {
                DB::table('firebaseids')->where('user_id', Auth::id())
                    ->where("app_kind", $chatApp)
                    ->update([
                        'firebase_id' => $firebaseId,
                        'updated_at' => date("Y/m/d H:i:s"),
                    ]);

            } else {
                DB::table('firebaseids')->insert([
                    'user_id' => Auth::id(),
                    'firebase_id' => $firebaseId,
                    'app_kind' => $chatApp,
                    'created_at' => date("Y/m/d H:i:s"),
                ]);
            }

            DB::commit();

            return 'success';

        } catch (\PDOException $e) {
            DB::rollBack();
            return 'error';
        }
    }

    public function deleteFirebaseID(Request $request)
    {

        Firebaseids::where('user_id', Auth::id())
            ->where("app_kind", 3)
            ->delete();
        return 'success';
    }

    public function pushMessage(Request $request)
    {

        $user_id = $request->input('user_id');
        $group_id = $request->input('group_id');
        $group_name = $request->input('group_name');
        $call_status = $request->input('call_status');

        if ($call_status == 'message' || $call_status == 'contact' || $call_status == 'task_limit') {
            $ttl = (3600 * 24) . "s";
        } else {
            $ttl = 0;
        }

        if ($user_id == Auth::id()) {
            return "empty";
        }

        $group =Group::where('id', $group_id)->first();
        if (empty($group)) {
            return "error";
        }

        $user = Account::where('id', Auth::id())->first();

        $file_name = null;
        if ($group->kind == '1') {
            $file_name = $user->file;
        } else {
            $file_name = $group->file;
        }

        $chatApp = "1";
        $firebaseids = Firebaseids::where('user_id', $user_id)
            ->where("app_kind", $chatApp)->first();

        if (!empty($firebaseids)) {
            $data = [
                "to" => $firebaseids->firebase_id, // 送付先のデバイストークン
                "priority" => "high",
                "time_to_live" => $ttl,   // メッセージの有効期限
                "data" => [
                    'user_name' => $user->name,
                    "user_id" => Auth::id(),
                    "group_id" => $group_id,
                    "group_name" => $group_name,
                    'kind' => $group->kind,
                    'file' => $file_name,
                    'call_status' => $call_status,
                    'call_time' => date(date('Y/m/d H:i:s'))
                ],
                "notification" => [
                    "title" => 'メッセージ受信',
                    "body" => $user->name . 'さんからメッセージが届きました。',
                    "badge" => 1,
                    "sound" => 'default',
                    'click_action' => 'OPEN_MAIN',
                ]
            ];

            FirebaseService::push($data);
        }

        return "success";
    }
}
