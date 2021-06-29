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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use DB;
use App\Contractor;
use App\Project;
use App\ProjectSearchModel;
use App\Http\Requests\ProjectFormRequest;
use Excel;
use PDF;

use Session;

/**
 * 工程管理
 */
class ProjectController extends \App\Http\Controllers\Controller
{
    private $pagination = 10;

    /**
     * 工程管理のトップページ
     * method: GET
     */
    public function index()
    {
        $models = Project::where('id', '>=', 1)->paginate($this->pagination);
        $query  = new ProjectSearchModel();

        return view('/project/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * method GET
     * @return 200 OK | 404 Not found
     */
    public function create()
    {
        $model = new Project();

        return view('/project/edit', [
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

        return view('/project/edit', [
            'model' => $model,
        ]);
    }

    /**
     * method GET
     * var $id integer
     * @return 200 OK | 404 Not found
     */
    public function gantt($id)
    {
        $model = $this->findModel($id);

        return view('/project/gantt', [
            'model'  => $model,
            'target' => 'all',
        ]);
    }

    /**
     * この案件にアクセス権限がある場合にのみ、画像を表示する
     *
     * method GET
     * var $id integer
     * @return 200 OK | 404 Not found
     */
    public function photo($id)
    {
        $model = $this->findModel($id);

        if(! $model->photo)
            abort(404, "見つかりません");

        return response(Storage::get($model->photo));
    }

    /**
     * method POST
     * @return 200 OK
     */
    public function search(Request $request)
    {
        $query = new ProjectSearchModel();
        $query->init([
            'id'      => $request->get('id'),
            'date'    => $request->get('date'),
            'keyword' => $request->get('keyword'),
            'location'=> $request->get('location'),
        ]);
        $models = $query->search()->paginate($this->pagination);

        return view('/project/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * method GET
     * var $id integer
     * @return 200 OK | 404 Not found
     */
    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/project/show', [
            'model' => $model,
        ]);
    }

    /**
     * method POST
     * @return 200 OK | 404 Not found
     */
    public function store(ProjectFormRequest $request)
    {
        $model = new Project();
        $model->fill($request->all());

        if($basename = $this->updateFileIfExist($request))
        {
            $model->photo = $basename;
        }

        if($model->save())
        {
            return redirect()->route('project.show',['id'=>$model->id]);
        }
        
        return view('/project/edit', [
            'model' => $model,
        ]);
    }

    /**
     * method POST
     * var $id integer
     * @return 200 OK | 404 Not found
     */
    public function update($id, ProjectFormRequest $request)
    {
        $model = $this->findModel($id);
        $model->fill($request->all());

        if($basename = $this->updateFileIfExist($request))
        {
            $model->photo = $basename;
        }

        if($model->save())
        {
            return redirect()->route('project.show',['id'=>$model->id]);
        }
        
        return view('/estate/edit', [
            'model' => $model,
        ]);
    }

    private function updateFileIfExist($request)
    {
        if(! $file = $request->file('image'))
        {
            return null;
        }

        $basename = $file->store(Project::PHOTO_PATH);

        return $basename;
    }

    /**
     * Project Model を返す
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    private function findModel($id)
    {
        $model = Project::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
