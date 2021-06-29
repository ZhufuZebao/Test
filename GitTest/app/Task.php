<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class Task extends Model
{
    protected $table = "tasks";
    public $primaryKey = "id";
    
    protected $fillable = [
            'name', 'st_date', 'ed_date', 'note', 'parent_id', 'user_id',
    ];

    public function children()
    {
        return $this->hasMany(\App\Task::class, 'parent_id', 'id')
                    ->orderByRaw('weight DESC, id ASC');
    }

    public function depends()
    {
        return $this->hasMany(\App\TaskDepend::class, 'dst_id', 'id');
    }

    public function connects()
    {
        return $this->hasMany(\App\TaskDepend::class, 'src_id', 'id');
    }

    public function parent()
    {
        return $this->hasOne(\App\Task::class, 'id', 'parent_id');
    }

    public function validate()
    {
        
        $rules = [
            'name'          => ['required', 'max:64'],
            'st_date'       => ['required', 'before_or_equal:ed_date'],
            'ed_date'       => ['required', 'after_or_equal:st_date'],
            'parent_id'     => ['nullable', 'exists:tasks,id'],
            'note'          => ['nullable', 'string', 'max:128'],
        ];

        if($p = $this->parent)
        {
            $rules['st_date'][] = 'after_or_equal:' . $p->st_date;
            $rules['ed_date'][] = 'before_or_equal:'. $p->ed_date;
        }

        return Validator::make($this->getAttributes(), $rules);
    }

}

