<?php
/**
 * 都道府県テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Pref extends Model
{
    /**
     * 都道府県テーブルからデータを取得
     *
     * @return  array   取得したデータ
     */
    static function get()
    {
        $prefs = DB::table('prefs')
        ->select('*')
        ->get();

        $rows = null;
        foreach ($prefs as $key => $item) {
            $rows[$item->id] = $item->name;
        }

        return $rows;
    }

    /**
     * 都道府県名から都道府県IDを取得
     *
     * @param   string  $name   都道府県名
     * @return  int     $id     都道府県ID
     */
    static function getId($name)
    {
        $id = DB::table('prefs')
        ->where('name', $name)
        ->value('id');

        return $id;
    }
}