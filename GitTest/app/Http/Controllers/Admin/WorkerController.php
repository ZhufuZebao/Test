<?php
/**
 * Created by goki
 */

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiHelper;
use App\Http\Services\FirebaseService;
use App\Mail\ContactFriend;
use App\Mail\ContactInvite;
use App\Models\ChatLastRead;
use App\Models\ChatList;
use App\Models\ChatMessage;
use App\Models\Dashboard;
use App\Models\EnterpriseParticipant;
use App\Models\User;
use App\Models\FriendSearchModel;
use App\Models\ChatContact;
use App\Models\ChatGroup;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\UserCategories;

class WorkerController extends \App\Http\Controllers\Controller
{
    /**
     * 仲間 一覧
     * @param Request $request
     * @return array
     */
    public function getList(Request $request){
        $page = $request->input('page');
        $pageSize = $request->input('pageSize', 10);
        $order = $request->input('order','asc');
        $sortCol = $request->input('sortCol', 'name');
        $KeyName = $request->input('searchName');
        $offset = ($page - 1) * $pageSize;
        $model =User::where('worker',1);
        $count=$model->whereRaw('(first_name like ? or last_name like ?)', ['%'.$KeyName.'%', '%'.$KeyName.'%'])->count();
        $res = $model->whereRaw('(first_name like ? or last_name like ?)', ['%'.$KeyName.'%', '%'.$KeyName.'%'])->orderBy($sortCol, $order)->offset($offset)->limit($pageSize)->get()->toArray();
        $from = ($page - 1) * $pageSize + 1;
        $to = ($page - 1) * $pageSize + count($res);
        foreach ($res as $i=>$item){
            $res[$i]['created_at'] = date('Y/m/d H:i:s',strtotime($item['created_at']));
            $res[$i]['updated_at'] = date('Y/m/d H:i:s',strtotime($item['updated_at']));
            $res[$i]['last_date'] = date('Y/m/d H:i:s',strtotime($item['last_date']));
            if ($res[$i]['last_date'] < $res[$i]['created_at']){
                $res[$i]['last_date'] = $res[$i]['created_at'];
            }
        }
        $result['workers'] = $res;
        $result['current_page'] = $page;
        $result['total'] = $count;
        $result['from'] = $from;
        $result['to'] = $to;
        return $result;
    }

//ブロックする/　ブロック解除
    public function block(Request $request){
        $id = $request->post('id');
        $block_message = $request->post('block_msg');
        $friend= User::query()->where('id', '=', $id)->first();
        if ($friend->block == '0'){
            $friend->block = '1';
            $friend->block_message = $block_message;
        }else{
            $friend->block = '0';
            $friend->block_message = null;
        }
        $friend->save();
        return 'success';
    }
    /**
     * 仲間DetailInformation
     */
    public function workerDetail(Request $request)
    {
        $id = $request->post('userId');
        $sql = <<<EOF
select  u.*, p.name as pref_name, a.city, ifnull(TIMESTAMPDIFF(YEAR, `birth`, CURDATE()), 0) AS age
from    users u
        left join prefs p
            on ifnull(u.pref, 0) = p.id
        left join mst_address_mini a
            on ifnull(u.addr_code, 0) = a.officialCode

where   u.id = ?
EOF;

        $data = DB::select($sql, [
            $id
        ]);

        $sql = <<<EOF
select  wa.id as workarea_id, wa.name as workarea_name, wp.id as workplace_id, wp.name as workplace_name,block,block_message,file,last_name,first_name
from    users u
        left join user_workareas uw
            left join workareas wa
                on uw.area_id = wa.id
            left join workplaces wp
                on uw.place_id = wp.id
            on u.id = uw.user_id
where   u.id = ?
AND u.deleted_at IS NULL
AND uw.deleted_at IS NULL
EOF;
        $areas = DB::select($sql, [
            $id
        ]);
        //print_r($areas);

        $tmp = "";
        if (is_array($areas)) {
            foreach ($areas as $area) {
                if ($area->workarea_name) {
                    $tmp .= $area->workarea_name . '　';
                }
                if ($area->workplace_name) {
                    $tmp .= $area->workplace_name . '　';
                }
            }
        }
        $data[0]->workarea = $tmp;
        $data[0]->workareaArray = $areas;

        // 分野取得
        $data[0]->categories = UserCategories::getUserCategories($id);

        return $data;
    }

    /**
     * 仲間 検索
     * @param Request $request
     * @return array
     */
    public function search(Request $request)
    {
        $query = new FriendSearchModel();
        $searchArray = $request->input('searchName');
        if (!$searchArray) {
            return $this->getList($request);
        }
        $models = $query->search($searchArray)
            ->with(['accounts', 'userworkareas.workareas', 'userworkareas.workplaces'])->get();
        $modelsInvite = $query->searchInvite($searchArray)
            ->with(['accountsInvite', 'userworkareasInvite.workareas', 'userworkareasInvite.workplaces'])->get()->toArray();
        for ($i = 0; $i < count($modelsInvite); $i++) {
            $modelsInvite[$i]['accounts'] = $modelsInvite[$i]['accounts_invite'];
            $modelsInvite[$i]['userworkareas'] = $modelsInvite[$i]['userworkareas_invite'];
            $modelsInvite[$i]['email'] = $modelsInvite[$i]['accounts_invite']['email'];
        }
        return array_merge($models->toArray(), $modelsInvite);
    }
    private function delCommonEnterprise($idRes,$groupArr){
        //協力会社の関係，私たちは会社
        $partner = [];
        $enterpriseParticipant = EnterpriseParticipant::where('enterprise_id',Auth::user()->enterprise_id)->whereIn('user_id',$idRes)
            ->where('agree',1)->get('user_id')->toArray();
        foreach ($enterpriseParticipant as $item){
            $partner[] = $item['user_id'];
        }
        if (count($partner)){
            $chatGroup = ChatGroup::whereHas('user',function ($q){
                $q->where('enterprise_id',Auth::user()->enterprise_id);
            })->whereHas('mine',function ($q) use($partner){
                $q->whereIn('user_id',$partner);
            })->get('group_id')->toArray();
            foreach ($chatGroup as $item){
                $groupArr[] = $item['group_id'];
            }
        }

        return $groupArr;
    }

    private function delCommonInvite($idRes,$groupArr){
        //協力会社の関係，私たちは協力会社
        $arr = [];
        $part = [];
        $enterprises = User::whereIn('id',$idRes)->get('enterprise_id')->toArray();
        foreach ($enterprises as $item){
            $arr[] = $item['enterprise_id'];
        }
        if (count($arr)){
            $enterpriseParticipant = EnterpriseParticipant::where('user_id',Auth::id())->whereIn('enterprise_id',$arr)
                ->where('agree',1)->get('enterprise_id')->toArray();
            foreach ($enterpriseParticipant as $item){
                $part[] = $item['enterprise_id'];
            }
            $chatGroup = ChatGroup::where('user_id',Auth::id())->whereHas('mine',function ($q) use($part){
                $q->whereHas('user',function ($q1) use($part){
                    $q1->whereIn('enterprise_id',$part);
                });
            })->get('group_id')->toArray();
            foreach ($chatGroup as $item){
                $groupArr[] = $item['group_id'];
            }
        }
        return $groupArr;
    }

    private function delCommonFriendAndInvite($idRes,$AuthIdArr){
        $idRes = array_merge_recursive($idRes,$AuthIdArr);
        //協力会社の関係，私たちは会社
        $groupArr=[];
        $this->delCommonEnterprise($idRes,$groupArr);

        //協力会社の関係，私たちは協力会社
        $this->delCommonInvite($idRes,$groupArr);
        return $groupArr;
    }

    /**
     * 仲間 削除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delFriend(Request $request)
    {
        $ids = $request->get('id');
        $idArr = ChatContact::whereIn('id', $ids)->where('contact_agree',1)->get(['from_user_id', 'to_user_id', 'email'])->toArray();
        $idRes = [];
        $AuthIdArr = [];
        $groupArr = [];
        foreach ($idArr as $arr) {
            //削除者は職人です
            if (Auth::user()->email == $arr['email']) {
                $AuthIdArr[] = $arr['from_user_id'];
                //削除者は社内の従業員です
            } else {
                $idRes[] = $arr['to_user_id'];
            }
        }
        if (count($AuthIdArr)) {
            //ChatContact 同じ人を含む
            $accountRes = [];
            $chatContact = ChatContact::where('from_user_id', Auth::id())->where('contact_agree',1)
                ->whereIn('to_user_id', $AuthIdArr)->get()->toArray();
            foreach ($chatContact as $item) {
                $accountRes[] = $item['to_user_id'];
            }
            //Entertained people-> Get entertained group_id
            $enterpriseArr = User::whereIn('id', $AuthIdArr)->get('enterprise_id')->toArray();
            $enterpriseRes = [];
            foreach ($enterpriseArr as $enterprise) {
                $enterpriseRes[] = $enterprise['enterprise_id'];
            }
            $chatArr = ChatGroup::whereHas('user', function ($q) use ($enterpriseRes, $accountRes) {
                $q->whereIn('enterprise_id', $enterpriseRes);//社内
                $q->whereNotIn('id', $accountRes);
            })->whereHas('mine', function ($q) {
                $q->where('user_id', Auth::id())->where('admin', 1);//職人
            })->whereHas('group', function ($q) {
                $q->where('kind', 1);
            })->get('group_id')->toArray();
            foreach ($chatArr as $arr) {
                $groupArr[] = $arr['group_id'];
            }
        }
        if (count($idRes)) {
            //削除者は社内の従業員です,協力会社・職人は同じ人を含む
            $accountRes = [];
            $chatContact = ChatContact::where('to_user_id', Auth::id())->where('contact_agree',1)
                ->whereIn('from_user_id', $idRes)->get()->toArray();
            foreach ($chatContact as $item) {
                if (!in_array($item['id'], $accountRes)) {
                    $accountRes[] = $item['from_user_id'];
                }
            }
            //Get entertained->Entertained people group_id
            $userArr = User::where('enterprise_id', Auth::user()->enterprise_id)->get('id')->toArray();
            $res = [];
            foreach ($userArr as $user) {
                $res[] = $user['id'];
            }
            $chatGroupArr = ChatGroup::whereHas('group', function ($q) {
                $q->where('kind', 1);
            })->whereHas('mine', function ($q) use ($res) {
                $q->whereIn('user_id', $res)->where('admin', 1);//社内
                //職人
            })->whereIn('user_id', $idRes)->whereNotIn('user_id', $accountRes)->where('admin', 1)->get('group_id')->toArray();

            foreach ($chatGroupArr as $id) {
                $groupArr[] = $id['group_id'];
            }
        }
        $notGroupArr = $this->delCommonFriendAndInvite($AuthIdArr,$idRes);
        try {
            DB::beginTransaction();
            ChatContact::whereIn('id', $ids)->delete();
            if (count($groupArr)) {
                ChatGroup::whereIn('group_id', $groupArr)->whereNotIn('group_id', $notGroupArr)->delete();
                ChatList::whereIn('group_id', $groupArr)->whereNotIn('group_id', $notGroupArr)->delete();
                ChatMessage::whereIn('group_id', $groupArr)->whereNotIn('group_id', $notGroupArr)->delete();
                ChatLastRead::whereIn('group_id', $groupArr)->whereNotIn('group_id', $notGroupArr)->delete();
                Group::whereIn('id', $groupArr)->whereNotIn('group_id', $notGroupArr)->delete();
                Dashboard::whereIn('related_id',$groupArr)->where('type','0')->delete();
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            //データベースエラー
            $error = trans('messages.error.delete');
            return $this->error($e, [$error]);
        }
        return $this->json();
    }

    /**
     * 仲間を並べ替え
     * @param                                                                                                                                                                                                      $request
     * @return array|string|null
     */
    public function detailSearch(Request $request)
    {
        $searchArray = $request->input('searchName');
        $sort = $request->get('sort');
        $order = $request->get('order');
        if ($sort == 'name') {
            foreach ($searchArray as $item) {
                $paytime[] = $item['accounts']['name'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, $searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, $searchArray);
            }
        } elseif ($sort == 'date') {
            foreach ($searchArray as $item) {
                $paytime[] = $item['contact_date'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, $searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, $searchArray);
            }
        } elseif ($sort == 'company_name') {
            foreach ($searchArray as $item) {
                $paytime[] = $item['accounts']['company_name'];
            }
            if ($order == 'asc') {
                array_multisort($paytime, SORT_ASC, $searchArray);
            } else {
                array_multisort($paytime, SORT_DESC, $searchArray);
            }
        }
        return $searchArray;
    }
    /**
     * CSV出力機能の追加
     */
    public function workerCsv(){
        $sql = <<<EOF
SELECT users.id, users.name, users.first_name,users.last_name, users.email, ad.pref,ad.city, users.addr , users.telno1,users.specialty, users.license, users.skill,users.dream,users.motto, users.things_to_realize, users.company_name, CASE users.company_div when 1 then '法人' when 2 then '個人' END AS company_div, CASE users.corporate_type when 1 then '株式会社' when 2 then '有限会社' when 3 then '合同会社' END AS corporate_type, users.created_at, c.chatcount, s.schedulecount FROM users
LEFT OUTER JOIN (
            SELECT from_user_id, COUNT(*) AS chatcount FROM chatmessages GROUP BY from_user_id) AS c ON users.id = c.from_user_id  
LEFT OUTER JOIN (
            SELECT created_user_id, COUNT(*) AS schedulecount FROM schedules GROUP BY created_user_id) AS s ON users.id = s.created_user_id
LEFT OUTER JOIN (
            SELECT * FROM mst_address_mini) AS ad ON users.addr_code = ad.officialCode
WHERE users.deleted_at IS NULL AND enterprise_admin IS NULL AND
        NOT EXISTS (SELECT * from developers WHERE users.id = developers.user_id)
ORDER BY users.id;
EOF;
        $data = DB::select($sql);
        return $this->exportCsv($data);
    }


    /**
     * CSVファイルのエクスポート
     */
    private function exportCsv($data)
    {
        $data=json_decode(json_encode($data),true);
        $date=date('YmdHis');
        $filename = '職人一覧_'.$date.'.csv';
        //題名 改行なし
        $fileData = 'ユーザID,ユーザ名,名,姓,メールアドレス,都道府県,市区町村,番地,電話番号,得意な分野,資格,スキル,将来の夢,座右の銘,3～5年で実現したいこと,職人会社名,会社区分,会社タイプ,アカウント登録日時,チャット送信数,スケジュール作成数' . "\n";
        //文字列へのデータスプライシング
        foreach ($data as $value) {
            $temp = $value['id'] . ',' . $value['name']. ',' . $value['first_name'].
                ',' . $value['last_name']. ',' . $value['email']. ',' . $value['pref'].
                ',' . $value['city']. ',' . $value['addr']. ',' . $value['telno1'].
                ',' . $value['specialty']. ',' . $value['license']. ',' . $value['skill'].
                ',' . $value['dream']. ',' . $value['motto']. ',' . $value['things_to_realize'].
                ',' . $value['company_name']. ',' . $value['company_div']. ',' . $value['corporate_type'].
                ',' . $value['created_at']. ',' . $value['chatcount']. ',' . $value['schedulecount'];
            $fileData .= $temp. "\n";
        }
        header('Content-Encoding:UTF-8');
        header('Content-Type:text/csv;charset=UTF-8');
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo "\xEF\xBB\xBF";
        echo $fileData;
        exit;
    }


}
