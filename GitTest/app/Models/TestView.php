<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
/**
 * Created by PhpStorm.
 * User: P0168873
 * Date: 2021/6/22
 * Time: 14:20
 */
class TestView extends Model{
    protected $table = 'testview';
    protected $primaryKey = 'id';
    protected $fillable = ['user','sex','like'];
    public $timestamps = false;
}