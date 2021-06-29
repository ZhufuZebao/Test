<?php
/**
 * 運営
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Operator extends Model
{
    protected $table = "operators";
    public $primaryKey = "user_id";

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}