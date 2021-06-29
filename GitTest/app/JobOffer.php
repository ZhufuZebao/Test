<?php
/**
 * JobOffer - 求人案件
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Validator;

class JobOffer extends Model
{
    const PENDING = -1;
    const BYE     =  0;
    const HIRED   =  1;

    protected $table   = "job_offers";
    public $primaryKey = "id";

    protected $fillable = [
        'content',
    ];

    /**
     * 同じ仕事に応募している、他の職人
     */
    public function competitors()
    {
        return $this->hasMany(self::class, 'vacancy_id', 'vacancy_id')
                    ->where('id', '!=', $this->id);
    }

    /**
     * 職人を雇用する
     * @var $exclusive bool - 他の候補者を不採用にする|しない
     * @return bool
     */
    public function hire($exclusive = false)
    {
        $this->hired = self::HIRED;

        if($exclusive)
        {
            $this->competitors()
                 ->where('hired', '!=', self::HIRED)
                 ->update(['hired' => self::BYE]);
        }

        return $this->update();
    }

    public function messages()
    {
        return $this->hasMany(\App\JobOfferMessage::class, 'vacancy_id', 'vacancy_id')
                    ->where('sender_id','=',$this->worker_id)
                    ->orWhere('receiver_id','=',$this->worker_id);
    }

    /**
     * 担当者：募集した人、または指名した人
     */
    public function staff()
    {
        return $this->hasOne(User::class, 'staff_id', 'id');
    }

    public function vacancy()
    {
        return $this->hasOne(\App\JobVacancy::class, 'id', 'vacancy_id');
    }

    public function validate()
    {
        $rules = [
            'worker_id'    => ['required', 'exists:users,id'],
            'created_by'   => ['required', 'exists:users,id'],
            'vacancy_id'   => ['required', 'string', 'max:2048'],
            'status_id'    => ['required', 'exists:job_vacancy_statuses,id'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

    /**
     * 職人：応募した人、または指名された人
     */
    public function worker()
    {
        return $this->hasOne(User::class, 'id', 'worker_id');
    }

}
