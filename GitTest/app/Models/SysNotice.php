<?php
/**
 * アカウント管理テーブル
 *
 * @author  WuJi
 */

namespace App\Models;

use Illuminate\Support\Facades\Validator;

class SysNotice extends Model
{
    protected $table = "sys_notices";
    protected $primaryKey = "id";
    protected $fillable = [
        'st_date','ed_date', 'title','content'
    ];
    public function validate()
    {
        $rules = [
            'st_date'=> ['required','date'],
            'ed_date'=> ['required','date'],
            'title' => ['required', 'max:60'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
}
