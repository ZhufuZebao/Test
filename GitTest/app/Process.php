<?php
/**
 * 工程管理テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use DB;

class Process extends Model
{
    /**
     * 工程管理データを取得
     *
     * @param   int     $project_id     プロジェクトID
     * @param   int     $year           年
     * @param   int     $month          月
     * @return  array   取得したデータ
     */
    static function get($project_id, $year, $month)
    {
        $sql = <<<EOF
select  t1.*,
        t2.name as part_name, t2.color as color,
        t3.name as staff_name,
        date_format(t1.st_expected, '%Y/%m/%d') as st_disp,
        date_format(t1.ed_expected, '%Y/%m/%d') as ed_disp,
        date_format(t1.st_expected, '%d') as st_day,
        date_format(t1.st_expected, '%w') as st_week,
        date_format(t1.ed_expected, '%d') as ed_day,
        date_format(t1.ed_expected, '%w') as ed_week,
        datediff(t1.ed_expected, t1.st_expected) + 1 as diff
from   processs t1
        inner join parts t2
            on t1.part_id = t2.id
        inner join staffs t3
            on t1.staff_id = t3.id
where t1.project_id = ?
and (date_format(t1.st_expected, '%Y%m') = ?
 or date_format(t1.ed_expected, '%Y%m') = ?)
order by t1.st_expected, t1.ed_expected
EOF;

        $data = DB::select($sql, [
                $project_id,
                sprintf('%04d%02d', $year, $month),
                sprintf('%04d%02d', $year, $month),
        ]);

        return $data;
    }

    /**
     * 工程管理のデータを登録
     *
     * @param   int     $project_id         プロジェクトID
     * @param   int     $contractor_id      会社ID
     * @param   int     $part_id            役割ID
     * @param   int     $staff_id           社員ID
     * @param   string  $st_expected        予定日（開始日）
     * @param   string  $ed_expected        予定日（終了日）
     * @param   string  $st_result          実績日（開始日）
     * @param   string  $ed_result          実績日（終了日）
     * @return  array   取得したデータ
     */
    static function set($project_id, $contractor_id, $part_id, $staff_id,
                        $st_expected, $ed_expected, $st_result, $ed_result)
    {
        if ($data = self::getProcess($project_id, $contractor_id, $part_id, $staff_id,
                $st_expected, $ed_expected)) {
            return $data;
        }

        $sql  = "insert into processs (";
        $sql .= "   project_id, contractor_id, part_id, staff_id, st_expected, ed_expected, st_result, ed_result\n";
        $sql .= ") values (\n";
        $sql .= "   ?, ?, ?, ?, ?, ?, ?, ?\n";
        $sql .= ")";

        $params = [
                $project_id, $contractor_id, $part_id, $staff_id,
                $st_expected, $ed_expected, $st_result, $ed_result
        ];

        $ret = DB::insert($sql, $params);
    }

    /**
     * 工程管理データを取得
     *
     * @param   int     $project_id         プロジェクトID
     * @param   int     $contractor_id      会社ID
     * @param   int     $part_id            役割ID
     * @param   int     $staff_id           社員ID
     * @param   string  $st_expected        予定日（開始日）
     * @param   string  $ed_expected        予定日（終了日）
     * @return  array   取得したデータ
     */
    static function getProcess($project_id, $contractor_id, $part_id, $staff_id,
            $st_expected, $ed_expected)
    {
        $sql  = <<<EOF
select * from processs
where project_id = ?
and contractor_id = ?
and part_id = ?
and staff_id = ?
and st_expected = ?
and ed_expected = ?
EOF;
        $data = DB::select($sql, [
                $project_id, $contractor_id, $part_id, $staff_id,
                $st_expected, $ed_expected
        ]);

        return $data;
    }
}
