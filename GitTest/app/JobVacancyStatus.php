<?php
/**
 * JobVacancyStatus - 求人情報の状態
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Validator;

class JobVacancyStatus extends Model
{
    const CLOSED   = 1;
    const TEMPORAL = 2;
    const PUBLISH  = 3;

    protected $table   = "job_vacancy_statuses";
    public $primaryKey = "id";

    protected $fillable = [
        'name',
    ];

    public function close()
    {
        return self::CLOSED;
    }

    public function isClosed()
    {
        return (self::CLOSED == $this->id);
    }

    public function isDraft()
    {
        return (self::TEMPORAL == $this->id);
    }

    public function isOpen()
    {
        return (self::PUBLISH == $this->id);
    }

    public function validate()
    {
        $rules = [
            'name' => ['required', 'string', 'max:256'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
