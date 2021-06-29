<?php
/**
 * チャットグループ管理のモデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Group extends Model
{
    /**
     * チャットグループのデータを登録
     *
     * @param   string  $name   グループ名
     * @param   int     $kind   0=グループチャット, 1=ダイレクトチャット
     * @return  int     $id     グループID
     */
    static function set($name, $kind)
    {
        $id = DB::table('groups')->insertGetId([
                'name'       => $name,
                'kind'       => $kind,
                'created_at' => date('Y/m/d H:i:s')
        ]);

        return $id;
    }
}
