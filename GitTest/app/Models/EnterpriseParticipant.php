<?php
/**
 * 協力会社Model
 * @author  ZhouChuanhui
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnterpriseParticipant extends Model
{   // 2020-10-27 #2298
    use SoftDeletes;//Turn on softDeletes
    protected $table = "enterprise_participants";
    public $primaryKey = "id";
    protected $guarded = ['id'];
    protected $hidden = ['invitation_code'];

    public function account()
    {
        return $this->hasOne('App\Models\Account','id','user_id')
            ->select('id','name','enterprise_id','coop_enterprise_id','file','deleted_at');
    }

    public function enterprises()
    {
        return $this->hasOne('App\Models\Enterprise','id','enterprise_id');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\Account','id','created_by');
    }

}
