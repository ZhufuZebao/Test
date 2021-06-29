<?php
/**
 * Created by PhpStorm.
 */

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCustomer extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "projects_customers";

}
