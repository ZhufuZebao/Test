<?php
/**
 * チャットのメッセージ管理のモデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chatmessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id', 'from_user_id', 'message', 'file_name',
    ];

    /**
     * チャットメッセージ取得
     *
     * @param   int     $group_id       チャットグループID
     * @return  array   チャットメッセージ
     */
    static function getChatMessage($group_id)
    {
        $sql = <<<EOF
select  m.id, m.group_id, m.from_user_id, u.name as name, u.file,
        m.message as message, m.file_name, m.created_at as created_at,
        p.user_id as to_re_id
from    chatmessages m
        inner join users u
            on m.from_user_id = u.id

        left join chatpersons p
            on m.id = p.message_id

where   m.group_id = ?
order by m.created_at
EOF;

        $data = DB::select($sql, [
                $group_id,
        ]);

        return $data;
    }

    /**
     * メッセージを更新
     *
     * @param   int     $message_id     メッセージID
     * @param   string  $message        メッセージ
     * @param   string  $file_name      添付ファイル名
     */
    static function updateChatMessage($message_id, $message, $file_name)
    {
        $sql = <<< EOF
update  chatmessages
set     message = ?,
        file_name = ?,
        updated_at = sysdate()
where   id = ?
EOF;

        DB::update($sql, [
                $message,
                $file_name,
                $message_id
        ]);
    }

    /**
     * 最後にグループチャットしたグループIDを取得
     *
     * @param   int     $user_id    ユーザーID
     */
    static function lastChatGroup($user_id)
    {
/*
        $sql = <<<EOF
select  max(group_id) as group_id
from    chatmessages c
        inner join groups g
            on c.group_id = g.id
            and g.kind = 0
where   from_user_id = ?
EOF;
*/
        $sql = <<<EOF
select  max(group_id) as group_id
from    chatmessages c
        inner join groups g
            on c.group_id = g.id
where   from_user_id = ?
EOF;

        $data = DB::select($sql, [
                $user_id,
        ]);

        return (is_array($data) && !empty($data) && isset($data[0])) ? $data[0]->group_id : null;
    }

    /**
     * 最後にダイレクトチャットしたグループIDを取得
     *
     * @param   int     $user_id    ユーザーID
     */
    static function lastChatUser($user_id)
    {
        $sql = <<<EOF
select  max(group_id) as group_id
from    chatmessages c
        inner join groups g
            on c.group_id = g.id
            and g.kind = 1
where   from_user_id = ?
EOF;

        $data = DB::select($sql, [
                $user_id,
        ]);

        return (is_array($data) && !empty($data) && isset($data[0])) ? $data[0]->group_id : null;
    }

    /**
     * 一番最後のメッセージIDを取得
     *
     * @param   int     $group_id   グループID
     * @return  int     最後のメッセージID
     */
    static function getMaxMessageId($group_id) {
        $sql = <<<EOF
select  max(id) as max_id
from    chatmessages
where   group_id = ?
EOF;

        $data = DB::select($sql, [
                $group_id,
        ]);

        return (is_array($data) && !empty($data) && isset($data[0])) ? $data[0]->max_id : 0;
    }

    /**
     * チャットのメッセージから検索する
     *
     * @param   int     $group_id   グループID
     * @param   string  $keyword    検索キーワード
     *
     */
    static function searchMessage($group_id, $keyword) {
        $sql = <<<EOF
select  m.id, m.group_id, m.from_user_id, u.name as name, u.file,
        m.message as message, m.file_name, m.created_at as created_at
from    chatmessages m
        inner join users u
            on m.from_user_id = u.id
where   m.group_id = ?
and     m.message like ?
order by m.created_at
EOF;

        $data = DB::select($sql, [
                $group_id,
                '%'. $keyword. '%',
        ]);

        return $data;
    }

    /**
     * 未読数取得
     * @param unknown $group_id
     * @param unknown $user_id
     * @return number
     */
    static function getUnreadCount($group_id, $user_id) {

        $sql = <<<EOF
select  count(*) as cnt
from    chatmessages
where   chatmessages.id > (
            select  message_id
            from    chatlastreads
            where   group_id = {$group_id}
            and     user_id = {$user_id}
        )
and     chatmessages.group_id = {$group_id}
and     chatmessages.from_user_id <> {$user_id}
EOF;


        $data = DB::select($sql, [
                $group_id,
                $user_id,
        ]);

        return (is_array($data) && !empty($data) && isset($data[0])) ? $data[0]->cnt : 0;
    }
}
