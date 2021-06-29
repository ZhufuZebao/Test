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

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use DB;
use App\Contractor;
use App\Task;
use App\TaskDepend;
use PDF;

use Session;

/**
 * 工程管理
 */
class GanttController extends \App\Http\Controllers\Controller
{
    /**
     * 工程管理のトップページ
     * method: (GET | POST)
     */
    public function index(Request $request)
    {
        $model = new Task();

        if($request->isMethod('post'))
        {
            $model->fill($request->all());
        } else {
            $model->st_date = date('2018-6-01');
            $model->ed_date = date('Y-m-t');
        }

        return view('/gantt/index2', [
            'model' => $model,
        ]);
    }

    /**
     * 工程管理のトップページ
     * method: POST
     */
    public function multiUpdate(Request $request)
    {
        return view('/gantt/index', [
        ]);
    }

    /**
     * 子タスク追加
     * method: GET
     */
    public function create()
    {
        $model = new Task([
            'name'    => '',
            'st_date' => date('Y-m-d'),
            'ed_date' => date('Y-m-d', time() + 60*60*24),
            'note'    => '',
        ]);

        return view('/gantt/_form', [
            'model' => $model,
        ]);
    }

    /**
     * 子タスク追加
     * method: GET
     */
    public function createChild($id)
    {
        $model  = new Task();

        $model->parent_id = $id;

        return view('/gantt/_form', [
            'model' => $model,
        ]);
    }

    public function edit($id)
    {
        $model = $this->findModel($id);
        $data  = self::jsonify([], $model);

        return view('/gantt/_form', [
            'model' => $model,
            'json'  => json_encode($data),
        ]);
    }

    /**
     * Task Model を返す
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    private function findModel($id)
    {
        $model = Task::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

    /**
     * Task Model の配列をJSONで返す
     * 
     * var $start yyyy-MM-dd
     * var $end   yyyy-MM-dd
     * @return mix
     */
    public function jsonByDate($start, $end)
    {
        $tasks = Task::where('parent_id', null)
                     ->where(function($query)use($start,$end){
                         $query->where(  'st_date', '<=', $end)
                               ->orWhere('ed_date', '=>', $start);
                     })
                     ->orderBy('st_date', 'asc')
                     ->get();

        $rows = [];
        foreach($tasks as $task)
        {
            $rows = self::jsonify($rows, $task);
        }

        return response()->json($rows);
    }

    /**
     * Task Model をJSONで返す
     * 
     * var $id integer
     * @return mix
     */
    public function jsonById($id)
    {
        $task = $this->findModel($id);
        $rows = self::jsonify([], $task);

        return response()->json($rows);
    }

    /**
     * Task Model を配列に追加し、配列を返す
     * 
     * var $rows array
     * var $task Task Model
     * @return array
     */
    private static function jsonify($rows, $task)
    {
        $rows[] = [
            'id'          => $task->id,
            'parent'      => $task->parent_id,
            'name'        => $task->name,
            'actualStart' => (strtotime($task->st_date) + 60*60*9) * 1000, // JST +900
            'actualEnd'   => (strtotime($task->ed_date) + 60*60*9) * 1000, // in milliseconds
            'connectTo'   => $task->connects->implode('dst_id'),
        ];

        foreach($task->children as $child)
        {
            $rows = self::jsonify($rows, $child); // 再帰的に配列へ追加
        }

        return $rows;
    }

    /**
     * method: POST
     */
    function pdf(Request $request)
    {
        $pdf = PDF::loadView('/gantt/pdf', [
            'base64' => $request->base64,
        ],[],[
            'format' => 'A4-L',
        ]);

        return $pdf->stream('document.pdf');
    }

    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/gantt/show', [
            'model' => $model,
        ]);
    }

    /**
     * 新規作成
     * method: POST
     */
    public function store(Request $request)
    {
        $model  = new Task();
        $model->fill($request->all());

        $v = $model->validate();
        if($v->fails())
        {
            return redirect(route('gantt/create'))->withErrors($v)
                                                  ->withInput();
        }

        $model->save();

        return redirect(route('gantt/edit',['id'=>$model->id]));
    }

    /**
     * 新規作成(子タスク)
     * method: POST
     */
    public function storeChild($id, Request $request)
    {
        $model  = new Task(['parent_id'=>$id]);
        $model->fill($request->all());

        $v = $model->validate();
        if($v->fails())
        {
            return redirect(route('gantt/create-child',['id'=>$id]))->withErrors($v)
                                                                    ->withInput();
        }

        $model->save();

        return redirect(route('gantt/edit',['id'=>$model->id]));
    }

    /**
     * method POST
     */
    public function update($id, Request $request)
    {
        $model  = $this->findModel($id);

        $model->fill($request->all());

        $v = $model->validate(); // or throw error
        if(! $v->fails()) {
            $model->update();
        }

        return redirect(route('gantt/edit',['id'=>$id]))
                        ->withErrors($v)
                        ->withInput();
    }

    /**
     * method POST
     */
    public function updateByAjax(Request $request)
    {

        $model = $this->findModel($request->id);
        $attr  = null;

        if("actualStart" == $request->field)
        {
            $model->st_date = $request->value;
        }
        elseif("actualEnd" == $request->field)
        {
            $model->ed_date = $request->value;
        }
        else
        {
            // send error message with HTTP response code 500
        }

        $v = $model->validate(); // or throw error
        if($v->fails())
        {
            return response()->json($v->errors(), 500);
        }
        elseif($model->update())
        {
            return 'updated';
        }

        $json = self::jsonify([], $model);

        return response()->json($json);
    }

}
