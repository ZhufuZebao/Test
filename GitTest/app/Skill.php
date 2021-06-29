<?php
/**
 * Skill - 職人の技能あれこれ
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Validator;

class Skill extends Model
{
    protected $table   = "skills";
    public $primaryKey = "id";

    protected $casts = [
    ];

    protected $fillable = [
        'name',
    ];

    public function validate()
    {
        $rules = [
            'name'    => ['required', 'string', 'max:256', "unique,skills,name,{$this->id}"],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
