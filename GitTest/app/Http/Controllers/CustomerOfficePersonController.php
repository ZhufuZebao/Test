<?php
/**
 * 顧客>請求先のコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use DB;
use App\Http\Requests\CustomerOfficePersonFormRequest;
use App\CustomerOfficePerson;
use Session;

/**
 * 顧客>請求先
 */
class CustomerOfficePersonController extends \App\Http\Controllers\Controller
{
    /**
     * 
     * @return 200 | 404 Not found
     */
    public function create($oid)
    {
        $model = new CustomerOfficePerson();
        $model->customer_office_id = $oid;

        return view('/customer-office-person/edit', [
            'model' => $model,
        ]);
    }

    /**
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    public function edit($cid, $oid)
    {
        $model = $this->findModel($oid);

        return view('/customer-office-person/edit', [
            'model' => $model,
        ]);
    }

    /*
    public function index()
    {
    }
     */

    public function show($oid,$pid)
    {
        $model = $this->findModel($pid);

        return view('/customer-office-person/show', [
            'model' => $model,
        ]);
    }

    private static function zip2addr($zip)
    {
        $params = \App\Zipdata::zip2addr($zip);

        return [
            'billing_pref_id' => $params['pref_id'],
            'billing_town'    => $params['city'].$params['town'],
        ];
    }

    public function store($oid, CustomerOfficePersonFormRequest $request)
    {
        $model = new CustomerOfficePerson();
        $model->customer_office_id = $oid;
        $model->user_id = Auth::user()->id;
        $model->fill($request->all());

        if($request->get('zip2addr'))
        {
            $params = self::zip2addr($request->get('billing_zip'));
            $model->fill($params);

            return view('/customer-office-person/edit', [
                'model'  => $model,
            ]);
        }

        $v = $model->validate();
        if(! $v->failed())
            $model->save();

        return view('/customer-office-person/show', [
            'model' => $model,
        ]);
    }

    public function update($oid, $pid, CustomerOfficePersonFormRequest $request)
    {
        $model = $this->findModel($pid);
        $model->fill($request->all());

        if($request->get('zip2addr'))
        {
            $params = self::zip2addr($request->get('billing_zip'));
            $model->fill($params);
        }
        else
        {
            $v = $model->validate();
            if(! $v->failed())
                $model->update();

            return redirect()->route('customer-office-person.show',['oid'=>$oid,'pid'=>$pid]);
        }

        return view('/customer-office-person/edit', [
            'model' => $model,
        ]);
    }

    /**
     * Model を返す
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    private function findModel($id)
    {
        $model = \App\CustomerOfficePerson::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
