<?php
/**
 * プロジェクトテーブル
 *
 * @author  liYanlin
 */

namespace App\Models;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    // 2020-10-22 #2298 Turn on softDeletes
    use softDeletes;
    /**
     * storage path of photo
     */
    const IMAGE_PATH = "project/";
    const PHOTO_PATH = "uploads/project/";
    const PHOTO_DEFAULT_PATH = "images/no-image.png";

    protected $guarded = ['id', 'image_path', 'telOut', 'telIn','customer_name','customer'];
    protected $appends = ['image_path'];

    public function customerOffice()
    {
        return $this->belongsToMany('App\Models\CustomerOffice', 'projects_customers','project_id',
            'office_id')->withPivot('office_id','customer_id')->select('customer_offices.id','customer_offices.name','customer_offices.tel','projects_customers.deleted_at');
    }
    public function customer()
    {
        return $this->belongsToMany('App\Models\Customer', 'projects_customers','project_id',
            'customer_id')->withPivot('office_id','customer_id')->select('customers.id','customers.name','projects_customers.deleted_at');
    }
    public function projectLocaleChief()
    {
        return $this->hasMany('App\Models\ProjectLocaleChief', 'project_id', 'id');
    }
    public function projectTradesChief()
    {
        return $this->hasMany('App\Models\ProjectTradesChief', 'project_id', 'id');
    }
    public function projectHospital()
    {
        return $this->hasMany('App\Models\ProjectHospital', 'project_id', 'id');
    }
    public function projectParticipant()
    {
        return $this->hasMany('App\Models\ProjectParticipant', 'project_id', 'id');
    }
    public function participants(){
        return $this->belongsToMany('App\Models\User', 'project_participants');
    }
    public function chatMessage(){
        return $this->hasOne('App\Models\ChatMessage', 'group_id', 'group_id');
    }
    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
    public function userUpdateBy(){
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function partners(){
        return $this->hasMany('App\Models\User', 'enterprise_id', 'enterprise_id');
    }

    public function enterprise()
    {
        return $this->hasOne('App\Models\Enterprise', 'id', 'enterprise_id');
    }

    public function getImagePathAttribute()
    {
        if ($this->subject_image) {
            $image_path = $this->subject_image;
        } else {
            $image_path = Project::PHOTO_DEFAULT_PATH;
        }
        return $image_path;
    }

    public function validate()
    {
        $rules = [
            'place_name' => ['required', 'max:50'],
            //#2790 remove construction_name in project
//            'construction_name' => ['required', 'max:50'],
            //#2790 add field name project_no
            'project_no' => ['max:50'],
            'zip' => ['max:7'],
            'pref' => ['max:4'],
            'town' => ['max:30'],
            'street' => ['max:20'],
            'tel' => ['max:15'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }

    public static function boot()
    {
        parent::boot();
        // 案件を削除したら、現場担当者、工種別責任者、最寄病院、協力会社も削除
        static::deleted(function ($project) {
            error_log(print_r('-----project_deleted-----', true));
            foreach ($project->projectLocaleChief as $localeChief) {
                $localeChief->delete();
            }
            foreach ($project->projectHospital as $hospital) {
                $hospital->delete();
            }
            foreach ($project->projectTradesChief as $tradesChief) {
                $tradesChief->delete();
            }
            foreach ($project->projectParticipant as $participant) {
                $participant->delete();
            }
        });
    }
}
