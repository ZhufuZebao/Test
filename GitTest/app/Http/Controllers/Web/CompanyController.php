<?php
/**
 * アカウント管理のコントローラー
 *
 * @author  WuJi
 */

namespace App\Http\Controllers\Web;

use App\Models\EnterpriseIntelligence;
use App\Models\EnterprisePerson;
use App\Models\Enterprise;
use App\Models\AccountSearchModel;
use App\Models\EnterpriseParticipant;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatGroup;
use App\Models\Group;
use App\Models\ChatList;
use Illuminate\Support\Facades\DB;
use App\Http\Facades\Common;


/**
 * アカウント管理 一覧
 * @param Request $request
 * @return array|string
 */
class CompanyController extends \App\Http\Controllers\Controller
{
    public function index(Request $request){
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $order = $request->input('order','asc');
        $sortCol = $request->input('sortCol', 'name');
        $KeyName = Common::escapeDBSelectKeyword($request->input('KeyName'));
        $offset = ($page - 1) * $pageSize;
        $res = EnterpriseIntelligence::where('enterprise_id',Auth::user()->enterprise_id)->where('name', 'like', "%$KeyName%")->orderBy($sortCol,
            $order)->offset($offset)->limit($pageSize)->get();
        $count= EnterpriseIntelligence::where('enterprise_id',Auth::user()->enterprise_id)->where('name', 'like', "%$KeyName%")->count();
        $from = ($page - 1) * $pageSize + 1;
        $to = ($page - 1) * $pageSize + count($res);
        $result['companys'] = $res;
        $result['current_page'] = $page;
        $result['total'] = $count;
        $result['from'] = $from;
        $result['to'] = $to;
        return $result;
    }


    public function save(Request $request){
        DB::beginTransaction();
        try{
            $model=new EnterpriseIntelligence();
            $companyBasis = json_decode($request->get("companyBasis"), true);
            $localeChief = json_decode($request->get("localeChief"), true);
            if(Auth::user()->enterprise_id){
                $enterprise_id=Auth::user()->enterprise_id;
            }else{
                $enterprise_id=Auth::user()->coop_enterprise_id;
            }
            $model->enterprise_id=$enterprise_id;
            $model->name=$companyBasis['name'];
            $model->phonetic=$companyBasis['phonetic'];
            $model->zip=$companyBasis['zip'];
            $model->pref=$companyBasis['pref'];
            $model->town=$companyBasis['town'];
            $model->street=$companyBasis['street'];
            $model->house=$companyBasis['house'];
            $model->street=$companyBasis['street'];
            $model->tel=$companyBasis['tel'];
            $model->fax=$companyBasis['fax'];
            $model->remarks=$companyBasis['remarks'];
            $model->created_at=date('Y-m-d H:i:s');
            $model->type=$companyBasis['type'];
            //$model->type=1;
            $model->save();
            foreach ($localeChief as $key =>$value){
                $enterprisePerson=new EnterprisePerson();
                $enterprisePerson->enterprise_intelligences_id=$model->id;
                $enterprisePerson->dept=$value['dept'];
                $enterprisePerson->email=$value['email'];
                $enterprisePerson->name=$value['name'];
                $enterprisePerson->position=$value['position'];
                $enterprisePerson->tel=$value['tel'];
                $enterprisePerson->save();
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return $this->json("変更中にエラーが発生しました");
        }
        return $this->json("", "登録しました");

    }
    public function getCompany(Request $request)
    {

        $enterpriseId=Auth::user()->enterprise_id;
        $model=EnterpriseIntelligence::where('id', '=', $request->get('id'))->first();
        $model->localeChief=EnterprisePerson::where('enterprise_intelligences_id', '=', $request->get('id'))->get()->toArray();
        return ['model'=>$model,'enterpriseId'=>$enterpriseId];
    }
    public function edit(Request $request){
        DB::beginTransaction();
        try{
        $companyBasis = json_decode($request->get("companyBasis"), true);
        $localeChief = json_decode($request->get("localeChief"), true);
        $model = EnterpriseIntelligence::where('id', $companyBasis['id'])->first();
        $model->name=$companyBasis['name'];
        $model->phonetic=$companyBasis['phonetic'];
        $model->zip=$companyBasis['zip'];
        $model->pref=$companyBasis['pref'];
        $model->town=$companyBasis['town'];
        $model->street=$companyBasis['street'];
        $model->house=$companyBasis['house'];
        $model->street=$companyBasis['street'];
        $model->tel=$companyBasis['tel'];
        $model->fax=$companyBasis['fax'];
        $model->remarks=$companyBasis['remarks'];
        $model->type=$companyBasis['type'];
        $model->updated_at=date('Y-m-d H:i:s');
        $model->save();
        $localId=EnterprisePerson::where('enterprise_intelligences_id', $companyBasis['id'])->pluck('id')->toArray();
        $arr=array_column($localeChief, 'id');
        foreach ($localId as $key =>$value){
              if(!in_array($value,$arr)){
                  EnterprisePerson::where('id', $value)->delete();
               }
        }
        foreach ($localeChief as $key =>$value){
            if(array_key_exists('id',$value)){
                $enterprisePerson=EnterprisePerson::where('id', $value['id'])->first();
                $enterprisePerson->dept=$value['dept'];
                $enterprisePerson->email=$value['email'];
                $enterprisePerson->name=$value['name'];
                $enterprisePerson->position=$value['position'];
                $enterprisePerson->tel=$value['tel'];
                $enterprisePerson->save();
            }else{
                $enterprisePerson=new EnterprisePerson();
                $enterprisePerson->enterprise_intelligences_id=$model->id;
                $enterprisePerson->dept=$value['dept'];
                $enterprisePerson->email=$value['email'];
                $enterprisePerson->name=$value['name'];
                $enterprisePerson->position=$value['position'];
                $enterprisePerson->tel=$value['tel'];
                $enterprisePerson->save();
            }
        }
        DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return $this->json("変更中にエラーが発生しました");
        }
        return $this->json("", "変更しました");
    }

    /**
     * 会社情報 削除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCompany(Request $request)
    {
        $ids = $request->get('id');
        $idArr = EnterpriseIntelligence::whereIn('id', $ids)->pluck('id')->toArray();
        DB::beginTransaction();
        try{
            foreach ($idArr as $key =>$value) {
                EnterprisePerson::where('enterprise_intelligences_id', '=', $value)->delete();
            }
            EnterpriseIntelligence::whereIn('id', $ids)->delete();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            $error = trans('messages.error.delete');
            return $this->error($e, [$error]);
        }
        return $this->json();
    }
}