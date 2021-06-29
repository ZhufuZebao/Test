<?php
/**
 * 施主のコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use DB;
use App\Customer;
use App\CustomerOffice;
use App\CustomerOfficePerson;
use App\CustomerOfficeBilling;
use App\CustomerSearchModel;
use GuzzleHttp\Client;
use Session;

/**
 * 施主管理
 */
class CustomerController extends \App\Http\Controllers\Controller
{
    private $pagination = 10;

    public function getList(Request $request)
    {
        $model = Customer::with('offices.people','offices.billings')->paginate($this->pagination);
        return $model;
    }

    public function search(Request $request)
    {
        $query = new CustomerSearchModel();
        $query->init([
            'searchword' => $request->get('q'),
            'target' => $request->get('target'),
        ]);
        $models = $query->search()->with('offices.people','offices.billings')->orderBy($request->get('sort'))->paginate($this->pagination);

        return $models;
    }

    public function detailedSearch(Request $request)
    {
        $query = new CustomerSearchModel();
        $query->init([
            'searchArray' => $request->get('searchArray'),
        ]);
        $models = $query->detailedSearch()->with('offices.people','offices.billings')->orderBy($request->get('sort'),$request->get('order'))->paginate($this->pagination);

        return $models;
    }

    public function show(Request $request)
    {
        return $customer = Customer::with('offices.people','offices.billings')->find($request->get('id'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $customer = new Customer();
            $customer->user_id = Auth::user()->id;
            $customer->fill($request->all());
            
            $v = $customer->validate();
            if(! $v->failed())
                $customer->save();
                
            
            foreach($request->get('offices', []) as $reqOffice){
                $office = new CustomerOffice();
                $office->user_id = Auth::user()->id;
                $office->customer_id = $customer->id;
                $office->fill($reqOffice);
                $v = $office->validate();
                if(! $v->failed())
                $office->save();

                foreach($reqOffice['people'] as $reqPerson){
                    $person = new CustomerOfficePerson();
                    $person->user_id = Auth::user()->id;
                    $person->customer_office_id = $office->id;
                    $person->fill($reqPerson);
                    $v = $person->validate();
                    if(! $v->failed())
                        $person->save();
                }
                
                foreach($reqOffice['billings'] as $reqBilling){
                    $billing = new CustomerOfficeBilling();
                    $billing->user_id = Auth::user()->id;
                    $billing->customer_office_id = $office->id;
                    $billing->fill($reqBilling);
                    $v = $billing->validate();
                    if(! $v->failed())
                        $billing->save();
                }
            }
            DB::commit();
        } catch (\PDOException $e){
            DB::rollBack();
            return $e;
        }
        return $customer;
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $customer = Customer::find($request->get('id'));
            $customer->user_id = Auth::user()->id;
            $customer->fill($request->all());
            
            $v = $customer->validate();
            if(! $v->failed())
                $customer->save();

            $officeIds = array_column($request->get('offices', []),'id'); 
            $customer->offices()->whereNotIn('id',$officeIds)->delete();

            foreach($request->get('offices', []) as $reqOffice){
                if(array_key_exists('id',$reqOffice)){
                    $office = CustomerOffice::find($reqOffice['id']);
                }else{
                    $office = new CustomerOffice();
                    $office->customer_id = $customer->id;
                }
                $office->user_id = Auth::user()->id;
                $office->fill($reqOffice);
                $v = $office->validate();
                if(! $v->failed())
                    $office->save();

                $billingIds = array_column($reqOffice['billings'],'id');
                $office->billings()->whereNotIn('id',$billingIds)->delete();
                $personIds = array_column($reqOffice['people'],'id');
                $office->people()->whereNotIn('id',$personIds)->delete();
            
                foreach($reqOffice['people'] as $reqPerson){
                    if(array_key_exists('id',$reqPerson)){
                        $Person = CustomerOfficePerson::find($reqPerson['id']);
                    }else{
                        $Person = new CustomerOfficePerson();
                    }
                    $Person->user_id = Auth::user()->id;
                    $Person->customer_office_id = $office->id;
                    $Person->fill($reqPerson);
                    $v = $Person->validate();
                    if(! $v->failed())
                        $Person->save();
                }

                foreach($reqOffice['billings'] as $reqBilling){
                    if(array_key_exists('id',$reqBilling)){
                        $billing = CustomerOfficeBilling::find($reqBilling['id']);
                    }else{
                        $billing = new CustomerOfficeBilling();
                    }
                    $billing->user_id = Auth::user()->id;
                    $billing->customer_office_id = $office->id;
                    $billing->fill($reqBilling);
                    $v = $billing->validate();
                    if(! $v->failed())
                        $billing->save();
                }
            }
            DB::commit();
        } catch (\PDOException $e){
            DB::rollBack();
            return $e;
        }
        return $customer;
    }

    public function delete(Request $request)
    {
        $customer = Customer::destroy($request->get('id'));

        return $customer;
    }

    public function zipcloud(Request $request)
    {
        $url = "http://zipcloud.ibsnet.co.jp/api/search";

        $client = new Client();
        $res = $client->get($url, [
            "query" => [
                "zipcode" => $request->get('zipcode')
            ]
        ]);
        $data = json_decode($res->getBody(),true);
        return $data;
    }

}
