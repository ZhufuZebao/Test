<?php

namespace App\Http\Middleware;

use App\Models\ChatGroup;
use App\Models\Customer;
use App\Models\CustomerOffice;
use App\Models\Group;
use App\Models\Operator;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\EnterpriseParticipant;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Authentication
{
    /**
     * 機関認証
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $url = $request->url();
            // 案件
            if (strpos($url, 'getProjectDetail')) {
                return $this->verifyProId($request, $next);
            } elseif (strpos($url, 'getProjectObject')) {
                return $this->verifyProId($request, $next);
            } elseif (strpos($url, 'getProjectAndCustomer')) {
                return $this->verifyProId($request, $next);
            } elseif (strpos($url, 'editProject')) {
                return $this->verifyProId($request, $next);
            } elseif (strpos($url, 'deleteProject')) {
                return $this->verifyProId($request, $next);
            } elseif (strpos($url, 'getParticipants')) {
                $projectId = $request->get('projectId');
                return $this->verifyProId($request, $next, $projectId);
            } elseif (strpos($url, 'getChatMassage')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            } elseif (strpos($url, 'insertProjectParticipants')) {
                $projectId = $request->get('projectId');
                return $this->verifyProId($request, $next, $projectId);
            } elseif (strpos($url, 'getProjectParticipants')) {
                $projectId = $request->get('projectId');
                return $this->verifyProId($request, $next, $projectId);
            } elseif (strpos($url, 'delProjectParticipant')) {
                $projectId = $request->get('projectId');
                return $this->verifyProId($request, $next, $projectId);
            } elseif (strpos($url, 'getFileList')) {
                $projectId = $request->get('projectId');
                return $this->verifyProId($request, $next, $projectId);
            } elseif (strpos($url, 'updateProjectProgressStatus')) {
                $projectId = $request->get('projectId');
                return $this->verifyProId($request, $next, $projectId);
                // スケジュール
            } elseif (strpos($url, 'checkUserClearGroup')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            } elseif (strpos($url, 'deleteSchScheduleWeek')) {
                $schId = $request->get('id');
                return $this->verifySchId($request, $next, $schId);
            } elseif (strpos($url, 'getScheduleById')) {
                $schId = $request->get('scheduleId');
                return $this->verifySchId($request, $next, $schId);
            } elseif (strpos($url, 'deleteScheduleById')) {
                $schId = $request->get('id');
                return $this->verifySchId($request, $next, $schId);
                //チャット
            } elseif (strpos($url, 'getGroupContact')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            } elseif (strpos($url, 'getChatList')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            } elseif (strpos($url, 'setChatMessage')) {
                $groupId = $request->get('group_id');
                return $this->verifyGroupId($request, $next, $groupId);
            } elseif (strpos($url, 'getGroupFileList')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'setGroup')) {
                $groupId=$request->post("parentId");
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'setChatList')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'getGroupUser')) {
                $groupId = $request->get('id');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'getGroupFriend')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'setFriendToGroup')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'delInGroup')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'clearGroup')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'updateMessage')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }
//            elseif (strpos($url, 'updateLastRead')) {
//                $groupId = $request->get('groupId');
//                return $this->verifyGroupId($request, $next, $groupId);
//            }
            elseif (strpos($url, 'getNewChatList')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
            }elseif (strpos($url, 'getFileSize')) {
                $groupId = $request->get('groupId');
                return $this->verifyGroupId($request, $next, $groupId);
                //施主
            }elseif (strpos($url, 'getCustomerList')) {
                if (!Auth::user()->enterprise_id){
                    $error = trans('messages.error.insert');
                    return $this->error('', $error);
                }else{
                    return $next($request);
                }
            }elseif (strpos($url, 'getEditCustomer')) {
                $customerId = $request->get("id");
                return $this->verifyCustomerId($request, $next, $customerId);
            }elseif (strpos($url, 'editCustomerOffice')) {
                $customerId = $request->get('customer')['id'];
                return $this->verifyCustomerId($request, $next, $customerId);
            }elseif (strpos($url, 'updateOffice')) {
                $customerId = $request->get('customer')['customer_id'];
                return $this->verifyCustomerId($request, $next, $customerId);
            }elseif (strpos($url, 'getCustomerName')) {
                $customerId = $request->get("id");
                return $this->verifyCustomerId($request, $next, $customerId);
            }elseif (strpos($url, 'deleteCustomerOffice')) {
                $cusOfficeId = $request->get('id');
                return $this->verifyCusOfficeId($request, $next, $cusOfficeId);
            }elseif (strpos($url, 'updateCustomerName')) {
                $customerId=$request->get('customer')['id'];
                return $this->verifyCustomerId($request, $next, $customerId);
            }elseif (strpos($url, 'getCustomerDetail')) {
                $customerId=$request->get('id');
                return $this->verifyCustomerId($request, $next, $customerId);
            }elseif (strpos($url, 'deleteCustomer')) {
                $customerId=$request->get('id');
                return $this->verifyCustomerId($request, $next, $customerId);
            }elseif (strpos($url, 'getOfficeDetail')) {
                $cusOfficeId = $request->get('id');
                return $this->verifyCusOfficeId($request, $next, $cusOfficeId);
            } else {
                if (strpos($url, 'adminLogin')){
                    return $next($request);
                }
                $isAllow = $this->getContactPermission($url,$request);
                if ($isAllow){
                    $error = trans('messages.error.permission');
                    // BUG #3217  scope = admin 管理者インターフェースを要求します。アクセス権限がなければ、フロントはlogin Formページにジャンプします。
                    return $this->json($error, ['scope' => 'admin']);
                }else{
                    return $next($request);
                }
            }
        } catch (\Exception $e) {
            $error = trans('messages.error.insert');
            return $this->error($e, $error);
        }
    }

    private function getContactPermission($url, $request)
    {
        //職人管理
        if (strpos($url, 'getWorkerList')
            || (strpos($url, 'workerDetail'))
            || (strpos($url, 'workerBlock'))
            //契約者
            || (strpos($url, 'getContractList'))
            || (strpos($url, 'getContractDetail'))
            || (strpos($url, 'getContractOffice'))
            || (strpos($url, 'ContractHistory'))
            || (strpos($url, 'projectCount'))
            || (strpos($url, 'contractContain'))
            || (strpos($url, 'enterpriseData'))
            || (strpos($url, 'updateContractDetail'))
            || (strpos($url, 'deleteContract'))
            || (strpos($url, 'getBrowse'))
            || (strpos($url, 'getOperatorUsers'))
            || (strpos($url, 'searchContract'))
            || (strpos($url, 'addContractFriend'))
            //お知らせ
            || (strpos($url, 'createNotice'))
            || (strpos($url, 'editNotice'))
            || (strpos($url, 'searchNotice'))) {
            $res = true;
        } elseif (strpos($url, 'getNoticeList')) {
            $type = $request->get('type');
            if ($type){
                $res = true;
            }else{
                $res = false;
            }
        }else{
            $res = false;
        }
        if ($res){
            $num = Operator::where('user_id',Auth::id())->count();
            if ($num){
                $res = false;
            }
        }
        return $res;
    }

    /**
     * @param $request
     * @param $next
     * @param $cusOfficeId
     * @return \Illuminate\Http\JsonResponse
     */
    private function verifyCusOfficeId($request, $next, $cusOfficeId){
        $customerArr = customerOffice::where('id',$cusOfficeId)->whereHas('customer',function ($q){
            $q->where('enterprise_id',Auth::user()->enterprise_id)->whereNotNull('enterprise_id');
        })->get('id')->toArray();
        if (count($customerArr)) {
            return $next($request);
        } else {
            $error = trans('messages.error.permission');
            return $this->error('', $error);
        }
    }

    /**
     * @param $request
     * @param $next
     * @param $customerId
     * @return \Illuminate\Http\JsonResponse
     */
    private function verifyCustomerId($request, $next, $customerId){
        $customerArr = Customer::where('id',$customerId)->
        where('enterprise_id',Auth::user()->enterprise_id)->whereNotNull('enterprise_id')->get('id')
            ->toArray();
        if (count($customerArr)) {
            return $next($request);
        } else {
            $error = trans('messages.error.permission');
            return $this->error('', $error);
        }
    }

    /**
     * @param $request
     * @param $next
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    private function verifyGroupMagId($request, $next, $userId)
    {
        if (Auth::id() == $userId) {
            return $next($request);
        } else {
            $error = trans('messages.error.permission');
            return $this->error('', $error);
        }
    }

    /**
     * @param $request
     * @param $next
     * @param $schId
     * @return \Illuminate\Http\JsonResponse
     */
    private function verifySchId($request, $next, $schId)
    {
        $cteated_id=Schedule::where('id',$schId)->value('created_user_id');
        if($cteated_id === Auth::id()){
            return $next($request);
        } elseif (!$cteated_id) {
            $error = trans('messages.error.scheduleCreate');
            return $this->json($error, $params = [], $status = 200);
        }
        $schArr = Schedule::whereHas('scheduleParticipants', function ($q) {
            $q->where('user_id', Auth::id());
        })->where('id', $schId)->get()
            ->toArray();
        if (count($schArr)) {
            return $next($request);
        } else {
            $error = trans('messages.error.permission');
            return $this->error('', $error);
        }
    }

    /**
     * @param $request
     * @param $next
     * @param $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    private function verifyGroupId($request, $next, $groupId)
    {
        if ($groupId) {
            $parentId = Group::where('id', $groupId)->get('parent_id')->toArray()[0]['parent_id'];
            if ($parentId) {
                $res = ChatGroup::where('group_id', $parentId)->where('user_id', Auth::id())->get('id')->toArray();
                if (!count($res)) {
                    $error = trans('messages.error.permission');
                    return $this->error('', $error);
                }
            }
            $groupArr = ChatGroup::where('group_id', $groupId)->where('user_id', Auth::id())->get('id')->toArray();
            if (count($groupArr)) {
                return $next($request);
            } else {
                $error = trans('messages.error.permission');
                return $this->error('', $error);
            }
        }else {
            return $next($request);
        }
    }

    /**
     * @param $request
     * @param $next
     * @param int $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    private function verifyProId($request, $next, $projectId = 0)
    {
        if (!$projectId) {
            $projectId = $request->get('projectId')?:$request->get('id');
        }
        $proArr = Project::where('id', $projectId)->where(function ($query) {
            $query->where('created_by', Auth::id());
            $query->orWhereHas('projectParticipant', function ($q) {
                $q->where('user_id', Auth::id());
            });
        })->get(['projects.id', 'projects.enterprise_id'])->toArray();

        if (count($proArr)) {
            $url = $request->url();
            $enterpriseId = Auth::user()->enterprise_id;
            if (strpos($url, 'getProjectAndCustomer')
                || (strpos($url, 'getProjectParticipants'))
                || (strpos($url, 'getChatMassage'))
                || (strpos($url, 'getFileList'))
                || (strpos($url, 'getProjectObject'))
                || (strpos($url, 'getProjectDetail'))) {
                return $next($request);
            } else {
                //協力会社ユーザでも共有者を追加
                $count=EnterpriseParticipant::where('enterprise_id',$proArr[0]['enterprise_id'])->where('user_id',Auth::id())->count();
                if ($enterpriseId != $proArr[0]['enterprise_id']&&!$count) {
                    $error = trans('messages.error.permission');
                    return $this->error('', $error);
                } else {
                    return $next($request);
                }
            }
        } else {
            $error = trans('messages.error.permission');
            return $this->error('', $error);
        }
    }

    private function error($e, $errors)
    {
        Log::error($e);
        return $this->json($errors);
    }

    private function json($errors = [], $params = [], $status = 403)
    {
        $data = [];
        $data['result'] = 1;
        $data['errors'] = $errors;
        if (!empty($params)) {
            $data['params'] = $params;
        }
        return response()->json($data, $status);
    }
}
