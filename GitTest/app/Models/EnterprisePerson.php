<?php


namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnterprisePerson extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "enterprise_persons";
    public $primaryKey = "id";
    protected $guarded = ['id'];
}
