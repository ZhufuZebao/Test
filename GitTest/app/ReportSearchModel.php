<?php
/**
 * 案件を検索する
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use DB;

class ReportSearchModel
{
    public $keyword;

    public function init($params)
    {
        $this->keyword = $params['keyword'] ?? null;
    }

    public function search()
    {
        $q = Report::query();

        if($this->keyword)
        {
            $words  = explode(' ', $this->keyword);
            $params = [];

            foreach($words as $word)
            {
                $q->where(function($query) use ($word){
                    $query->orWhere('title',    'LIKE', "%{$word}%");
                    $query->orWhere('location', 'LIKE', "%{$word}%");
                    $query->orWhere('note',     'LIKE', "%{$word}%");
                    $query->orWhere('goal',     'LIKE', "%{$word}%");
                    $query->orWhere('tips',     'LIKE', "%{$word}%");
                });
            }
        }

        return $q;
    }

}
