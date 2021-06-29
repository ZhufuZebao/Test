<?php
/**
 * チャットのタスク担当者管理モデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chattaskcharge extends Model
{
    /**
     * タスクの担当者一覧を取得
     *
     * @return  array   取得したデータ
     */
    static function get($task_id)
    {
        $sql = <<<EOF
select c.user_id, u.name
from chattaskcharges c
    inner join users u
        on c.user_id = u.id
where c.task_id = ?
EOF;

        $data = DB::select($sql, [
                $task_id,
        ]);

        return $data;
    }
}
