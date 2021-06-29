<?php
/**
 * チャットのお知らせ管理のモデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chatnews extends Model
{
    /**
     * お知らせのデータを取得
     *
     * @return  array   取得したデータ
     */
    static function get()
    {
        $sql = <<<EOF
select  *
from    chatnewss
where   curdate() between st_date and ed_date
order by st_date desc
EOF;

        $data = DB::select($sql);

        return $data;
    }
}
