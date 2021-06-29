<?php
/**
 * 求人を検索する
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

/**
 * 職人が仕事を探すためのクエリーを組み立てるクラス
 */
class JobSearchModel extends JobVacancy
{
    public $keyword;
    public $posted = false;//応募した仕事
    public $hired  = false;//契約した仕事

    public $fillable = [
        'keyword',
        'skill_id',
        'status_id',
    ];

    public function init($params)
    {
        $this->id      = $params['id']      ?? null;
        $this->name    = $params['name']    ?? null;
        $this->keyword = $params['keyword'] ?? null;
        $this->posted  = $params['posted']  ?? false;
        $this->hired   = $params['hired']   ?? false;
    }

    public function search()
    {
        $q = self::query();

        if($this->keyword)
        {
            $words  = explode(' ', $this->keyword);
            $params = [];

            foreach($words as $word)
            {
                /*
                 * name または description に部分一致すればOK
                 */
                $q->orWhere('name',        'LIKE', "%{$word}%");
                $q->orWhere('description', 'LIKE', "%{$word}%");
            }
        }

        if($this->future)
        {
            $q->where('st_date','>','NOW()');
        }
        elseif($this->past)
        {
            $q->where('ed_date','<','NOW()');
        }

        if($this->skill_id)
        {
            $q->whereIn('skill_id',$this->skill_id);
        }

        if($this->hired)
        {
            $q->whereHas('offer', function($q) {
                $q->where('worker_id', \Illuminate\Support\Facades\Auth::id());
                $q->where('hired', 1);
                $q->where('accepted', 1);
            });
        }
        elseif($this->posted)
        {
            $q->whereHas('offer', function($q) {
                $q->where('worker_id', \Illuminate\Support\Facades\Auth::id());
                $q->whereNull('accepted');
            });
        }
        else
        {
            if($this->status_id)
            {
                $q->where('status_id', $this->status_id);
            }
            else
            {
                $q->where('status_id', '=', \App\JobVacancyStatus::PUBLISH);
            }
        }

        if($this->hired)
        {
            $q->whereHas('offer', function($q) {
                $q->where('hired',\App\JobOffer::HIRED); 
            });
        }

        return $q->orderBy('id','desc');
    }

}
