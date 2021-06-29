<?php
/**
 * 求人を検索する
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

use Illuminate\Support\Facades\Auth;

class JobOfferMessageSearchModel extends JobOfferMessage
{
    public $filter; // unread | inbox | outbox
    public $keyword;

    public $fillable = [
        'keyword',
    ];

    public function init($params)
    {
        $this->keyword = $params['keyword'] ?? null;
    }

    public function search()
    {
        $q = self::query()->orderBy('id','DESC');
        $userId = Auth::id();

        if($this->keyword)
        {
            $words  = explode(' ', $this->keyword);
            $params = [];

            foreach($words as $word)
            {
                /*
                 * content に部分一致すればOK
                 */
                $q->orWhere('content', 'LIKE', "%{$word}%");
            }
        }

        if('outbox' == $this->filter)
        {
            $q->where('sender_id', Auth::id());
        }
        elseif($this->filter) // inbox
        {
            $q->where('sender_id', '<>', Auth::id());

            // 自分が応募した案件
            $q->orWhereHas('offer', function($q) {
                $q->where('worker_id', Auth::id());
            });

            if('unread' == $this->filter)
            {
                $q->whereNull('read_at');
            }
        }

        return $q;
    }

}
