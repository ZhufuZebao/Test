<?php
/**
 * 日報を検索する
 *
 * @author  WuJi
 */

namespace App\Models;

use DB;

class AccountSearchModel
{
    public $keyword;
    public $target;
    public $searchArray;

    public function init($params)
    {
        $this->keyword = $params['keyword'] ?? null;
        $this->target = $params['target'] ?? null;
        $this->searchArray = $params['searchArray'] ?? null;
    }

    public function search()
    {
        $q = Account::query();
        if (strlen($this->keyword) > 0) {
            $words = explode(' ', $this->keyword);
            $params = [];
            foreach ($words as $word) {
                $q->where(function ($query) use ($word) {
                    $query->orWhere('name', 'LIKE', "%{$word}%");
                    $query->orWhere('email', 'LIKE', "%{$word}%");
                });
            }
        }
        return $q;
    }
}
