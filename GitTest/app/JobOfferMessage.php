<?php
/**
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class JobOfferMessage extends Model
{
    protected $table   = "job_offer_messages";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'content',
    ];

    public function offer()
    {
        return $this->hasOne(\App\JobOffer::class, 'vacancy_id', 'vacancy_id');
    }

    public function sender()
    {
        return $this->hasOne(\App\User::class, 'id', 'sender_id');
    }

    public function vacancy()
    {
        return $this->hasOne(\App\JobVacancy::class, 'id', 'vacancy_id');
    }

    public function validate()
    {
        $rules = [
            'content'    => ['required', 'string', 'max:2048'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
