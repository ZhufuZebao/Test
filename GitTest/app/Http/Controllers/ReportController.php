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
use App\Report;
use App\ReportSearchModel;
use Session;

/**
 * 日報管理
 */
class ReportController extends \App\Http\Controllers\Controller
{
    private $pagination = 10;

    /**
     * 
     * @return 200 | 404 Not found
     */
    public function create()
    {
        $model = new Report();
        $model->user_id = Auth::user()->id;

        return view('/report/edit', [
            'model' => $model,
        ]);
    }

    /**
     * method GET
     * var $id integer
     * @return 200 OK | 404 Not found
     */
    public function edit($id)
    {
        $model = $this->findModel($id);

        return view('/report/edit', [
            'model' => $model,
        ]);
    }

    public function index()
    {
        $models = Report::where('id','>=',1)->paginate(15);
        $query  = new ReportSearchModel();

        return view('/report/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * method POST
     * @return 200 OK
     */
    public function search(Request $request)
    {
        $query = new ReportSearchModel();
        $query->init([
            'keyword' => $request->get('keyword'),
        ]);
        $models = $query->search()->paginate($this->pagination);

        return view('/report/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/report/show', [
            'model' => $model,
        ]);
    }

    public function store(Request $request)
    {
        $model = new Report();
        $model->user_id = Auth::user()->id;
        $model->log_date= date('Y-m-d');

        $model->fill($request->all());
        $v = $model->validate();
        if(! $v->failed())
            $model->save();

        return view('/report/show', [
            'model' => $model,
        ]);
    }

    public function update($id, Request $request)
    {
        $model = $this->findModel($id);

        $model->fill($request->all());
        $v = $model->validate();
        if(! $v->failed())
            $model->update();

        return view('/report/show', [
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
        $model = \App\Report::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
