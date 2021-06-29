<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Node
{
    const NODE_TYPE_DIR = 0;
    const NODE_TYPE_FILE = 1;

    const ACCESS_TYPE_INNER = 1;
    const ACCESS_TYPE_SHARE = 2;
    const ACCESS_TYPE_CHAT = 3;

    const OP_MOVE = 1;
    const OP_COPY = 2;

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

    public static function findAll($project_id, $attributes = [], $includeDeleted = false)
    {
        $sql = self::getSelectBaseSql();

        $where = $includeDeleted ? '1=1 AND project_id = :project_id' : 'NODE.deleted_at IS NULL AND project_id = :project_id';

        $where .= self::getExceptionWhere($project_id);

        $params = [];
        foreach ((array)$attributes as $key => $value) {
            $where .= " AND NODE.$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':project_id'] = $project_id;
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

    private static function getExceptionWhere($project_id)
    {
        $doc_db = config('const.db_database_doc');
        $where = '';

        $res = @self::checkUser($project_id);
        if ($res == 1002) {

            $parent_id = DB::table("$doc_db.nodes")->where('project_id', $project_id)->whereNull('node_id_parent')->where('node_name', '=', '社内')->value('node_id');
            $node_id_str = "";
            $node_id = [];

            $node_id[] = $parent_id;
            $i = 0;
            while(true) {
                @self::getNodeId($node_id[$i], $node_id);
                $i++;
                if (!isset($node_id[$i])) {
                    break;
                }
            }

            foreach ($node_id as $id) {
                $node_id_str .= ($node_id_str != '') ? ', '. $id : $id;
            }

            if ($node_id_str != '') {
                $where .= " AND NODE.node_id not in ($node_id_str)";
            }
        }

        return $where;
    }

    public static function findFirst($project_id, $attributes = [], $includeDeleted = false)
    {
        return @self::findAll($project_id, $attributes, $includeDeleted)[0];
    }

    public static function exists($project_id, $attribues = [], $includeDeleted = false)
    {
        return count(self::findAll($project_id, $attribues, $includeDeleted)) !== 0;
    }

    public static function delete($nodeId, $project_id)
    {
        $sql = <<<SQL
UPDATE
    __DOCDB__.nodes
SET
    deleted_at = NOW(),
    updated_at = NOW()
WHERE
    node_id = :node_id
SQL;
        $sql = str_replace('__DOCDB__', config('const.db_database_doc'), $sql);
        DB::update($sql, [
            ':node_id' => $nodeId,
        ]);

        return self::findFirst($project_id, ['node_id' => $nodeId], true);
    }

    public static function rmnode($target, $project_id) {
        $doc_db = config('const.db_database_doc');

        // 自分削除
        $node = self::delete($target, $project_id);

        // 孤児を削除
        $sql = <<<SQL
UPDATE
    __DOCDB__.nodes AS CH
LEFT JOIN
    __DOCDB__.nodes AS PA ON
        PA.deleted_at IS NULL
        AND CH.node_id_parent = PA.node_id
SET
    CH.deleted_at = NOW(),
    CH.updated_at = NOW()
WHERE
    CH.deleted_at IS NULL
    AND CH.node_id_parent IS NOT NULL
    AND PA.node_id IS NULL
    AND CH.project_id = :project_id
SQL;
        $sql = str_replace('__DOCDB__', config('const.db_database_doc'), $sql);
        while (DB::update($sql, ['project_id' => $project_id]) > 0); // 孤児がいなくなるまで

        return $node;
    }

    public static function checkUser($project_id)
    {
        $enterprise_id1 = DB::table("projects")->where('id', $project_id)->value('enterprise_id');
        //echo 'enterprise_id1='. $enterprise_id1.'<br>';

        $enterprise_id2 = DB::table("users")->where('id', Auth::id())->value('enterprise_id');
        //echo 'enterprise_id2='. $enterprise_id2.'<br>';

        if ($enterprise_id1 == $enterprise_id2) {
            // 事業者
            return 1001;

        } else {
            $project_participant_id = DB::table("project_participants")->where('user_id', Auth::id())->where('project_id', $project_id)->value('id');

            if ($project_participant_id != null) {
                // 協力会社
                return 1002;
            }
        }

        // 関係ないユーザ
        return null;
    }
}
