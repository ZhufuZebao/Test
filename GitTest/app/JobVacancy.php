<?php
/**
 * JobVacancy - 求人情報マスター
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Validator;

class JobVacancy extends Model
{
    protected $table   = "job_vacancies";
    public $primaryKey = "id";

    protected $fillable = [
        'name',
        'description',
        'contractor_id',
        'skill_id',
        'st_date',
        'ed_date',
        'status_id',
    ];

    public static function attributes()
    {
        return [
            'name'  => "見出し",
        ];
    }

    /**
     * 求人を停止する
     * @return void
     */
    public function close()
    {
        $this->status_id = \App\JobVacancyStatus::CLOSED;
    }

    public function contractor()
    {
        return $this->hasOne(\App\Contractor::class, 'id', 'contractor_id');
    }

    /**
     * このユーザが閲覧できるかどうか
     * @return bool
     */
    public function isAccesible($user_id)
    {
        if($user_id == $this->user_id) // 自分が作成した
        {
            return true;
        }

        $q = $this->offers()
                  ->where('worker_id', $user_id);
                      
        if($q->exists()) // 自分が応募した
        {
            return true;
        }

        if(false == $this->isActive()) // 未公開・期限切れ
        {
            return false;
        }

        return true;
    }
    /**
     * 現在公開中かどうか
     * @return bool
     */
    public function isActive()
    {
        if(false == $this->status->isOpen())
        {
            return false;
        }

        $now = time();
        if(strtotime($this->st_date) > $now ||
           strtotime($this->ed_date) < $now )
        {
            return false;
        }

        return true;
    }

    public function messages()
    {
        return $this->hasMany(\App\JobOfferMessage::class,'vacancy_id','id');
    }

    public function offers()
    {
        return $this->hasMany(\App\JobOffer::class, 'vacancy_id', 'id');
    }

    /**
     * 求人を公開する
     * @return void
     */
    public function open()
    {
        $this->status_id = \App\JobVacancyStatus::PUBLISH;
    }

    public static function rules()
    {
        return [
            'name'          => ['required', 'string', 'max:256'],
            'description'   => ['required', 'string', 'max:2048'],
            'contractor_id' => ['required', 'exists:contractors,id'],
            'skill_id'      => ['required', 'exists:skills,id'],
            'st_date'       => ['nullable'],
            'ed_date'       => ['nullable'],
            'status_id'     => ['required', 'exists:job_vacancy_statuses,id'],
        ];
    }

    public function offer()
    {
        return $this->hasMany(\App\JobOffer::class, 'vacancy_id', 'id');
    }

    public function skill()
    {
        return $this->hasOne(\App\Skill::class, 'id', 'skill_id');
    }

    public function status()
    {
        return $this->hasOne(\App\JobVacancyStatus::class, 'id', 'status_id');
    }

    public function user()
    {
        return $this->hasOne(\App\User::class, 'id', 'user_id');
    }

    public function validate()
    {
        return Validator::make($this->getAttributes(), self::rules(), [], self::attributes());
    }

}
