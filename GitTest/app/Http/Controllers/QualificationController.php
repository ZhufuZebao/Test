<?php
/**
 * \App\Qualification のコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Qualification;
use App\QualificationSearchModel;

/**
 * Admin専用 - 資格テーブルの閲覧と修正
 */
class QualificationController extends \App\Http\Controllers\Controller
{
    private $pagination = 30;

    /**
     * 
     * @return 200 | 404 Not found
     */
    public function create()
    {
        $model = new Qualification();

        return view('/qualification/edit', [
            'model' => $model,
        ]);
    }

    /**
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    public function edit($id)
    {
        $model = $this->findModel($id);

        return view('/qualification/edit', [
            'model' => $model,
        ]);
    }

    /**
     * method GET | POST
     * @return 200
     */
    public function index()
    {
        $query  = new QualificationSearchModel();
        $models = $query->search()->paginate($this->pagination);

        return view('/qualification/index', [
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
        $query = new QualificationSearchModel();
        $query->init($request->all());

        $models = $query->search()->paginate($this->pagination);

        return view('/qualification/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/qualification/show', [
            'model' => $model,
        ]);
    }

    public function store(Request $request)
    {
        $model = new Qualification();

        $model->fill($request->all());
        $v = $model->validate();

        if(false === $v->fails())
        {
            $model->update();
            return redirect()->route('qualification.show', $model->id);
        }

        return view('/qualification/edit', [
            'model'  => $model,
            'errors' => $v->errors(),
        ]);
    }

    public function update($id, Request $request)
    {
        $model = $this->findModel($id);

        $model->fill($request->all());
        $v = $model->validate();

        if(false === $v->fails())
        {
            $model->update();
            return redirect()->route('qualification.show', $model->id);
        }

        return view('/qualification/edit', [
            'model'  => $model,
            'errors' => $v->errors(),
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
        $model = \App\Qualification::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
