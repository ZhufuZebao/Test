<?php
/**
 * UserSkill - 職人ひとりずつの技能
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Validator;

class UserSkill extends Model
{
    protected $table   = "user_skills";
    public $primaryKey = "id";

    protected $fillable = [
        'user_id',
        'skill_id',
    ];

    public function validate()
    {
        $rules = [
            'user_id'    => ['required', 'exists:users,id'],
            'skill_id'   => ['required', 'exists:skills,id'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
