<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class TaskDepend extends Model
{
    protected $table = "task_depends";
    public $primaryKey = "id";
    
    protected $fillable = [
        'src_id', 'dst_id', 'user_id',
    ];

    public function dst()
    {
        return $this->hasOne('App\Task', 'id', 'dst_id');
    }

    public function src()
    {
        return $this->hasOne('App\Task', 'id', 'src_id');
    }

    /**
     * @return Validator
     */
    public function validate()
    {
        $rules = [
            'src_id' => ['required','exists:tasks,id',"unique:task_depends,src_id,{$this->dst_id}"],
            'dst_id' => ['required','exists:tasks,id'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

    // TO BE REMOVED
    static public function validator($params)
    {
        return Validator::make($params, [
            'src_id'        => ['required','exists:tasks,id','unique'],
            'dst_id'        => ['required','exists:tasks,id'],
        ]);
    }
}

