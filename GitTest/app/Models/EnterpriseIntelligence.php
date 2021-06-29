<?php


namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnterpriseIntelligence extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes; //Turn on softDeletes
}
