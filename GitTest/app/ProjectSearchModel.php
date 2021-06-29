<?php
/**
 * 案件を検索する
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use DB;

class ProjectSearchModel
{
    public $id;
    public $date; // TODO
    public $keyword;
    public $location; // TODO

    public function init($params)
    {
        $this->id      = $params['id']      ?? null;
        $this->date    = $params['date']    ?? null;
        $this->keyword = $params['keyword'] ?? null;
        $this->location= $params['location']?? null;
    }

    public function search()
    {
        $q = Project::query();

        if($this->id)
        {
            $q->where('id', $this->id);
        }

        if($this->date)
        {
            // TODO
        }

        if($this->keyword)
        {
            $words  = explode(' ', $this->keyword);
            $params = [];

            foreach($words as $word)
            {
                $q->where(function($query) use ($word){
                    $query->orWhere('name',    'LIKE', "%{$word}%");
                    $query->orWhere('manager', 'LIKE', "%{$word}%");
                    $query->orWhere('author',  'LIKE', "%{$word}%");
                });
            }
        }

        if($this->location)
        {
            // TODO
        }

        return $q;
    }

}
