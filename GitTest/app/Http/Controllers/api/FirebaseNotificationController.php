<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Response;
use App\User;
use DB;

class FirebaseNotificationController extends Controller
{

    public function __construct(){
        $this->content = array();
    }

    public function saveFirebaseID(Request $request) {
        $firebaseId = $request->input('id');

        $already = DB::table('firebaseids')
        ->where('user_id', Auth::id())
        ->first();

        DB::beginTransaction();
        try {
            if ($already) {
                DB::table('firebaseids')
                ->where('user_id', Auth::id())
                ->update([
                        'firebase_id'   => $firebaseId,
                        'updated_at'    => date("Y/m/d H:i:s"),
                ]);

            } else {
                DB::table('firebaseids')->insert([
                        'user_id'       => Auth::id(),
                        'firebase_id'   => $firebaseId,
                        'created_at'    => date("Y/m/d H:i:s"),
                ]);
            }

            DB::commit();

            return 'success';

        } catch (\PDOException $e){
            DB::rollBack();
            return 'error';
        }
    }

    public function pushMessage(Request $request) {

        $user_id     = $request->input('user_id');
        $group_id    = $request->input('group_id');
        $group_name  = $request->input('group_name');
        $call_status = $request->input('call_status');

        if ($user_id == Auth::id()) {
            return "empty";
        }

        $group = DB::table('groups')->where('id', $group_id)->first();
        if (empty($group)) {
            return "error";
        }

        $user = DB::table('users')->where('id', Auth::id())->first();

        $file_name = null;
        if ($group->kind == '1') {
            $file_name = $user->file;
        } else {
            $file_name = $group->file;
        }

        $firebaseids = DB::table('firebaseids')->where('user_id', $user_id)->first();

        $token = 'AAAASIrY5Jk:APA91bGeodDuFKq16THvDMlGyUGQJ71K_FBs_GxNuD4CE44HUyu42oqI_r2An4JfDDOitjFjlfBisX4X-HQxGZPHWuWVWle2CV0IjKClmkGaXfEMnE3nSBXdbGR39Oe7q731sNjqyuNi'; // sakura firebaseのトークン
        //$token = 'AAAARwRTY1c:APA91bGG0Ff4UGmN-7UPKQKYd0szbVzMyzZ2NNKzYmBZpbR6YCKqhVcScz3oA9ywqRHiDC8zcfITG_E8dpr_-8htSCRuH3FDF3doEuetcPqwFGDA3zu5NknGFdK1bygNPQjbip-PKDPP'; // AWS firebaseのトークン
        $base_url = 'https://fcm.googleapis.com/fcm/send';

        $data = [
                "to" => $firebaseids->firebase_id, // 送付先のデバイストークン
                "priority" => "high",
                "data" => [
                        'user_name'     => $user->name,
                        "group_id"      => $group_id,
                        "group_name"    => $group_name,
                        'kind'          => $group->kind,
                        'file'          => $file_name,
                        'call_status'   => $call_status
                ]
        ];

        $header = [
                'Authorization: key='.$token,
                'Content-Type: application/json'
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST'); // post
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); // jsonデータを送信
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header); // リクエストにヘッダーを含める
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $response = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $result = json_decode($body, true);

        curl_close($curl);

        return "success";
    }
}
