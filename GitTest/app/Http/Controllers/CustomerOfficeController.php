<?php
/**
 * 日報のコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use DB;
use App\CustomerOffice;
use Session;

/**
 * 顧客管理
 */
class CustomerOfficeController extends \App\Http\Controllers\Controller
{
    /**
     * 
     * @return 200 | 404 Not found
     */
    public function create($cid)
    {
        $model = new CustomerOffice();
        $model->customer_id = $cid;

        return view('/customer-office/edit', [
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

        return view('/customer-office/edit', [
            'model' => $model,
        ]);
    }

    /*
    public function index()
    {
    }
     */

    public function show($cid,$oid)
    {
        $model = $this->findModel($oid);

        return view('/customer-office/show', [
            'model' => $model,
        ]);
    }

    private static function zip2addr($zip)
    {
        return \App\Zipdata::zip2addr($zip);
    }

    public function store($cid, Request $request)
    {
        $model = new CustomerOffice();
        $model->customer_id = $cid;
        $model->user_id = Auth::user()->id;
        $model->fill($request->all());

        if($request->get('zip2addr'))
        {
            $params = self::zip2addr($request->get('zip'));
            $model->fill($params);

            return view('/customer-office/edit', [
                'model'  => $model,
            ]);
        }

        $v = $model->validate();
        if(! $v->failed())
            $model->save();

        return redirect()->route('customer-office.show',[
            'cid' => $model->customer_id,
            'oid' => $model->id,
        ]);
    }

    public function update($cid, $oid, Request $request)
    {
        $model = $this->findModel($oid);

        $model->fill($request->all());
        $v = $model->validate();
        if(! $v->failed())
            $model->update();

        return view('/customer-office/show', [
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
        $model = \App\CustomerOffice::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
