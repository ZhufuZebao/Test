<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class Scheme extends Model
{
    protected $table = "schemes";
    public $primaryKey = "id";

    protected $attributes = [
        'note' => '',
    ];

    protected $fillable = [
        'parent_id', 'name', 'note', 'st_date', 'ed_date', 'user_id',
    ];

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
                    ->orderByRaw('weight DESC, id ASC');
    }

    public function contractor()
    {
        return $this->hasOne(\App\Contractor::class, 'id', 'contractor_id');
    }

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    public function project()
    {
        return $this->hasOne(\App\Project::class, 'id', 'project_id');
    }

    public function validate()
    {
        $rules = [
            'parent_id'     => ['nullable', 'integer'],
            'name'          => ['required', 'max:64'],
            'st_date'       => ['required', 'before_or_equal:ed_date'],
            'ed_date'       => ['required', 'after_or_equal:st_date'],
            'note'          => ['nullable', 'string', 'max:128'],
            'contractor_id' => ['nullable', 'exists:contractors,id'],
            'user_id'       => ['nullable', 'exists:users,id'],
        ];

        if($p = $this->parent)
        {
            $rules['st_date'][] = 'after_or_equal:' . $p->st_date;
            $rules['ed_date'][] = 'before_or_equal:'. $p->ed_date;
        }

        return Validator::make($this->getAttributes(), $rules);
    }

}

