<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/07/22
 * Time: 14:07
 */

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectParticipant extends Model
{
    // 2020-10-22 #2298 Turn on softDeletes
    use softDeletes;
    protected $table = "project_participants";
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne('App\Models\Account', 'id', 'user_id');
    }

    public function project()
    {
        return $this->hasMany('App\Models\Project','id','project_id');
    }
}