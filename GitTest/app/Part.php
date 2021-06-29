<?php
/**
 * 役割（設計・製造・検査など）テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Part extends Model
{
    /**
     * 役割テーブルからデータ取得
     *
     * @return  array   取得したデータ
     */
    static function get()
    {
        $prefs = DB::table('parts')
        ->select('*')
        ->get();

        foreach ($prefs as $key => $item) {
            $rows[$item->id] = $item->name;
        }

        return $rows;
    }
}