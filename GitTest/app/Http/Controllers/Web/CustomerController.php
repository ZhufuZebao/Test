<?php
/**
 * 施主管理
 *
 * @author zlh
 */

namespace App\Http\Controllers\Web;

use App\Models\Customer;
use App\Models\CustomerOffice;
use App\Models\Project;
use App\Models\CustomerOfficeBilling;
use App\Models\CustomerOfficePeople;
use App\Models\CustomerSearchModel;
use App\Models\ProjectCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Facades\Common;

class CustomerController extends \App\Http\Controllers\Controller
{

    //ページ数設定
    private $pagination = 10;

    /**
     * キーワード検索施主
     * @param Request $request
     * @return Collection
     */
    public function getCustomer(Request $request)
    {
        $customer = new Customer();
        $customer->init($request->get("keyword"));
        $customers = $customer->search();
        return $customers;
    }

    /**
     * キーワード検索事業所
     * @param Request $request
     * @return mixed models
     */
    public function getCustomerOffice(Request $request)
    {
        $customerOffice = new CustomerOffice();
        $ids = $request->get("ids");
        $customerOffice->init(Common::escapeDBSelectKeyword($request->get("keyword")));
        $offices = $customerOffice->search($ids);
        return $offices;
    }

    /**
     * 施主新規登録
     * @param Request $request
     * @return Customer|string
     */
    public function createCustomer(Request $request)
    {
        if (!Auth::user()->enterprise_id) {
            return $this->json("登録中にエラーが発生しました");
        }
        DB::beginTransaction();
        try {
            // 検証
            $errorMsg = $this->requestCheck($request);
            if ("noError" != $errorMsg) {
                return $errorMsg;
            }
            // customersの新規と変更
            $data = $request->get('customer');
            $userId = Auth::id();
            $enterprise_id = Auth::user()->enterprise_id;
            if (isset($data['id']) && !empty($data['id'])) {
                $customer = Customer::find($data['id']);
                $customer->user_id = $userId;
                $customer->updated_at = date("Y-m-d H:i:s");
                $customer->save();
            } else {
                $customer = new Customer();
                $customer->user_id = $userId;
                $customer->enterprise_id = $enterprise_id;
                $customer->name = $data['name'];
                $customer->phonetic = $data['phonetic'];
                $customer->created_at = date("Y-m-d H:i:s");
                $customer->save();
            }
            // customer_officesの新規
            $customerOffice = new CustomerOffice();
            $this->officeBuilder($request, $customerOffice);
            $customerOffice->customer_id = $customer->id;
            $customerOffice->created_at = date("Y-m-d H:i:s");
            $customerOffice->save();
            // customer_office_peoplesの新規
            if ($data['office']['people']) {
                foreach ($data['office']['people'] as $key => $people) {
                    $officePeople = new CustomerOfficePeople();
                    $officePeople->fill($people);
                    $officePeople->customer_office_id = $customerOffice->id;
                    $officePeople->created_at = date("Y-m-d H:i:s");
                    $officePeople->user_id = $userId;
                    $officePeople->save();
                }
            }
            // customer_office_billingsの新規
            if ($data['office']['billings']) {
                foreach ($data['office']['billings'] as $key => $billing) {
                    $officeBilling = new CustomerOfficeBilling();
                    $officeBilling->fill($billing);
                    $officeBilling->customer_office_id = $customerOffice->id;
                    $officeBilling->created_at = date("Y-m-d H:i:s");
                    $officeBilling->user_id = $userId;
                    unset($officeBilling->telIn);
                    $officeBilling->save();
                }
            }
            $users = User::where('enterprise_id',Auth::user()->enterprise_id)->get()->toArray();
            $dashboard = new DashboardController();
            foreach ($users as $item){
                if (Auth::id() != $item['id']){
                    $dashboard->addDashboard($customer->id,8,
                        $data['name'].'が追加されました。',
                        '',$item['id']);
                }
            }
            $people = CustomerOfficePeople::where('customer_office_id', $customerOffice->id)->get();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return $this->json("登録中にエラーが発生しました");
        }
        return $this->json("", ['customer' => $customer, 'office' => $customerOffice, 'people' => $people]);
    }

    /**
     * ドナー及び事业所情报を変更します
     * @param Request $request
     * @return json
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        $arr = array();
        try {
            $data = $request->get('customer');
            $userId = Auth::id();
            // 検証
            $errorMsg = $this->requestCheck($request);
            if ("noError" != $errorMsg) {
                return $errorMsg;
            }
            // customersの変更
            $customer = Customer::find($data['id']);
            $customer->name = $data['name'];
            $customer->phonetic = $data['phonetic'];
            $customer->updated_at = date("Y-m-d H:i:s");
            $customer->save();
            // customer_officesの変更
            $customerOffice = CustomerOffice::find($data['office']['id']);
            $this->officeBuilder($request, $customerOffice);
            $customerOffice->customer_id = $customer->id;
            $customerOffice->updated_at = date("Y-m-d H:i:s");
            $customerOffice->save();
            // customer_office_peoplesの新規と変更
            if ($data['office']['people']) {
                foreach ($data['office']['people'] as $key => $people) {
                    if (isset($people['id']) && !empty($people['id'])) {
                        $officePeople = CustomerOfficePeople::find($people['id']);
                        $officePeople->fill($people);
                        $officePeople->updated_at = date("Y-m-d H:i:s");
                        $officePeople->save();
                        array_push($arr, $people['id']);
                    } else {
                        $officePeople = new CustomerOfficePeople();
                        $officePeople->fill($people);
                        $officePeople->customer_office_id = $customerOffice->id;
                        $officePeople->created_at = date("Y-m-d H:i:s");
                        $officePeople->user_id = $userId;
                        $officePeople->save();
                        array_push($arr, $officePeople->id);
                    }
                }
            }
            // customer_office_peoplesの削除
            if (count($arr) == 0) {
                CustomerOfficePeople::where('customer_office_id', '=', $customerOffice->id)->delete();
            } else {
                CustomerOfficePeople::whereNotIn('id', $arr)->where('customer_office_id', '=',
                    $customerOffice->id)->delete();
            }
            // customer_office_billingsの新規と変更
            $arr = array();
            if ($data['office']['billings']) {
                foreach ($data['office']['billings'] as $key => $billing) {
                    if (isset($billing['id']) && !empty($billing['id'])) {
                        $officeBilling = CustomerOfficeBilling::find($billing['id']);
                        $officeBilling->fill($billing);
                        $officeBilling->updated_at = date("Y-m-d H:i:s");
                        unset($officeBilling->telIn);
                        $officeBilling->save();
                        array_push($arr, $billing['id']);
                    } else {
                        $officeBilling = new CustomerOfficeBilling();
                        $officeBilling->fill($billing);
                        $officeBilling->customer_office_id = $customerOffice->id;
                        $officeBilling->created_at = date("Y-m-d H:i:s");
                        $officeBilling->user_id = $userId;
                        unset($officeBilling->telIn);
                        $officeBilling->save();
                        array_push($arr, $officeBilling->id);
                    }
                }
            }
            // customer_office_billingsの削除
            if (count($arr) == 0) {
                CustomerOfficeBilling::where('customer_office_id', '=', $customerOffice->id)->delete();
            } else {
                CustomerOfficeBilling::whereNotIn('id', $arr)->where('customer_office_id', '=',
                    $customerOffice->id)->delete();
            }
            DB::commit();
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            return $this->json("変更中にエラーが発生しました");
        }
        return $this->json();
    }

    /**
     * CustomerOffice object build
     * @param Request $request
     * @param CustomerOffice $customerOffice
     * @return CustomerOffice
     */
    private function officeBuilder(Request $request, CustomerOffice $customerOffice)
    {
        $data = $request->get('customer');
        $office = $data['office'];
        $customerOffice->name = $office['name'];
        $customerOffice->zip = $office['zip'];
        $customerOffice->pref = $office['pref'];
        $customerOffice->town = $office['town'];
        $customerOffice->street = $office['street'];
        $customerOffice->house = $office['house'];
        $customerOffice->tel = $office['telOut'];
        $customerOffice->tel_in = $office['telIn'];
        $customerOffice->fax = $office['fax'];
        $customerOffice->user_id = Auth::id();
        return $customerOffice;
    }

    /**
     * customerIdとcustomerOfficeIdでCustomerOfficeを検索する
     * @param Request $request
     * @return array
     */
    public function getEditCustomer(Request $request)
    {
        $customerOffice = CustomerOffice::with("people", "billings")->where("id", "=",
            $request->get("officeId"))->get();
        $customer = Customer::find($request->get("id"));
        return ["customer" => $customer, "office" => $customerOffice];
    }

    /**
     * customerIdでCustomerを検索する
     * @param Request $request
     * @return mixed
     */
    public function getCustomerName(Request $request)
    {
        $customer = Customer::find($request->get("id"));
        return $customer;
    }

    /**
     * customerOffice 事業所を削除する
     * @param Request $request
     * @return mixed
     */
    public function deleteOffice(Request $request)
    {
        $co = customerOffice::find($request->get('id'));
        //この事業所の情報が案件情報に結び付けられているかどうかを検証する
        $customer_office_bind = ProjectCustomer::where('office_id', $request->get('id'))->count();
        if ($customer_office_bind > 0) {
            $error = trans('messages.error.officeBind');
            return $this->error($error, [$error]);
        }
        $num = customerOffice::where('customer_id', $co->customer_id)->count();
        if ($num > 1) {
            try {
                CustomerOfficeBilling::where('customer_office_id',$request->get('id'))->delete();
                CustomerOfficePeople::where('customer_office_id',$request->get('id'))->delete();
                $co::destroy($request->get('id'));
                $customerOffice = $co::destroy($request->get('id'));
                return $customerOffice;
            } catch (\PDOException $e) {
                DB::rollBack();
                $error = trans('messages.error.delete');
                return $this->error($e, [$error]);
            }
        } else {
            $error = trans('messages.error.office');
            return $this->error($error, [$error]);
        }

    }

    /**
     * customerのname update
     * @param Request $request
     * @return $modal
     */
    public function updateCustomerName(Request $request)
    {
        try {
            $customer = Customer::find($request->post('customer')['id']);
            $customer->name = $request->post('customer')['name'];
            $customer->phonetic = $request->post('customer')['phonetic'];
            $v = $customer->validate();
            if (!$v->failed()) {
                $customer->save();
            }
            $users = User::where('enterprise_id',Auth::user()->enterprise_id)->get()->toArray();
            $dashboard = new DashboardController();
            foreach ($users as $item){
                if (Auth::id() != $item['id']){
                    $dashboard->addDashboard($customer->id,8,
                        $customer->name.'が変更されました。',
                        '',$item['id']);
                }
            }
        } catch (\PDOException $e) {
            return $this->error($e);
        }
        return $customer;
    }

    /**
     * 施主詳細、関連テーブルを取得する
     * @param Request $request
     * @return $modal
     */
    public function show(Request $request)
    {
        return $customer = Customer::with('offices.people', 'offices.billings')->find($request->get('id'));
    }

    /**
     * 施主を削除する
     * @param Request $request
     * @return mixed json / model
     */
    public function delete(Request $request)
    {
        //この施主の情報が案件情報に結び付けられているかどうかを検証する
        $customer_office_bind = ProjectCustomer::where('customer_id', $request->get('id'))->count();
        if ($customer_office_bind > 0) {
            $error = trans('messages.error.customerBind');
            return $this->error($error, [$error]);
        } else {
            DB::beginTransaction();
            try{
                $id = CustomerOffice::where('customer_id',$request->get('id'))->pluck('id')->toArray();
                CustomerOfficeBilling::whereIn('customer_office_id',$id)->delete();
                CustomerOfficePeople::whereIn('customer_office_id',$id)->delete();
                CustomerOffice::where('customer_id',$request->get('id'))->delete();
                $customer = Customer::destroy($request->get('id'));
                DB::commit();
            }catch (\PDOException $e) {
                DB::rollBack();
                $error = trans('messages.error.delete');
                return $this->error($e, [$error]);
            }
            return $customer;
        }

    }

    /**
     * 施主一覧取得
     * @param Request $request
     * @return $modal
     * @throws \Spatie\PdfToImage\Exceptions\PdfDoesNotExist
     * @throws \Spatie\PdfToImage\Exceptions\PageDoesNotExist
     */
    public function getlist(Request $request)
    {
        $model = Customer::with('offices.billings')->where('enterprise_id',
            Auth::user()->enterprise_id)
            ->orderBy($request->get('sort'), $request->get('order'))
            ->paginate($this->getPagesize($this->pagination));
        return $model;
    }

    /**
     * 入来値検出
     * @param Request $request
     * @return mix
     */
    public function requestCheck(Request $request)
    {
        $data = $request->get("customer");
        $office = $data['office'];
        $people = $office['people'];
        $billings = $office['billings'];
        $customerCheck = new Customer();
        $customerCheck->fill($data);
        $validate = $customerCheck->validate();
        if ($validate->fails()) {
            return $this->json($validate->errors()->all());
        }
        $check = new CustomerOffice();
        $check->fill($office);
        $validate = $check->validate();
        if ($validate->fails()) {
            return $this->json($validate->errors()->all());
        }
        if ($people) {
            foreach ($people as $key => $value) {
                $check = new CustomerOfficePeople();
                $check->fill($value);
                $validate = $check->validate();
                if ($validate->fails()) {
                    return $this->json($validate->errors()->all());
                }
            }
        }
        if ($billings) {
            foreach ($billings as $key => $billing) {
                $check = new CustomerOfficeBilling();
                $check->fill($billing);
                $validate = $check->validate();
                if ($validate->fails()) {
                    return $this->json($validate->errors()->all());
                }
            }
        }
        return "noError";
    }

    /**
     * 施主詳細検索
     * @param Request $request
     * @return $models
     */
    public function detailedSearch(Request $request)
    {
        $query = new CustomerSearchModel();
        $query->init([
            'searchArray' => $request->get('searchArray'),
        ]);
        $models = $query->detailedSearch()->with('offices.billings')->where('enterprise_id',
            Auth::user()->enterprise_id)->orderBy($request->get('sort'),
            $request->get('order'))->paginate($this->pagination);
        return $models;
    }

    /**
     * 事業所の詳細情報を取得する
     * @param Request $request
     * @return $models
     */
    public function officeDetail(Request $request)
    {
        $office = new CustomerOffice();
        $info = $office->with('people', 'billings')->find($request->get('id'));
        return $info;
    }

    /**
     * キーワードでドナー情報を検索する
     * @param Request $request
     * @return mixed $models
     */
    public function easySearch(Request $request)
    {
        $kw = '%' . Common::escapeDBSelectKeyword($request->get('keyword')) . '%';
        dd($kw);
        //住所全体で検索できる
        $model = Customer::whereHas('customerOffice', function ($data) use ($kw) {
                $data->whereRaw("CONCAT(IFNULL(`pref`,''), IFNULL(`town`,''),IFNULL(`street`,''),IFNULL(`house`,'')) LIKE ?", [$kw])
                ->where('enterprise_id', Auth::user()->enterprise_id);
        })->orWhere('name', 'like', $kw)->with('offices.people', 'offices.billings')
            ->where('enterprise_id', Auth::user()->enterprise_id)
            ->orderBy($request->get('sort'), $request->get('order'))
            ->paginate($this->getPagesize($this->pagination));
        return $model;
    }

    /**
     * 事業所詳細情報修正
     * @param Request $request
     * @return mixed json $model
     */
    public function updateOffice(Request $request)
    {
        DB::beginTransaction();
        $arr = array();
        try {
            $data = $request->get('customer');
            $userId = Auth::id();
            // 検証
            $errorMsg = $this->officeCheck($request);
            if ("noError" != $errorMsg) {
                return $errorMsg;
            }
            // customersの変更
            $customer = Customer::find($data['customer_id']);
            $customer->name = $customer['name'];
            $customer->phonetic = $customer['phonetic'];
            $customer->updated_at = date("Y-m-d H:i:s");
            $customer->save();
            // customer_officesの変更
            $customerOffice = CustomerOffice::find($data['id']);
            $this->officeBuilderEdit($request, $customerOffice);
            $customerOffice->customer_id = $customer->id;
            $customerOffice->updated_at = date("Y-m-d H:i:s");
            $customerOffice->save();
            // customer_office_peoplesの新規と変更
            if ($data['people']) {
                foreach ($data['people'] as $key => $people) {
                    if (isset($people['id']) && !empty($people['id'])) {
                        $officePeople = CustomerOfficePeople::find($people['id']);
                        $officePeople->fill($people);
                        $officePeople->updated_at = date("Y-m-d H:i:s");
                        $officePeople->save();
                        array_push($arr, $people['id']);
                    } else {
                        $officePeople = new CustomerOfficePeople();
                        $officePeople->fill($people);
                        $officePeople->customer_office_id = $customerOffice->id;
                        $officePeople->created_at = date("Y-m-d H:i:s");
                        $officePeople->user_id = $userId;
                        $officePeople->save();
                        array_push($arr, $officePeople->id);
                    }
                }
            }
            // customer_office_peoplesの削除
            if (count($arr) == 0) {
                CustomerOfficePeople::where('customer_office_id', '=', $customerOffice->id)->delete();
            } else {
                CustomerOfficePeople::whereNotIn('id', $arr)->where('customer_office_id', '=',
                    $customerOffice->id)->delete();
            }
            // customer_office_billingsの新規と変更
            $arr = array();
            if ($data['billings']) {
                foreach ($data['billings'] as $key => $billing) {
                    if (isset($billing['id']) && !empty($billing['id'])) {
                        $officeBilling = CustomerOfficeBilling::find($billing['id']);
                        $officeBilling->fill($billing);
                        $officeBilling->updated_at = date("Y-m-d H:i:s");
                        unset($officeBilling->telIn);
                        $officeBilling->save();
                        array_push($arr, $billing['id']);
                    } else {
                        $officeBilling = new CustomerOfficeBilling();
                        $officeBilling->fill($billing);
                        $officeBilling->customer_office_id = $customerOffice->id;
                        $officeBilling->created_at = date("Y-m-d H:i:s");
                        $officeBilling->user_id = $userId;
                        unset($officeBilling->telIn);
                        $officeBilling->save();
                        array_push($arr, $officeBilling->id);
                    }
                }
            }
            // customer_office_billingsの削除
            if (count($arr) == 0) {
                CustomerOfficeBilling::where('customer_office_id', '=', $customerOffice->id)->delete();
            } else {
                CustomerOfficeBilling::whereNotIn('id', $arr)->where('customer_office_id', '=',
                    $customerOffice->id)->delete();
            }
            $users = User::where('enterprise_id',Auth::user()->enterprise_id)->get()->toArray();
            $dashboard = new DashboardController();
            foreach ($users as $item){
                if (Auth::id() != $item['id']){
                    $dashboard->addDashboard($customer->id,8,
                        $customer->name.'が変更されました。',
                        '',$item['id']);
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            Log::error($e);
            DB::rollBack();
            return $this->json("変更中にエラーが発生しました");
        }
        return $this->json();
    }

    /**
     * 事業所詳細情報は入来パラメータ検証を修正する
     * @param Request $request
     * @return miexed
     */
    public function officeCheck(Request $request)
    {
        $data = $request->get("customer");
        $office = $data;
        $people = $office['people'];
        $billings = $office['billings'];
        $check = new CustomerOffice();
        $check->fill($office);
        $validate = $check->validate();
        if ($validate->fails()) {
            return $this->json($validate->errors()->all());
        }
        if ($people) {
            foreach ($people as $key => $value) {
                $check = new CustomerOfficePeople();
                $check->fill($value);
                $validate = $check->validate();
                if ($validate->fails()) {
                    return $this->json($validate->errors()->all());
                }
            }
        }
        if ($billings) {
            foreach ($billings as $key => $billing) {
                $check = new CustomerOfficeBilling();
                $check->fill($billing);
                $validate = $check->validate();
                if ($validate->fails()) {
                    return $this->json($validate->errors()->all());
                }
            }
        }
        return "noError";
    }

    /**
     * 事業所の情報構造化
     * @param Request $request
     * @param CustomerOffice $customerOffice
     * @return CustomerOffice
     */
    private function officeBuilderEdit(Request $request, CustomerOffice $customerOffice)
    {
        $data = $request->get('customer');
        $office = $data;
        $customerOffice->name = $office['name'];
        $customerOffice->zip = $office['zip'];
        $customerOffice->pref = $office['pref'];
        $customerOffice->town = $office['town'];
        $customerOffice->street = $office['street'];
        $customerOffice->house = $office['house'];
        $customerOffice->tel = $office['telOut'];
        $customerOffice->tel_in = $office['telIn'];
        $customerOffice->fax = $office['fax'];
        $customerOffice->user_id = Auth::id();
        return $customerOffice;
    }
}
