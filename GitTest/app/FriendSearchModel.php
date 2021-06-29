<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/04/12
 * Time: 9:52
 */

namespace App;


use Illuminate\Support\Facades\DB;

class FriendSearchModel
{
    public $id;
    public $name;

    public function init($params)
    {
        $this->id      = $params['id']      ?? null;
        $this->name    = $params['name']    ?? null;
    }

    public function search()
    {
        $q = Friend::query();
        $q->where('id','!=' ,1);
        $friendsId = DB::table("invitation_message")->whereRaw('send_id = ? and status = 1',[1])->select('receive_id')->get();
        $arr = [];
        for($i=0;$i<$friendsId->count();$i++){
            $arr[$i]=$friendsId[$i]->receive_id;
        }

        $q->whereIn('id',$arr);

        if($this->id)
        {
            $q->where('id', $this->id);
        }

        if($this->name)
        {
            $q->where('name', 'LIKE', "%{$this->name}%");
        }
        return $q;
    }
}