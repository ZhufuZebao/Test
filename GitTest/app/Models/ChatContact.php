<?php
/**
 * アカウント管理テーブル
 *
 * @author  WuJi
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatContact extends Model
{   // 2020-10-22 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chatcontacts";
    protected $guarded = ['id'];

    public function accounts()
    {
        return $this->hasOne('App\Models\Account','id','to_user_id')
            ->select('id','name','enterprise_id','coop_enterprise_id','company_name','email','file','publicity','deleted_at');
    }

    public function accountsInvite()
    {
        return $this->hasOne('App\Models\Account','id','from_user_id')
            ->select('id','name','enterprise_id','company_name','email','file','deleted_at');
    }

    public function userworkareas()
    {
        return $this->hasMany('App\Models\UserWorkarea','user_id','to_user_id');
    }

    public function userworkareasInvite()
    {
        return $this->hasMany('App\Models\UserWorkarea','user_id','from_user_id');
    }

}
