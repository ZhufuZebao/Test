<?php
/**
 * Created by goki
 */

namespace App\Models;

use DB;
use Illuminate\Support\Facades\Auth;

class FriendSearchModel
{
    public $searchArray;

    public function init($params)
    {
        $this->searchArray = $params['searchArray'] ?? null;
    }

    public function search($word)
    {
        $q = ChatContact::query();
        $usersArr = User::where('enterprise_id', Auth::user()->enterprise_id)->whereNotNull('enterprise_id')->withTrashed()->get('id');
        $res = [];
        foreach ($usersArr as $user) {
            $res[] = $user['id'];
        }
        $q->whereIn('from_user_id', $res);
        if (strlen($word) > 0) {
            $q->where(function ($q1) use ($word) {
                $q1->whereHas('accounts', function ($query) use ($word) {
                    $query->withTrashed();
                    $query->where('name', 'LIKE', "%{$word}%");
                    $query->where('id', '!=', Auth::id());
                });
                $q1->orWhereHas('accounts',function ($query2) use ($word) {
                    $query2->where('email', 'LIKE', "%{$word}%");
                    $query2->where('contact_agree', 1);
                });
            });
        }
        return $q;
    }

    public function searchInvite($word)
    {
        $userId = Auth::id();
        $q = ChatContact::query();
        $q->where(function ($q2) use ($userId){
            $q2->where('to_user_id', $userId)->orWhere('email',Auth::user()->email);
        });
        if (strlen($word) > 0) {
            $q->where(function ($q1) use ($word) {
                $q1->whereHas('accountsInvite', function ($query) use ($word) {
                    $query->withTrashed();
                    $query->where('name', 'LIKE', "%{$word}%");
                    $query->where('id', '!=', Auth::id());
                });
                $q1->orWhereHas('accountsInvite', function ($query) use ($word) {
                    $query->where('email', 'LIKE', "%{$word}%");
                    $query->where('contact_agree', 1);
                });
            });
        }
        return $q;
    }

}
