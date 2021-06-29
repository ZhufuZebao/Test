<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/06/20
 * Time: 16:09
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleSub extends Model
{
    // 2020-10-26 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "schedulesubs";
    protected $guarded = ['id'];

    public function schedules(){
        return $this->belongsTo('App\Models\Schedule', 'relation_id', 'id');
    }

    /**
     * 日付けからスケジュール取得
     */
    static function getSubId($id, $targetDate) {
        $subId = Schedulesub::select('id')
            ->where([
                ['relation_id', '=', $id],
                ['s_date', '=', $targetDate],])
            ->get();
        return isset($subId[0]) ? $subId[0]->id : '0';
    }
}
