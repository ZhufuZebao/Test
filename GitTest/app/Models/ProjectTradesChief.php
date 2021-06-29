<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/05/08
 * Time: 15:40
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class ProjectTradesChief extends Model
{
    // 2020-10-22 #2298 Turn on softDeletes
    use softDeletes;
    protected $table = 'project_trades_chief';
    protected $guarded = ['id'];

    public function validate()
    {
        $rules = [
            'trades_type' => ['max:1'],
            'name' => ['required', 'max:30'],
            'tel' => ['max:15'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
}