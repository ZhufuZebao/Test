<?php
/**
 * チャットグループ管理のモデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Usertemporary extends Model
{
    static function checkAuthKey($authkey) {

        $sql = <<<EOF
select  id, email
from    usertemporarys
where   authkey = ?
and     created_at > date_add(sysdate(), interval -1 hour)
EOF;

        $data = DB::select($sql, [
                $authkey
        ]);

        if (is_array($data) && !empty($data)) {
            return 'OK';
        }
        return 'NG';
    }
}
