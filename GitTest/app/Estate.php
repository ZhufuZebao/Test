<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class Estate extends Model
{
    protected $table = "estates";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'location_name',
        'project_name',
        'location_zip',
        'location_pref_id',
        'location_town',
        'location_street',
        'location_house',
        'location_tel',
        'renovate_flg',
        'staff_name',
        'staff_position',
        'maintainer_company',
        'maintainer_zip',
        'maintainer_pref_id',
        'maintainer_town',
        'maintainer_street',
        'maintainer_house',
        'maintainer_tel',
        'maintainer_person',
        'maintainer_position',
        'realtor_company',
        'realtor_zip',
        'realtor_pref_id',
        'realtor_town',
        'realtor_street',
        'realtor_house',
        'realtor_tel',
        'realtor_person',
        'realtor_position',
        'land_area',
        'floor_area',
        'floor_level',
        'usage',
        'start_at',
        'finish_at',
        'open_at',
        'contractor_id',
        'note',
        'progress_id',
        'comment',
        'sos_tel',
        'chief',
        'assistant',
        'firehose_name',
        'firehouse_person',
        'firehose_tel',
        'police_name',
        'police_person',
        'police_tel',
        'user_id',
    ];

    public function contractor()
    {
        return $this->hasOne('App\Contractor', 'id', 'contractor_id');
    }

    public function hospitals()
    {
        return $this->hasMany('App\EstateHospital', 'estate_id', 'id');
    }

    public function jobs()
    {
        return $this->hasMany('App\EstateJob', 'estate_id', 'id');
    }

    public function locationPref()
    {
        return $this->hasOne('App\Pref', 'id', 'location_pref_id');
    }

    public function maintainerPref()
    {
        return $this->hasOne('App\Pref', 'id', 'maintainer_pref_id');
    }

    public function progress()
    {
        return $this->hasOne('App\EstateProgress', 'id', 'progress_id');
    }

    public function realtorPref()
    {
        return $this->hasOne('App\Pref', 'id', 'realtor_pref_id');
    }

    public function persons()
    {
        return $this->hasMany('App\CustomerOfficePerson', 'customer_office_id', 'id');
    }

    public function pref()
    {
        return $this->hasOne('App\Pref', 'id', 'pref_id');
    }

    /* public function people()
     * {
     *     return $this->hasMany('App\Customer', 'id', 'customer_id');
     * }*/

    public static function rules()
    {
        return [
            'location_name' => ['required', 'max:256'],
            'project_name' => ['required', 'max:256'],
            'location_zip' => ['required', 'max:256'],
            'location_pref_id' => ['required', 'max:256'],
            'location_town' => ['required', 'max:256'],
            'location_street' => ['required', 'max:256'],
            'location_house' => ['nullable', 'max:256'],
            'location_tel' => ['required', 'max:256'],
            'renovate_flg' => ['required', 'boolean'],

            'land_area' => ['nullable','max:256'],
            'floor_area' => ['nullable','max:256'],
            'floor_level' => ['nullable','max:256'],
            'usage' => ['nullable','max:256'],

            'start_at' => ['nullable','max:256'],
            'finish_at' => ['nullable','max:256'],
            'open_at' => ['nullable','max:256'],

            'contractor_id' => ['nullable','exists:contractors,id'],
            'note' => ['nullable','max:256'],
            'progress_id' => ['nullable','exists:estate_progresses,id'],
            'comment' => ['nullable','max:256'],

            'staff_name' => ['nullable','max:256'],
            'staff_position' => ['nullable','max:256'],
            'staff_tel' => ['nullable','max:256'],
            'staff_email' => ['nullable','max:256', 'email'],

            'maintainer_company' => ['nullable','max:256'],
            'maintainer_zip' => ['nullable','max:256'],
            'maintainer_pref_id' => ['nullable','max:256'],
            'maintainer_town' => ['nullable','max:256'],
            'maintainer_street' => ['nullable','max:256'],
            'maintainer_house' => ['nullable','max:256'],
            'maintainer_tel' => ['nullable','max:256'],
            'maintainer_person' => ['nullable','max:256'],
            'maintainer_position' => ['nullable','max:256'],

            'realtor_company' => ['nullable','max:256'],
            'realtor_zip' => ['nullable','max:256'],
            'realtor_pref_id' => ['nullable','max:256'],
            'realtor_town' => ['nullable','max:256'],
            'realtor_street' => ['nullable','max:256'],
            'realtor_house' => ['nullable','max:256'],
            'realtor_tel' => ['nullable','max:256'],
            'realtor_person' => ['nullable','max:256'],
            'realtor_position' => ['nullable','max:256'],

            'sos_tel' => ['nullable','max:256'],
            'chief' => ['nullable','max:256'],
            'assistant' => ['nullable','max:256'],

            'firehose_name' => ['nullable','max:256'],
            'firehouse_person' => ['nullable','max:256'],
            'firehose_tel' => ['nullable','max:256'],

            'police_name' => ['nullable','max:256'],
            'police_person' => ['nullable','max:256'],
            'police_tel' => ['nullable','max:256'],
        ];

    }

    public function validate()
    {
        $rules = self::rules();

        return Validator::make($this->getAttributes(), $rules);
    }

}
