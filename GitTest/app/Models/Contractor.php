<?php
/**
 * Created by PhpStorm.
 * User: P0128147
 * Date: 2020/05/22
 * Time: 14:04
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table = "contractors";
    protected $primaryKey = "id";

    public function enterprise()
    {
        return $this->hasOne('App\Models\Enterprise','id','enterprise_id');
    }

}