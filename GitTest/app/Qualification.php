<?php
/**
 * Qualification - 職人の資格あれこれ
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Qualification extends Model
{
    protected $fillable = [
        'name',
        'skill_id',
    ];

    /**
     * 技能分野
     */
    public function skill()
    {
        return $this->hasOne(\App\Skill::class, 'id', 'skill_id');
    }

    public function validate()
    {
        $rules = [
            'name'     => ['required', 'string', "unique:qualifications,name,{$this->id}"],
            'skill_id' => ['required', 'exists:skills,id'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
