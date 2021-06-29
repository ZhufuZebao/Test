<?php
/**
 * 工程管理ページのコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use DB;
use App\Contractor;
use App\Project;
use App\Exports\Array2Excel;
use Excel;
use PDF;

use Session;

/**
 * 工程管理
 */
class TaskController extends \App\Http\Controllers\Controller
{
    /**
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    public function create($id)
    {
        $model = $this->findModel($id);

        if($model->tasks()->exists())
            return redirect(route('task.edit',['id'=>$id]));

        $tasks = $model->schemes;//TODO

        return view('/task/edit', [
            'model' => $model,
            'tasks' => $tasks,
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

        $tasks = $model->tasks;

        return view('/task/edit', [
            'model' => $model,
            'tasks' => $tasks,
        ]);
    }

    public function json($id)
    {
        $model = $this->findModel($id);

        $data = self::jsonify($model->tasks);

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * method: POST
     */
    public function excel($id)
    {
        $project = $this->findModel($id);
        $data    = [];

        foreach($project->tasks as $model)
        {
            $data[] = [
                $model->name,
                $model->st_date,
                $model->ed_date,
            ];
        }
        $header = ["名称","開始日","終了日"];

        return Excel::download(new Array2Excel($header, $data), 'sample.xls');
    }

    /**
     * method: POST
     */
    public function pdf(Request $request, $id)
    {
        $model = $this->findModel($id);

        $pdf = PDF::loadView('/scheme/pdf', [
            'model'  => $model,
            'base64' => $request->base64,
        ],[],[
            'format' => 'A4-L',
        ]);

        return $pdf->stream('document.pdf');
    }

    private static function jsonify($tasks)
    {
        $data = [];

        foreach($tasks as $task)
        {
            $data[] = [
                'id' => $task->id,
                'start_date' => $task->st_date,
                'end_date'   => $task->ed_date,
                'duration'   => floor((strtotime($task->ed_date) - strtotime($task->st_date)) / (60 * 60 * 24)),
                'text'       => $task->name,
                'progress'   => 0,
                'parent'     => $task->parent_id,
                'open'       => false,
                'planned_start' => $task->st_date . ' 00:00:00', // div class="baseline"
                'planned_end'   => $task->ed_date . ' 00:00:00', // 
            ];
        }
        
        return $data;
    }

    /**
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    public function gantt($id)
    {
        $model = $this->findModel($id);

        return view('/task/gantt', [
            'model' => $model,
        ]);
    }

    public function ganttEdit($id)
    {
        $model = $this->findModel($id);

        return view('/task/gantt', [
            'model' => $model,
        ]);
    }

    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/task/show', [
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
        $model = \App\Project::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }
    /**
     * DTHMLX API からの PUT/POSTを取り出す
     * なぜこんな面倒なことをしているか：
     * https://github.com/laravel/framework/issues/13457#issuecomment-341973180
     */
    private static function parseInputForTaskModel()
    {
        $params = [];
        $input = file_get_contents('php://input');
        $input = urldecode($input);
        $input = explode('&', $input);

        foreach($input as $buf)
        {
            $chunk = explode('=', $buf);
            $value = array_pop($chunk);
            $key   = array_pop($chunk);

            if('start_date' == $key)
                $key = 'st_date';

            if('parent' == $key)
                $key = 'parent_id';

            if('text' == $key)
                $key = 'name';

            $params[$key] = $value;
        }

        if(($start = array_get($params, 'st_date')) &&
           ($duration = array_get($params, 'duration')))
        {
            $params['ed_date'] = date('Y-m-d H:i:s', strtotime($start) + ($duration * 60 * 60 * 24));
        }
        
        return $params;
    }

    /**
     * method: POST
     * see also https://docs.dhtmlx.com/gantt/desktop__server_side.html#techniqe
     */
    public function apiCreate($pid)
    {
        $params = $this->parseInputForTaskModel();

        $model = new \App\Task();
        $model->project_id = $pid;
        $model->fill($params);

        if(0 == $model->parent_id)
        {
            $model->weight = 1 + \App\Task::where('project_id', $pid)->max('weight');
        }
        
        $v = $model->validate();

        if(! $v->failed() && $model->save())
            return response()->json([
                'action' => 'inserted',
                'tid'    => $model->id,
            ]);

        return response()->json([
            'action' => 'error',
            'tid'    => $model->id,
        ]);
    }

    /**
     * method: DELETE
     */
    public function apiDelete($pid, $id)
    {
        $model = \App\Task::where('project_id', $pid)
                            ->where('id', $id);
        $model->delete();
        
        return response()->json([
            'action' => 'deleted',
        ]);
    }

    /**
     * method: POST
     * 順位の変更
     */
    public function apiMove(Request $request, $pid)
    {
        $itemId = $request->get('itemId');
        $prevId = $request->get('prevId');

        $item = \App\Task::where('id',$itemId)->where('project_id',$pid)->first();
        $prev = \App\Task::where('id',$prevId)->where('project_id',$pid)->first();

        if(! $item){ abort(403); }

        if($prev)
        {
            \App\Task::where('project_id',   $prev->project_id)
                     ->where('parent_id',    $prev->parent_id)
                     ->where('weight', '>=', $prev->weight)
                     ->where('id',     '<=', $prev->id)
                     ->whereNotIn('id', [$item->id])
                     ->update([
                         'weight' => DB::raw('weight + 2'),
                     ]);
            $prevWeight = \App\Task::where('id',$prev->id)->value('weight');
            $item->update(['weight' => $prevWeight]);

            $item->weight = $prevWeight -1;
            $item->update();
        }
        else
        {
            $maxWeight = \App\Task::where('project_id',$pid)
                                  ->where('parent_id', $item->parent_id)
                                  ->max('weight');
            $item->weight = $maxWeight +1;
            $item->update();
        }
        
        return response("item $itemId moved next to $prevId");
    }

    /**
     * method: PUT
     */
    public function apiUpdate($pid, $id)
    {
        $params = $this->parseInputForTaskModel();

        $model = \App\Task::where('project_id', $pid)
                            ->where('id', $id)
                            ->first();
        $model->fill($params);

        $v = $model->validate();

        if($v->failed())
            return response()->json([
                'action' => 'error',
                'tid'    => $id,
            ]);

        if(! $model->update())
            return response()->json([
                'action' => 'error',
                'tid'    => $id,
                'model'  => $model->attributes,
            ]);

        return response()->json([
            'action' => 'updated',
            'tid'    => $id,
            'req'    => $params,
        ]);
    }

}
