<?php
/**
 * @author goki
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatTaskCharge extends Model
{
    // 2020-10-22 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chattaskcharges";
    protected $primaryKey = "id";
    protected $guarded = 'id';

    protected $fillable = [
        'id','task_id', 'user_id', 'created_at',
    ];

    public function chatTask()
    {
        return $this->belongsTo('App\Models\ChatTask', 'task_id', 'id');
    }
    public function users()
    {
        return $this->hasMany('App\Models\User', 'id', 'user_id');

    }
}
