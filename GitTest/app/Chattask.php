<?php
/**
 * チャットのタスク管理モデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chattask extends Model
{
    /**
     * タスクの一覧を取得
     *
     * @return  array   取得したデータ
     */
    static function get($group_id, $user_id)
    {
        $sql = <<<EOF
select t.*, u.id as create_user_id, u.name as create_user_name, u.file as user_file
from chattasks t
    inner join chattaskcharges c
        on t.id = c.task_id
    inner join users u
        on t.create_user_id = u.id
where t.group_id = ?
and c.user_id = ?
and t.complete_date is null
order by created_at desc
EOF;

        $data = DB::select($sql, [
                $group_id,
                $user_id
        ]);

        return $data;
    }
}
