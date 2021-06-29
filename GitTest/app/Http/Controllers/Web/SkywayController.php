<?php
namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

define('SECRETKEY', config('web.skyway.secretkey'));
define('CREDENTIALTTL', 3600); // 1 hour

class SkywayController extends \Illuminate\Routing\Controller
{
    public function authenticate(Request $request) {

        $peerId       = $request->input('peerId');
        $sessionToken = $request->input('sessionToken');

        if ($peerId== '' || $sessionToken == '') {
            http_response_code(400);
            return 'Bad Request';
        }

        if ($this->checkSessionToken($peerId, $sessionToken)) {
            $timestamp = time();

            $authToken = $this->calculateAuthToken($peerId, $timestamp);

            $returnJSON = array(
                    'peerId' => $peerId,
                    'timestamp' => $timestamp,
                    'ttl' => CREDENTIALTTL,
                    'authToken' => $authToken
            );

            header('Content-Type: application/json');
            return response()->json($returnJSON);

        } else {
            http_response_code(401);
            return 'Authentication Failed';
        }
    }

    private function checkSessionToken($peerId, $token) {
        // Implement checking whether the session is valid or not.
        // Return true if the session token is valid.
        // Return false if it is invalid.
        return true;
    }

    private function calculateAuthToken($peerId, $timestamp) {
        $message = "$timestamp:" . CREDENTIALTTL . ":$peerId";
        return base64_encode(hash_hmac('sha256', $message, SECRETKEY, true));
    }
}
