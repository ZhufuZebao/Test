<?php
/**
 * 求人情報テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use DB;

class Work extends Model
{
    /**
     * 求人情報を取得
     *
     * @param   string  $keyword    検索キーワード
     * @param   string  $place      勤務地（検索用）
     * @return  array   取得したデータ
     */
    static function getList($keyword, $place)
    {
        $sql = "select works.*, \n";
        $sql .= "contractors.name as contractor_name, contractors.addr as contractor_addr, \n";
        $sql .=" contracts.name as contract_name\n";
        $sql .= "from works\n";
        $sql .= " inner join contractors\n";
        $sql .= "     on works.contractor_id = contractors.id\n";
        $sql .= " inner join contracts\n";
        $sql .= "     on works.contract_id = contracts.id\n";
        $sql .= "where (works.name like ? or works.contents like ?)\n";
        $sql .= "and place like ?\n";

        $data = DB::select($sql, [
            '%'. $keyword. '%',
            '%'. $keyword. '%',
            '%'. $place. '%',
        ]);

        return $data;

    }

    /**
     * 求人詳細情報を取得
     *
     * @param   int     $id     求人ID
     * @return  array   取得したデータ
     */
    static function getJobDetails($id)
    {
        $sql = "select works.*, \n";
        $sql .= "contractors.name as contractor_name, contractors.addr as contractor_addr, \n";
        $sql .=" contracts.name as contract_name\n";
        $sql .= "from works\n";
        $sql .= " inner join contractors\n";
        $sql .= "     on works.contractor_id = contractors.id\n";
        $sql .= " inner join contracts\n";
        $sql .= "     on works.contract_id = contracts.id\n";
        $sql .= "where works.id = ?\n";

        $data = DB::select($sql, [
                $id
        ]);

        return $data;
    }
}
