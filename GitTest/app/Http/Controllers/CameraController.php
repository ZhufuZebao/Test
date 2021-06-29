<?php
/**
 * Webカメラのコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use DB;
use App\Chatcontact;
use App\Chatgroup;

class CameraController extends \App\Http\Controllers\Controller
{
    public function index($group_id = '')
    {
        // コンタクト承認一覧を取得
        $contacts= Chatcontact::getContactList(\Auth::id());

        // チャットグループを取得
        $groups = Chatgroup::get(\Auth::id());

        $room = array();
        if (is_array($contacts)) {
            foreach ($contacts as $row) {
                $tmp['room_name'] = 'group_'. $row->group_id;
                $tmp['name']      = $row->name;
                $room[] = $tmp;
            }
        }
        if (is_array($groups)) {
            foreach ($groups as $row) {
                $tmp['room_name'] = 'group_'. $row->group_id;
                $tmp['name']      = $row->group_name;
                $room[] = $tmp;
            }
        }

        // ユーザー情報を取得
        $user   = DB::table('users')->where('id', \Auth::id())->first();

//print_r($room);
        return view('/camera/index2', [
                'group_id' => $group_id,
                'room'  => $room,
                'user'  => $user,
        ]);
    }
}
