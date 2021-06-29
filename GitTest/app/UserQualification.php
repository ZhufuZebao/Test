<?php
/**
 * UserQualification - 職人ひとりずつの資格
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Validator;

class UserQualification extends Model
{
    protected $table   = "user_qualifications";
    public $primaryKey = "id";

    protected $fillable = [
        'user_id',
        'qualification_id',
    ];

    public function validate()
    {
        $rules = [
            'user_id'         => ['required', 'exists:users,id'],
            'qualification_id'=> ['required', 'exists:qualifications,id'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
