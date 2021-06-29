<?php
/**
 * アカウント管理テーブル
 *
 * @author  WuJi
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Developer extends Model
{
    use SoftDeletes;
    protected $table = "developers";
    protected $fillable = ['user_id','created_at'];

    public static function check($user_id) {
        $developer = DB::table('developers')->where('user_id', $user_id)->first();
        return $developer ? true : false;
    }
}
