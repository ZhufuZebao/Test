<?php
/**
 * Created by PhpStorm.
 * User: P0123443
 * Date: 2019/07/05
 * Time: 9:34
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class Group extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes;////Turn on softDeletes
    protected $table = "groups";

    protected $fillable = [
        'name', 'kind', 'file', 'parent_id', 'description'
    ];

    public function users()
    {
        //#2298 chatgroupsデータの削除をフィルタリングする
        return $this->belongsToMany('App\Models\User', 'chatgroups', 'group_id', 'user_id')
            ->withPivot('admin')->wherePivot('deleted_at',NUll);
    }

    public function chatGroup()
    {
        return $this->hasMany('App\Models\ChatGroup', 'group_id', 'id');
    }

    public function mine()
    {
        return $this->hasOne('App\Models\Group', 'id', 'parent_id');
    }

    public function groupValidate()
    {
        $rules = [
            'name' => ['required','max:191'],
            'kind' => ['required','between:0,1','numeric'],
            'parent_id' => ['numeric','digits_between:1,10'],
            'description' => ['max:500'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }

    public function project(){
        return $this->hasOne('App\Models\Project', 'group_id', 'id');
    }
}
