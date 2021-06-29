<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Created by PhpStorm.
 * User: 周
 * Date: 2019/10/31
 * Time: 15:16
 */

class UserCategories extends Model
{
    use SoftDeletes;
    protected $table = "user_categories";
    public $primaryKey = "id";
    protected $guarded = ['id'];

    /**
     * 対象ユーザーの分野取得
     */
    static function getUserCategories($userId) {
        $userCategories = UserCategories::leftJoin('mst_category1', 'user_categories.category_id1', '=', 'mst_category1.id')
            ->leftJoin('mst_category2', function($join)
            {
                $join->on('user_categories.category_id2', '=', 'mst_category2.id')
                    ->on('user_categories.category_id1', '=', 'mst_category2.category1_id');
            })
            ->where('user_categories.user_id', '=', $userId)
            ->select('user_categories.*', 'mst_category1.name as category_name1'
                , 'mst_category2.name as category_name2', 'mst_category2.text_flag')
            ->orderBy('user_categories.id', 'asc')
            ->get();
        return $userCategories;
    }
}