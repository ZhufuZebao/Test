<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TestLearn extends Model{
    protected $table = 'testlearn';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public function getDataFormat(){
        return time();
    }
}