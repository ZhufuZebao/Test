<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/05/08
 * Time: 14:45
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class ProjectLocaleChief extends Model
{
    // 2020-10-22 #2298 Turn on softDeletes
    use softDeletes;
    protected $table = 'project_locale_chief';
    protected $guarded = ['id'];

    public function validate()
    {
        $rules = [
            'name' => ['required', 'max:30'],
            'mail' => ['max:191'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
}
