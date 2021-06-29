<?php
/**
 * アカウント管理テーブル
 *
 * @author  WuJi
 */

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;
    protected $table = "users";
    protected $primaryKey = "id";
    protected $guarded = ['id','auth_id','union_id',];

    protected $appends = ['auth_id','union_id',];
    protected $fillable = [
        'name', 'email', 'enterprise_admin', 'editPassword','password', 'enterprise_id','id','last_name','first_name','file'
    ];
    protected $dates = ['deleted_at'];

    public function getAuthIdAttribute()
    {
        $auth_id = Auth::id();

        return $auth_id;
    }
    public function getUnionIdAttribute()
    {
        $auth_id = Auth::id();
        $enterpriseId = Account::where('id', $auth_id)->pluck('enterprise_id');
        $union_id = Enterprise::where('id', $enterpriseId)->pluck('user_id')->first();

        return $union_id;
    }
    public function validate()
    {
        $rules = [
            'name' => ['required', 'max:118'],
            'email' => ['required', 'max:191', 'email'],
            'password' => ['required', 'max:20'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

    public function enterprise()
    {
        return $this->hasOne('App\Models\Enterprise','id','enterprise_id');
    }

    public function userProfile()
    {
        return $this->hasOne('App\Models\UserProfile','id','id');
    }

    public function coopEnterprise()
    {
        return $this->hasOne('App\Models\Enterprise','id','coop_enterprise_id');
    }
}
