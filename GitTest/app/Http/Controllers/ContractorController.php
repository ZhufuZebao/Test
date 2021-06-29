<?php
/**
 * 工程管理ページのコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use DB;
use App\Contractor;

use Session;

/**
 * 
 */
class ContractorController extends \App\Http\Controllers\Controller
{
    /**
     * method: GET
     */
    public function create()
    {
        $model = Contractor::make(['establishment'=>date('Y-m-d')]);

        return view('/contractor/edit',[
            'model' => $model,
        ]);
    }

    /**
     * method: GET
     */
    public function edit($id)
    {
        $model = $this->findModel($id);

        return view('/contractor/edit',[
            'model' => $model,
        ]);
    }

    /**
     * 工程管理のトップページ
     * method: GET
     */
    public function index()
    {
        $models = Contractor::where('id', '>=', 1)->paginate(10);

        return view('/contractor/index', [
            'models' => $models,
        ]);
    }

    /**
     * method: POST
     */
    public function store(Request $request)
    {
        $model = new Contractor();
        
        $model->fill($request->all());
        $v = $model->validate();

        if(! $v->fails() && $model->save())
            return redirect()->route('contractor.show',['id'=>$model->id]);

        return view('/contractor/edit',[
            'model'  => $model,
            'errors' => $v->errors(),
        ]);
    }

    /**
     * method: POST
     */
    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);
        $model->fill($request->all());
        $v = $model->validate();

        if(! $v->fails() && $model->update())
            return redirect()->route('contractor.show',['id'=>$id]);

        return view('/contractor/edit',[
            'model' => $model,
            'errors' => $v->errors(),
        ]);
    }

    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/contractor/show', [
            'model' => $model,
        ]);
    }

    /**
     * Project Model を返す
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    private function findModel($id)
    {
        $model = Contractor::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
