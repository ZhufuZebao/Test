<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class EstateHospital extends Model
{
    protected $table = "estate_hospitals";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'estate_id',
        'name',
        'tel',
    ];

    public function estate()
    {
        return $this->hasOne('App\Estate', 'id', 'estate_id');
    }

    public function validate()
    {
        $rules = [
            'estate_id' => ['required', 'exists:estates,id'],
            'name' => ['required', 'max:256'],
            'tel' => ['required', 'max:256'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
