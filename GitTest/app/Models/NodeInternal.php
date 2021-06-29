<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NodeInternal
{
    const NODE_TYPE_DIR = 0;
    const NODE_TYPE_FILE = 1;

    const SELECT_BASE_SQL = <<<SQL
SELECT
    NODE.*,
    REV.rev_no,
    REV.user_id_commit,
    REV.commit_comment,
    REV.commit_time,
    REV.file_name,
    REV.file_size,
    REV.file_type,
    REV.file_time,
    REV.upload_time,
    COALESCE(REV.commit_time, NODE.updated_at) AS last_updated,
    U1.name AS owner,
    U2.name AS locker
FROM
__DOCDB__.nodes AS NODE
LEFT JOIN
    (
        SELECT
            *
        FROM
        __DOCDB__.revs
        WHERE
            (node_id, rev_no) IN (SELECT node_id, max(rev_no) FROM __DOCDB__.revs WHERE deleted_at IS NULL GROUP BY node_id)
    ) AS REV ON
        NODE.node_id = REV.node_id
LEFT JOIN
    users AS U1 on NODE.user_id_owner = U1.id
LEFT JOIN
    users AS U2 on NODE.user_id_lock = U2.id
WHERE
    %s
SQL;

    private static function getSelectBaseSql()
    {
        return str_replace('__DOCDB__', config('const.db_database_doc'), self::SELECT_BASE_SQL);
    }

    public static function findAll($enterprise_id, $attributes = [], $includeDeleted = false)
    {
        $sql = self::getSelectBaseSql();

        $where = $includeDeleted ? '1=1 AND NODE.enterprise_id = :enterprise_id' : 'NODE.deleted_at IS NULL AND NODE.enterprise_id = :enterprise_id';

        $params = [];
        foreach ((array)$attributes as $key => $value) {
            $where .= " AND NODE.$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':enterprise_id'] = $enterprise_id;
        $sql = sprintf($sql, $where);

        return DB::select($sql, $params);
    }

    private static function getNodeId($parent_id, &$node_id)
    {
        $doc_db = config('const.db_database_doc');
        $parents = DB::table("$doc_db.nodes")->select('node_id')->where('node_id_parent', $parent_id)->get();
        foreach ($parents as $parent) {
            $node_id[] = $parent->node_id;
        }
    }

    public static function findFirst($enterprise_id, $attributes = [], $includeDeleted = false)
    {
        return @self::findAll($enterprise_id, $attributes, $includeDeleted)[0];
    }

    public static function exists($enterprise_id, $attribues = [], $includeDeleted = false)
    {
        return count(self::findAll($enterprise_id, $attribues, $includeDeleted)) !== 0;
    }

}