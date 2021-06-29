<?php
/**
 * QRコードでお友達登録のコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use DB;


class QRController extends \Illuminate\Routing\Controller
{
    /**
     * 友達登録用に自分のQRコードを生成したものを読み込んだ時の処理
     *
     */
    public function myQR($id, $key) {

        $data = DB::table('myqrs')
        ->where('user_id', $id)
        ->where('qrkey', $key)
        ->first();

        return view('/chat/myqr', [
                'id'    => $id,
                'key'   => $key,
                'data'  => $data,
        ]);
    }
    /**
     * スケジュールアプリから友達登録用に自分のQRコードを生成したものを読み込んだ時の処理
     */
    public function myScheduleQR($id, $key) {

        $data = DB::table('myqrs')
        ->where('user_id', $id)
        ->where('qrkey', $key)
        ->first();

        return view('/schedule/myqr', [
                'id'    => $id,
                'key'   => $key,
                'data'  => $data,
        ]);
    }

}
