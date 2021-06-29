<?php
/**
 * 担当者テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Staff extends Model
{
    /**
     * 社員データを取得する
     *
     * @param   int     $contractor_id      下請け会社ID
     * @return  array   取得したデータ
     */
    static function get($contractor_id)
    {
        $prefs = DB::table('staffs')
        ->select('*')
        ->where('contractor_id', $contractor_id)
        ->get();

        $rows = array();
        foreach ($prefs as $key => $item) {
            $rows[$item->id] = $item->name;
        }

        return $rows;
    }
}