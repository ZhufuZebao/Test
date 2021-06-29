<?php
/**
 *
 * @author  Reiko Mori
 */
namespace App;

use DB;

class EstateProgress extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "estate_progresses";

    public $primaryKey = "id";

    public $fillable = [
        'name'
    ];

}
