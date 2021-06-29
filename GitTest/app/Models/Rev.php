<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Rev
{
    public static function findAll($attributes = [], $includeDeleted = false)
    {
        $docDb = config('web.db_database_doc');
        $sql = <<<SQL
SELECT
    REV.*,
    U.name AS commit_user
FROM
$docDb.revs AS REV
LEFT JOIN
    users AS U ON REV.user_id_commit = U.id
WHERE
    %s
ORDER BY
    node_id,
    rev_no DESC
SQL;
        $where = $includeDeleted ? '1=1' : 'REV.deleted_at IS NULL';
        $params = [];
        foreach ((array)$attributes as $key => $value) {
            $where .= " AND $key = :$key";
            $params[":$key"] = $value;
        }
        $sql = sprintf($sql, $where);

        return DB::select($sql, $params);
    }

    public static function findFirst($attributes = [], $includeDeleted = false)
    {
        return @self::findAll($attributes, $includeDeleted)[0];
    }

    public static function exists($attribues = [], $includeDeleted = false)
    {
        return self::findAll($attribues, $includeDeleted).length !== 0;
    }
}
