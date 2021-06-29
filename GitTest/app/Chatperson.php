<?php
/**
 * チャットグループ管理のモデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chatperson extends Model
{
    protected $fillable = [
            'group_id', 'user_id', 'from_user_id', 're_message_id', 'flag'
    ];

    /**
     * TO、RE のユーザーを登録
     *
     * @param   int     $group_id           グループID
     * @param   int     $user_id            ユーザーID
     * @param   int     $from_user_id       送信者のユーザーID
     * @param   int     $re_message_id      返信する元のメッセージID
     * @param   int     $flag               1:TO、2:RE
     */
    static function set($group_id, $user_id, $from_user_id, $message_id, $re_message_id, $flag)
    {
        $sql  = "insert into chatpersons (\n";
        $sql .= "group_id, user_id, from_user_id, message_id, re_message_id, flag, created_at, updated_at\n";
        $sql .= ") values (?, ?, ?, ?, ?, ?, sysdate(), sysdate())";

        $params = [
                $group_id, $user_id, $from_user_id, $message_id, $re_message_id, $flag
        ];
//echo $sql;
//print_r($params);
//exit();

        $ret = DB::insert($sql, $params);
//var_dump($ret);
//exit();
    }
}