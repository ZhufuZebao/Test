<?php
/**
 * 教えてサイトのカテゴリーテーブル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Category extends Model
{
    /**
     * カテゴリーテーブルからデータを取得
     *
     * @return  array   取得したデータ
     */
    static function get()
    {
        $categorys = DB::table('categorys')
        ->select('*')
        ->get();

        return $categorys;
    }
}