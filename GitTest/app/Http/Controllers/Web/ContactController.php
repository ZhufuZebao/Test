<?php
/**
 * Created by PhpStorm.
 * User: P0128147
 * Date: 2020/06/02
 * Time: 15:02
 */

namespace App\Http\Controllers\Web;

use App\Http\Facades\Common;
use App\Models\Account;
use App\Models\ChatGroup;
use App\Models\ChatList;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactController extends \App\Http\Controllers\Controller
{

    /**
     * 連絡先 社内一覧 dataを取得
     * @return array
     */
    public function getContactList()
    {
        // 該当事業者の「会社ID」を取得
        $enterpriseId = Auth::user()->enterprise_id;

        //協力会社の場合、連絡先の社内は空array
        if ($enterpriseId>0) {
            //
        } else {
            return [];
        }

        $accounts = Account::where('enterprise_id', $enterpriseId)
            ->with('enterprise')
            ->get(['id', 'name', 'enterprise_id', 'coop_enterprise_id',
                   'email','file','created_at'])
            ->toArray();

        foreach ($accounts as $k => $item)
        {
            //データから自分を削除する
            if ($item['id'] == Auth::id()) {
                array_splice($accounts, $k, 1);
            }
        }

        //date formart
        foreach ($accounts as $k=>$perItem)
        {
            $accounts[$k]['created_at'] = date('Y/m/d', strtotime($perItem['created_at']));
        }

        return $accounts;
    }

    /**
     * 連絡先 社内一覧 dataを検索
     * @param Request $request
     */
    public function contactSearch(Request $request)
    {
        $word = Common::escapeDBSelectKeyword($request->input('searchName'));

        // 該当事業者の「会社ID」を取得
        $enterpriseId = Auth::user()->enterprise_id;

        //協力会社の場合、連絡先の社内は空array
        if ($enterpriseId>0) {
            //
        } else {
            return [];
        }

        //検索
        $q = Account::where('enterprise_id', $enterpriseId)->with('enterprise');
        if (strlen($word) > 0) {
            $q->where(function ($q1) use ($word) {
                $q1->whereHas('enterprise', function ($query) use ($word) {
                    $query->where('name', 'LIKE', "%{$word}%");
                });
                $q1->orWhere('name', 'LIKE', "%{$word}%");
            });
        }

        return $q->get(['id', 'name', 'enterprise_id', 'coop_enterprise_id',
                        'email','file','created_at']);
    }

    /**
     * 連絡先 社内一覧 dataを並べ替え
     * @param Request $request
     */
    public function contactSort(Request $request)
    {
        $searchArray = $request->input('searchName');
        $sort = $request->get('sort');
        $order = $request->get('order');

        if ($sort == 'name'){
            foreach ($searchArray as $item) {
                $paytime[] = $item['name'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, SORT_LOCALE_STRING,$searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, SORT_LOCALE_STRING,$searchArray);
            }
        } elseif ($sort == 'enterprise'){
            foreach ($searchArray as $item) {
                //組織 会社名or協力会社名を取得
                if ($item['enterprise']['name']) {
                    //会社名
                    $paytime[] = $item['enterprise']['name'];
                } else {
                    //NULL
                    $paytime[] = null;
                }
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, SORT_LOCALE_STRING,$searchArray);
            } else {
                array_multisort($paytime, SORT_DESC,SORT_LOCALE_STRING, $searchArray);
            }
        } elseif ($sort == 'date'){
            foreach ($searchArray as $item) {
                $paytime[] = $item['created_at'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC,SORT_LOCALE_STRING, $searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, SORT_LOCALE_STRING,$searchArray);
            }
        }
        return $searchArray;

    }

    /**
     * 連絡先 社内一覧 チャットリンク
     * @param Request $request
     */
    public function contactChatLink(Request $request)
    {
        $contactUserId = $request->input('contactUserId');

        $chatGroup = ChatGroup::query()->where('user_id', $contactUserId)
            ->where('admin', 1)
            ->get();
        $chatGroupAuth = ChatGroup::query()->where('user_id', Auth::id())
            ->where('admin', 1)
            ->get();

        //チャットリンクを取得
        $groupIdsTempArr = [];
        foreach ($chatGroup as $item) {
            foreach ($chatGroupAuth as $itemAuth) {
                if ($item->group_id == $itemAuth->group_id) {
                    $groupIdsTempArr[] = $item->group_id;
                }
            }
        }
        //1 vs 1 chatgroup filter
        $groupModels = DB::table('groups')->whereIn('id',$groupIdsTempArr)->where('kind',1)->pluck('id');
        if (count($groupModels)>0){
            $arr = array("user_id" => $contactUserId, "group_id" => $groupModels[0]);
            return $arr;
        } else {
            //新規の場合
            return $this->createNewGroup($contactUserId);
        }
    }

    private function createNewGroup($inviteUserId){
        try {
            DB::beginTransaction();

            $group = new Group();
            $group->name = Auth::user()->name;
            $group->kind = 1;
            $group->save();

            $chatMineGroup = new ChatGroup();
            $chatMineGroup->user_id = $inviteUserId;
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->admin = 1;
            $chatMineGroup->save();

            $chatMineGroup = new ChatGroup();
            $chatMineGroup->user_id = Auth::id();
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->admin = 1;
            $chatMineGroup->save();

            $chatMineGroup = new ChatList();
            $chatMineGroup->owner_id = Auth::id();
            $chatMineGroup->user_id = $inviteUserId;
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->save();

            $chatMineGroup = new ChatList();
            $chatMineGroup->owner_id = $inviteUserId;
            $chatMineGroup->user_id = Auth::id();
            $chatMineGroup->group_id = $group->id;
            $chatMineGroup->save();

            DB::commit();

            $arr = array("user_id" => $inviteUserId, "group_id" => $group->id);
            return $arr;
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            Log::error($e);
        }
    }

}