<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class Report extends Model
{
    // 2020-11-4 #2298 Turn on softDeletes
    use SoftDeletes;
    protected $table = "reports";
    public $primaryKey = "id";

    protected $fillable = [
        'project_id',
        'report_date',
        'user_id',
        'location',
        'type',
        'file_path',
        'file_date',
        'created_at',
    ];

    public function ReportFile()
    {
        return $this->hasMany('App\Models\ReportFile', 'report_id', 'id');
    }

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function validate()
    {
        $rules = [
            'location' => ['nullable', 'max:1'],
            'type' => ['nullable', 'max:256'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
}
