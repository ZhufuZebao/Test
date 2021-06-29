<?php
/**
 * @author  liYanlin
 */

namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enterprise extends Model
{
    use SoftDeletes;
    protected $table = "enterprises";
    public $primaryKey = "id";

    protected $fillable = [
        'name', 'zip', 'pref', 'town', 'street', 'house', 'tel', 'user_id',
    ];
    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function contractor()
    {
        return $this->hasOne('App\Models\Contractor', 'enterprise_id', 'id');
    }

    public function validate()
    {
        $rules = [
            'name' => ['required', 'max:50'],
            'zip' => ['required','digits_between:1,7','numeric'],
            'pref' => ['required', 'max:4'],
            'town' => ['required', 'max:30'],
            'street' => ['required', 'max:20'],
            'house' => ['max:70'],
            'tel' => ['required','digits_between:1,15','numeric'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }


    public function detailValidate()
    {
        $rules = [
            'name' => ['required', 'between:0,50'],
            'zip' => ['required', 'numeric','digits_between:1,7'],
            'pref' => ['required', 'max:20'],
            'town' => ['required', 'max:30'],
            'street' => ['required', 'max:20'],
            'tel' => ['required','digits_between:1,15','numeric'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
}
