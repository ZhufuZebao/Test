<?php
/**
 * チャットの既読管理のモデル
 *
 * @author  Miyamoto
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chatlastread extends Model
{
    /**
     * 既読した最後のメッセージIDを取得
     *
     * @param   int     $group_id   グループID
     * @param   int     $user_id    ユーザーID
     * @return  int     既読した最後のメッセージID
     */
    static function get($group_id, $user_id)
    {
        $readMessageId = DB::table('chatlastreads')
                ->where('group_id', $group_id)
                ->where('user_id', $user_id)
                ->value('message_id');

        return $readMessageId == '' ? 0 : $readMessageId;
    }

    // 未使用
    static function set($group_id, $user_id, $message_id)
    {
        $sql = <<<EOF
select  *
from    chatlastreads
where   group_id = ?
and     user_id = ?
EOF;

        $data = DB::select($sql, [
                $group_id,
                $user_id,
        ]);
//print_r($data);
//exit();

        if (is_array($data) && !empty($data) && isset($data[0])) {

            $sql = <<<EOF
update  chatlastreads
set     message_id = ?,
        updated_at = sysdate()
where   group_id = ?
and     user_id = ?
EOF;

            DB::update($sql, [
                    $message_id,
                    $group_id,
                    $user_id,
            ]);

        } else {
            $sql = <<<EOF
insert into chatlastreads (
    group_id, user_id, message_id, created_at, updated_at
) values (
    ?, ?, ?, sysdate(), sysdate()
);
EOF;

            DB::insert($sql, [
                    $group_id,
                    $user_id,
                    $message_id,
            ]);
        }

        return $data;
    }
}
