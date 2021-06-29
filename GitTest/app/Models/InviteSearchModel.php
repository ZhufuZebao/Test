<?php
/**
 * Created by PhpStorm.
 * User: P0128147
 * Date: 2019/07/08
 * Time: 10:26
 */

namespace App\Models;

use DB;
use Illuminate\Support\Facades\Auth;

class InviteSearchModel
{
    public $searchArray;

    public function init($params)
    {
        $this->searchArray = $params['searchArray'] ?? null;
    }

    public function search($word)
    {
        $q = EnterpriseParticipant::query();
        if (strlen($word) > 0) {
            $q->where(function($q1) use ($word){
                $q1->whereHas('account', function ($query) use ($word) {
                    $query->where('users.name', 'like', "%$word%");
                })->where('enterprise_participants.enterprise_id',Auth::user()->enterprise_id)
                ->orWhereHas('account.enterprise', function ($query) use ($word) {
                    $query->where('enterprises.name', 'like', "%$word%");
                })->orWhereHas('account.coopEnterprise',function ($query) use ($word) {
                        $query->where('enterprises.name', 'like', "%$word%");
                });
            })
            ->where('enterprise_participants.enterprise_id',Auth::user()->enterprise_id);
        }
        return $q;
    }

}