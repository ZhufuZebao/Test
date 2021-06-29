<?php
/**
 * 受注履歴テーブル
 *
 * @author  Miyamoto
 */

namespace App;

use DB;

class History extends Model
{
    /**
     * 受注履歴のデータを取得
     *
     * @param   int     $user_id        ユーザーID
     * @param   int     $project_id     プロジェクトID
     * @return  array   取得したデータ
     */
    static function get($user_id, $project_id='')
    {
        $params = array($user_id);

        $where = '';
        if ($project_id != '') {
            $where = 'and historys.project_id = ?';
            $params[] = $project_id;
        }

        $sql =<<<EOF
select  historys.*,
        projects.name as project_name,
        contractors.id as contractor_id,
        contractors.name as contractor_name,
        date_format(historys.st_date,'%c') as st_disp_m,
        date_format(historys.st_date,'%e') as st_disp_d,
        elt(weekday(historys.st_date)+1, '月', '火', '水', '木', '金', '土', '日') as st_disp_w,
        date_format(historys.ed_date,'%c') as ed_disp_m,
        date_format(historys.ed_date,'%e') as ed_disp_d,
        elt(weekday(historys.ed_date)+1, '月', '火', '水', '木', '金', '土', '日') as ed_disp_w
from    historys
        inner join projects
            on historys.project_id = projects.id
        inner join contractors
            on projects.contractor_id = contractors.id
where   historys.user_id = ?
{$where}
order by projects.st_date desc
EOF;

        $historys = DB::select($sql, $params);

        return $historys;
    }
}
