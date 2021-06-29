<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class EstateJob extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "estate_jobs";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'estate_id',
        'job_type_id',
        'chief',
        'chief_tel',
    ];

    public function estate()
    {
        return $this->hasOne('App\Estate', 'id', 'estate_id');
    }

    public function jobType()
    {
        //return $this->hasOne('App\JobType', 'id', 'job_type_id');
    }

    public function validate()
    {
        $rules = [
            'estate_id'   => ['required', 'exists:estates,id'],
            'job_type_id' => ['required', 'exists:job_type,id'],
            'chief'       => ['required', 'max:256'],
            'chief_tel'   => ['required', 'max:256'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
