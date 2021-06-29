<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class ReportFile extends Model
{
    // 2020-11-4 #2298 Turn on softDeletes
    use SoftDeletes;
    protected $table = "report_files";
    public $primaryKey = "id";
    protected $fillable = [
        'report_id',
        'report_path',
        'user_id',
        'comment',
        'created_at',
    ];

    public function report()
    {
        return $this->belongsTo('App\Models\Report', 'report_id', 'id');
    }
}
