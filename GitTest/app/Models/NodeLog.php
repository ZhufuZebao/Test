<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NodeLog
{
    // 生成ログ
    public static function c($node, $description = null)
    {
        self::log('C', $node->updated_at, $node->node_id, $description);
    }

    // 更新ログ
    public static function u($node, $description = null)
    {
        self::log('U', $node->updated_at, $node->node_id, $description);
    }

    // 削除ログ
    public static function d($node, $description = null)
    {
        self::log('D', $node->updated_at, $node->node_id, $description);
    }

    private static function log($type, $opeTime, $nodeId, $description)
    {
        if (!$opeTime || !$nodeId) {
            Log::info(__METHOD__ . ': invalid argument');
            return;
        }
        $sql = <<<SQL
INSERT INTO __DOCDB__.node_logs (
    ope_time, user_id, ope_type, node_id, ope_desc
) VALUES (
    :ope_time, :user_id, :ope_type, :node_id, :ope_desc
)
SQL;

        $sql = str_replace('__DOCDB__', config('const.db_database_doc'), $sql);
            DB::insert($sql, [
            ':ope_time' => $opeTime,
            ':user_id' => Auth::id(),
            ':ope_type' => $type,
            ':node_id' => $nodeId,
            ':ope_desc' => $description,
        ]);
    }
}
