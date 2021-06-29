<?php

namespace App\Helpers;

use App\Http\Controllers\ScheduleController;

use GuzzleHttp\Client;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiHelper
{

    static public $acitivityInvitation = "invitation";
    static public $acitivityQr = "qr";
    static public $chatApp = "1";
    static public $scheduleApp = "2";
    static public $web = "3";

    /**
     * 仲間承認依頼招待URL取得
     * @param  string $targetUserId
     * @param string $activityKind
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getInvitationUrl($targetUserId, $activityKind = 'invitation')
    {

        $appUrl = config('app.url');
        $appUrl = Str::endsWith($appUrl, '/') ? $appUrl : $appUrl . '/';
        $url = $appUrl . '?user_id=' . $targetUserId . '&actvity_kind=' . $activityKind;
        return self::getShortenLink($url, self::$chatApp);
    }

    /**
     * Firebase Dynamic Linksのショートリンクを取得する
     *
     * @param string $longUrl
     * @param string $appKind
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getShortenLink($longUrl, $appKind = '1')
    {
        if (config('firebase.disable') == '1') {
            return $longUrl;
        }
        if ($appKind == self::$scheduleApp) {
            $androidPackageName = config('firebase.androidSchedulePackageName');
            $iosBundleId = config('firebase.iosScheduleBundleId');
        } else {
            $androidPackageName = config('firebase.androidChatPackageName');
            $iosBundleId = config('firebase.iosChatBundleId');
        }
        $domainUriPrefix = config('firebase.linkDomain');
        $apiKey = config('firebase.apikey');
        $url = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=' . $apiKey;
        $data = array(
            "dynamicLinkInfo" => array(
                "domainUriPrefix" => $domainUriPrefix,
                "link" => $longUrl,
                "androidInfo" => array(
                    "androidPackageName" => $androidPackageName
                ),
                "iosInfo" => array(
                    "iosBundleId" => $iosBundleId
                ),
            )
        );
        $client = new Client([
            'base_uri' => $url
        ]);
        Log::debug(json_encode($data));
        $request = new \GuzzleHttp\Psr7\Request('POST', $url, [
            'Content-Type' => 'application/json'
        ], json_encode($data));
        try {
            $response = $client->send($request, config("firebase.httpProxy"));
        } catch (ClientException $e) {
            Log::warning("push firebase error");
            Log::error($e->getRequest());
            Log::error($e->getResponse());
        }
        if ($response->getStatusCode() !== 200) {
            Log::warning("push firebase error");
            Log::error($response->getStatusCode() . '::' . $response->getReasonPhrase());
            return null;
        } else {
            Log::debug("push firebase:200");
        }
        $result = json_decode((string)$response->getBody(), true);
        Log::debug($result);
        if (isset($result->error)) {
            Log::warning($result['error']['message']);
            return null;
        } else {
            Log::debug($result['shortLink']);
            return $result['shortLink'];
        }
    }
}
