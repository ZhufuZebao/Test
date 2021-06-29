<?php

namespace App\Http\Services;

use App\Helpers\ApiHelper;
use App\Models\ChatGroup;
use App\Models\Firebaseids;
use App\Project;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FirebaseService
{
    const BASE_URL = 'https://fcm.googleapis.com/fcm/send';

    public function pushSchedule($toUserId, $scheduleId, $subId, $title,$kind = null)
    {
        $uri = 'https://' . $_SERVER["SERVER_NAME"] . '/' . explode('/', $_SERVER["REQUEST_URI"])[1] . '/#/schedule';
        //always send notification to web
        $sendUserDataWeb = Firebaseids::getFirebaseId(ApiHelper::$web, $toUserId);
        $sendUserDataWebFirebaseId = '';
        if(!empty($sendUserDataWeb)){
            $sendUserDataWebFirebaseId = $sendUserDataWeb->firebase_id;
        }
        // 通知送信相手データ取得
        $sendUserData = Firebaseids::getFirebaseId(ApiHelper::$scheduleApp, $toUserId);
        $sendUserDataFirebaseId = '';
        if (!empty($sendUserData)) {
            $sendUserDataFirebaseId = $sendUserData->firebase_id;
        }
        $click_action='';
        if (!$kind) {
            $kind = "updateSchedule";
            $click_action='OPEN_DETAIL';
        }
        $data = array(
            "to" => '',
            "priority" => "high",
            "data" => array(
                "scheduleId" => $scheduleId,
                "subScheduleId" => $subId,
                "title" => $title,
                "notificationKind" => $kind,
                "to_user_id" => $toUserId
            ),
            "notification" => array(
                "title" => 'SITEスケジュール',
                "body" => $title,
                "badge" => 1,
                "sound" => 'default',
                "click_action" => $uri
            )
        );
        $dataHttp = [
            "message" => [
                "token" => '',
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "alert" => [
                                "title" => 'SITEスケジュール',
                                "body" => $title,
                            ],
                            "badge" => 1,
                            "sound" => 'default',
                            "click_action" =>$click_action
                        ],
                        "scheduleId" => (string)$scheduleId,
                        "subScheduleId" => (string)$subId,
                        "title" => $title,
                        "notificationKind" => $kind,
                        "to_user_id" => (string)$toUserId
                    ],
                ],
                'android' => [
                    "data" => [
                        "click_action" =>$click_action,
                        "title" => $title,// android body
                        "body" => $title,
                        "scheduleId" => (string)$scheduleId,
                        "subScheduleId" => (string)$subId,
                        "notificationKind" => $kind,
                        "to_user_id" => (string)$toUserId,
                    ],
                ],
            ]
        ];
        if($sendUserDataFirebaseId){
            $dataHttp['message']['token'] = $sendUserDataFirebaseId;
            $this->pushHttp($dataHttp);
        }
        if($sendUserDataWebFirebaseId){
            $data['to'] = $sendUserDataWebFirebaseId;
            $this->pushData($data);
        }
    }

    public function pushAddChatFriend($fromUser, $toUserId)
    {
        //always send notification to web
        $sendUserDataWeb = Firebaseids::getFirebaseId(ApiHelper::$web, $toUserId);
        $sendUserDataWebFirebaseId = '';
        if(!empty($sendUserDataWeb)){
            $sendUserDataWebFirebaseId = $sendUserDataWeb->firebase_id;
        }
        // 通知送信相手データ取得
        $sendUserData = Firebaseids::getFirebaseId(ApiHelper::$chatApp, $toUserId);
        $sendUserDataFirebaseId = '';
        if (!empty($sendUserData)) {
            $sendUserDataFirebaseId = $sendUserData->firebase_id;
        }
        $kind = "1";
        $data = [
            "to" => '', // 送付先のデバイストークン
            "priority" => "high",
            "data" => [
                'user_name' => $fromUser->name,
                "user_id" => $fromUser->id,
                "group_id" => $toUserId,    // 相手のユーザーIDを送る
                "group_name" => $fromUser->name,
                'kind' => $kind,
                'file' => $fromUser->file,
                'to_user_id' => $toUserId,
                'call_status' => 'contact'
            ],
            "notification" => [
                "title" => '仲間追加',
                "body" => $fromUser->name . 'さんから仲間追加申請が届きました。',
                "badge" => 1,
                "sound" => 'default'
            ]
        ];
        if($sendUserDataFirebaseId){
            $data['to'] = $sendUserDataFirebaseId;
            $this->pushData($data);
        }
        if($sendUserDataWebFirebaseId){
            $data['to'] = $sendUserDataWebFirebaseId;
            $this->pushData($data);
        }
    }
    public function userTemKey($user_id)
    {
        $key = Str::random(10);
        Cache::put("temKey" . $key, $user_id, 300);
        return $key;
    }
    public function pushChatMessage($fromUser, $toUserId, $group, $callStatus='message')
    {
        if (empty($fromUser) || empty($group)) {
            Log::warning('data error in pushChatMessage');
            return ;
        }
        //always send notification to web
        $sendUserDataWeb = Firebaseids::getFirebaseId(ApiHelper::$web, $toUserId);
        $sendUserDataWebFirebaseId = '';
        if(!empty($sendUserDataWeb)){
            $sendUserDataWebFirebaseId = $sendUserDataWeb->firebase_id;
        }
        // 通知送信相手データ取得
        $sendUserData = Firebaseids::getFirebaseId(ApiHelper::$chatApp, $toUserId);
        $sendUserDataFirebaseId = '';
        if(!empty($sendUserData)){
            $sendUserDataFirebaseId = $sendUserData->firebase_id;
        }
        $uri = 'https://' . $_SERVER["SERVER_NAME"] . '/' . explode('/', $_SERVER["REQUEST_URI"])[1] . '/#/chat?groupId=' . $group->id . '&userId=0';
        if (intval($group->kind) == 1) {
            $file_name = $fromUser->file;
            $group_name = $fromUser->name;
            $group_type = '';
        } else {
            $file_name = $group->file;
            $group_count = ChatGroup::where('group_id',$group->id)->count();
            $group_name = $group->name.' ('.$group_count.'人)';
            $proCount = Project::where('group_id',$group->id)->orWhere('group_id',$group->parent_id)->count();
            if($proCount){
                $group_type = 'pro';
                $uri = 'https://' . $_SERVER["SERVER_NAME"] . '/' . explode('/', $_SERVER["REQUEST_URI"])[1] . '/#/chat?proId=' . $group->id . '&userId=0';
            }else{
                $group_type = '';
            }
        }
        if ($callStatus == 'message' || $callStatus == 'contact' || $callStatus == 'task_limit' || $callStatus == 'message_like') {
            $ttl = (3600 * 24). "s";
        } else {
            $ttl = 0;
        }
        if(in_array($callStatus,['video_join','join'])){
            $click_action='OPEN_CALL';
            $uri = $uri. '&call_status=' .$callStatus;
        }else{
            $click_action='OPEN_MAIN';
        }
        switch ($callStatus) {
            case 'video_join':
                $body = 'さんからビデオ通話が届きました。';
                break;
            case 'join':
                $body = 'さんから音声通話が届きました。';
                break;
            case 'reject':
                $body = 'さんは音声通話に参加されませんでした。';
                break;
            case 'video_reject':
                $body = 'さんはビデオ通話に参加されませんでした。';
                break;
            default:
                $body = 'さんからメッセージが届きました。';
                break;
        }
        if($group->kind != 1&&!in_array($callStatus,['video_join','join','reject','video_reject'])){
            $body='さんから'.$group->name.'にメッセージが届きました。';
        }
        // いいね通知の追加#3245
        if($callStatus == 'message_like'){
            $body = 'さんがいいねしました。';
        }
        if($callStatus=='leave'||$callStatus=='video_leave'||$callStatus =='video_reject'||$callStatus =='reject'){
            if($sendUserDataFirebaseId){
                $data = [
                    "to" => $sendUserDataFirebaseId, // 送付先のデバイストークン
                    "priority" => "high",
                    "time_to_live" => $ttl,
                    "data" => [
                        'user_name' => $fromUser->name,
                        "user_id" => $fromUser->id,
                        "group_id" => $group->id,
                        "group_name" => $group_name,
                        'kind' => $group->kind,
                        'file' => $file_name,
                        'call_status' => $callStatus,
                        "group_type" => $group_type,
                        "to_user_id" => $toUserId,
                        "body" => $fromUser->name . $body
                    ],
                ];
                $this->pushData($data);
            }
            //ウェブとアプリが送信するメッセージの内容が異なります
            if($sendUserDataWebFirebaseId) {
                $webData = [
                    "to" => '', // 送付先のデバイストークン
                    "priority" => "high",
                    "time_to_live" => $ttl,
                    "notification" => [
                        "title" => 'メッセージ受信',
                        "body" => $fromUser->name . $body,
                        "badge" => 1,
                        "sound" => 'default',
                        'click_action' => $uri,
                    ],
                    "data" => [
                        'user_name' => $fromUser->name,
                        "user_id" => $fromUser->id,
                        "group_id" => $group->id,
                        "group_name" => $group_name,
                        'kind' => $group->kind,
                        'file' => $file_name,
                        'call_status' => $callStatus,
                        "group_type" => $group_type,
                        "to_user_id" => $toUserId
                    ],
                ];
                $webData['to'] = $sendUserDataWebFirebaseId;
                $this->pushData($webData);
            }
        }else {
            $userTemKey=$this->userTemKey($fromUser->id);
            $data = [
                "message" => [
                    "token" => '',
                    "webpush" => [
                        "notification" => [
                            "title" => 'メッセージ受信',
                            "body" => $fromUser->name . $body,
                            "badge" => 1,
                            "sound" => 'default',
                            "icon" => 'chat_notify',
                            'click_action' => $uri,
                        ],
                        "data" => [
                            'user_name' => $fromUser->name,
                            "user_id" => (string)Auth::id(),
                            "group_id" => (string)$group->id,    // 相手のユーザーIDを送る
                            "group_name" => $group_name,
                            'kind' => (string)$group->kind,
                            'file' => $file_name,
                            'call_status' => $callStatus,
                            'call_time' => date(date('Y/m/d H:i:s')),
                            'group_type' => $group_type,
                            "to_user_id" => (string)$toUserId
                        ],
                    ],
                    "apns" => [
                        "payload" => [
                            "aps" => [
                                "alert" => [
                                "title" => 'メッセージ受信',
                                "body" => $fromUser->name . $body,
                                ],
                                "badge" => 1,
                                "sound" => 'default',
                                "icon" => 'chat_notify',
                                'click_action' => $click_action,
                                "mutable-content" => 1,
                            ],
                            'userTemKey' =>$userTemKey,
                            'user_name' => $fromUser->name,
                            "user_id" => (string)Auth::id(),
                            "group_id" => (string)$group->id,    // 相手のユーザーIDを送る
                            "group_name" => $group_name,
                            'kind' => (string)$group->kind,
                            'file' => $file_name,
                            'call_status' => $callStatus,
                            'call_time' => date(date('Y/m/d H:i:s')),
                            'group_type' => $group_type,
                            "to_user_id" => (string)$toUserId
                        ],
                    ],
                    'android' => [
                        "data" => [
                            'user_name' => $fromUser->name,
                            "user_id" => (string)Auth::id(),
                            "group_id" => (string)$group->id,    // 相手のユーザーIDを送る
                            "group_name" => $group_name,
                            'kind' => (string)$group->kind,
                            'file' => $file_name,
                            'call_status' => $callStatus,
                            'call_time' => date(date('Y/m/d H:i:s')),
                            'group_type' => $group_type,
                            "to_user_id" => (string)$toUserId,
                            "body" => $fromUser->name . $body,
                        ],
                    ],
                ]
            ];

            if($sendUserDataFirebaseId){
                $data['message']['token'] = $sendUserDataFirebaseId;
                $this->pushHttp($data);
            }
            if($sendUserDataWebFirebaseId){
                $data['message']['token'] = $sendUserDataWebFirebaseId;
                $this->pushHttp($data);
            }
        }
    }

    //TASK
    public function pushTaskMessage($fromUser, $toUserId, $group = null, $taskId, $taskNote, $callStatus = 'message')
    {
        //group can be null
        if (empty($fromUser)) {
            Log::warning('data error in pushChatMessage');
            return ;
        }
        //always send notification to web
        $sendUserDataWeb = Firebaseids::getFirebaseId(ApiHelper::$web, $toUserId);
        $sendUserDataWebFirebaseId = '';
        if(!empty($sendUserDataWeb)){
            $sendUserDataWebFirebaseId = $sendUserDataWeb->firebase_id;
        }
        // 通知送信相手データ取得
        $sendUserData = Firebaseids::getFirebaseId(ApiHelper::$chatApp, $toUserId);
        $sendUserDataFirebaseId = '';
        if(!empty($sendUserData)){
            $sendUserDataFirebaseId = $sendUserData->firebase_id;
        }
        if($group){
            $uri='https://'.$_SERVER["SERVER_NAME"].'/'.explode('/',$_SERVER["REQUEST_URI"])[1].'/#/chat?groupId='.$group->id.'&userId=0';
            if (intval($group->kind) == 1) {
                $file_name = $fromUser->file;
                $group_name = $fromUser->name;
                $group_id = $group->id;
                $kind = $group->kind;
                $group_type = '';
            } else {
                $file_name = $group->file;
                $group_name = $group->name;
                $group_id = $group->id;
                $kind = $group->kind;
                $proCount = Project::where('group_id', $group->id)->orWhere('group_id', $group->parent_id)->count();
                if ($proCount) {
                    $group_type = 'pro';
                    $uri = 'https://' . $_SERVER["SERVER_NAME"] . '/' . explode('/', $_SERVER["REQUEST_URI"])[1] . '/#/chat?proId=' . $group->id . '&userId=0';
                } else {
                    $group_type = '';
                }
            }
        }else{
            $group_id = '';
            $file_name = '';
            $group_name = '';
            $kind = '';
            $group_type = '';
            $uri = 'https://' . $_SERVER["SERVER_NAME"] . '/' . explode('/', $_SERVER["REQUEST_URI"])[1] . '/#/chat?taskId=' . $taskId;
        }

        $body='さんからメッセージが届きました。';
        $click_action='OPEN_MAIN';
        $data = [
            "message" => [
                "token" => '',
                "webpush" => [
                    "notification" => [
                        "title" => 'メッセージ受信',
                        "body" => $fromUser->name . $body,
                        "badge" => 1,
                        "sound" => 'default',
                        "icon" => 'chat_notify',
                        'click_action' => $uri,
                    ],
                    "data" => [
                        'user_name' => $fromUser->name,
                        "user_id" => (string)Auth::id(),
                        "group_id" => (string)$group->id,    // 相手のユーザーIDを送る
                        "group_name" => $group_name,
                        'kind' => (string)$group->kind,
                        'file' => $file_name,
                        'call_status' => $callStatus,
                        'task' => $taskId,
                        'group_type' => $group_type,
                        "to_user_id" => (string)$toUserId
                    ],
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "alert" => [
                                "title" => 'メッセージ受信',
                                "body" => $fromUser->name . $body,
                            ],
                            "badge" => 1,
                            "sound" => 'default',
                            "icon" => 'chat_notify',
                            'click_action' => $click_action,
                            "mutable-content" => 1,
                        ],
                        'user_name' => $fromUser->name,
                        "user_id" => (string)Auth::id(),
                        "group_id" => (string)$group->id,    // 相手のユーザーIDを送る
                        "group_name" => $group_name,
                        'kind' => (string)$group->kind,
                        'file' => $file_name,
                        'call_status' => $callStatus,
                        'task' => $taskId,
                        'group_type' => $group_type,
                        "to_user_id" => (string)$toUserId
                    ],
                ],
                'android' => [
                    "data" => [
                        'user_name' => $fromUser->name,
                        "user_id" => (string)Auth::id(),
                        "group_id" => (string)$group->id,    // 相手のユーザーIDを送る
                        "group_name" => $group_name,
                        'kind' => (string)$group->kind,
                        'file' => $file_name,
                        'call_status' => $callStatus,
                        'task' => $taskId,
                        'group_type' => $group_type,
                        "to_user_id" => (string)$toUserId,
                        "body" => $fromUser->name . $body,
                    ],
                ],
            ]
        ];

        if($sendUserDataFirebaseId){
            $data['message']['token'] = $sendUserDataFirebaseId;
            $this->pushHttp($data);
        }
        if($sendUserDataWebFirebaseId){
            $data['message']['token'] = $sendUserDataWebFirebaseId;
            $this->pushData($data);
        }
    }

    /**
     * 通知送信
     * @param $data
     */
    public function pushData($data)
    {
        $token = config('firebase.token');
        if (!empty($token) && !empty($data)) {
            $client = new Client([
                'base_uri' => self::BASE_URL
            ]);
            $header = [
                'Content-Type' => 'application/json',
                'Authorization' => 'key=' . $token
            ];
            $jsonData = json_encode($data);
            Log::debug($jsonData);

            $request = new \GuzzleHttp\Psr7\Request('POST', self::BASE_URL, $header, $jsonData);
            $promise = $client->sendAsync($request, config("firebase.httpProxy"));
            // 成功したリクエストのコールバック処理
            $promise->then(
                function ($response) {
                    if ($response->getStatusCode() !== 200) {
                        Log::warning("push firebase error");
                        Log::error($response->getStatusCode() . '::' . $response->getReasonPhrase());
                    } else {
                        Log::debug("push firebase:200");
                        Log::debug((string)$response->getBody());
                    }
                },
                function (RequestException $e) {
                    Log::error($e->getRequest()->getMethod());
                }
            );
            $promise->wait();
        } else {
            Log::warning('no data or token:', ['token' => $token, 'data' => $data]);
        }
    }

    //FCM v1 HTTP API
    public static function pushHttp($data)
    {
        $accessToken = Cache::get("accessToken");
        if (!$accessToken) {
            $config_path = base_path() . '/service-account-file.json';
            $client = new GooleClient();
            $client->useApplicationDefaultCredentials();
            $client->setAuthConfig($config_path);
            $client->setScopes('https://www.googleapis.com/auth/firebase.messaging');
            $res = $client->fetchAccessTokenWithAssertion();
            $accessToken = $res['access_token'];
            Cache::put("accessToken", $accessToken, $res['expires_in']);
        }
        $base_url = 'https://fcm.googleapis.com/v1/projects/'.config('web.baseUrlId').'/messages:send';
        $header = [
            'Authorization: Bearer ' . $accessToken,
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
        // 中国側テスト用設定
        if (!empty(config("firebase.httpProxy.proxy"))) {
            curl_setopt($curl, CURLOPT_PROXY, config("firebase.httpProxy.proxy"));
        }

        $response = curl_exec($curl);
        if ($response == false) {
            Log::debug("Chat Firebase Error:");
            Log::debug(json_encode($data));
            return 'error';
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $result = json_decode($body, true);

        curl_close($curl);
        if (array_key_exists('error', $result)) {
            $config_path = base_path() . '/service-account-file.json';
            $client = new GooleClient();
            $client->useApplicationDefaultCredentials();
            $client->setAuthConfig($config_path);
            $client->setScopes('https://www.googleapis.com/auth/firebase.messaging');
            $res = $client->fetchAccessTokenWithAssertion();
            $accessToken = $res['access_token'];
            Cache::put("accessToken", $accessToken, $res['expires_in']);
        }
        return 'success';
    }

}
