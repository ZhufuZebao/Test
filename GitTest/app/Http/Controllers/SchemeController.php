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
use App\Scheme;
use App\Exports\Array2Excel;
use Excel;
use PDF;

use Session;

/**
 * 工程管理
 */
class SchemeController extends \App\Http\Controllers\Controller
{
    /**
     * 
     * var $id integer
     * @return HTTP 200 | 404 Not found
     */
    public function edit($id)
    {
        $model = Project::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return view('/scheme/edit', [
            'model' => $model,
        ]);
    }

    /**
     * method: POST
     */
    public function excel($id)
    {
        $project = Project::find($id);
        $data    = [];

        foreach($project->schemes as $model)
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
     * @return HTTP 200
     */
    public function index()
    {
        $models = \App\Scheme::where('id', '>=', 1)->paginate(15);

        if(! $models)
            abort(404, "見つかりません");

        return view('/scheme/index', [
            'models' => $models,
        ]);
    }

    public function json($id)
    {
        $model = Project::find($id);

        if(! $model)
            abort(404, "見つかりません");

        $data = self::jsonify($model->schemes);

        return response()->json([
            'data' => $data
        ]);
    }

    private static function jsonify($schemes)
    {
        $data = [];

        foreach($schemes as $scheme)
        {
            $data[] = [
                'id' => $scheme->id,
                'start_date' => $scheme->st_date,
                'end_date'   => $scheme->ed_date,
                'duration'   => floor((strtotime($scheme->ed_date) - strtotime($scheme->st_date)) / (60 * 60 * 24)),
                'text'       => $scheme->name,
                'progress'   => 0,
                'parent'     => $scheme->parent_id,
                'open'       => false,
                'planned_start' => $scheme->st_date . ' 00:00:00', // div class="baseline"
                'planned_end'   => $scheme->ed_date . ' 00:00:00', // 
            ];

            if($children = $scheme->children)
            {
                $rows = self::jsonify($children);//再帰的に追加
                foreach($rows as $row)
                {
                    $data[] = $row;
                }
            }
        }
        
        return $data;
    }

    /**
     * method: POST
     */
    public function pdf(Request $request, $id)
    {
        $model = Project::find($id);

        $pdf = PDF::loadView('/scheme/pdf', [
            'model'  => $model,
            'base64' => $request->base64,
        ],[],[
            'format' => 'A4-L',
        ]);

        return $pdf->stream('document.pdf');
    }

    /**
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    public function gantt($id)
    {
        $model = Project::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return view('/scheme/gantt', [
            'model'  => $model,
        ]);
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

        $model = new \App\Scheme();
        $model->project_id = $pid;
        $model->fill($params);

        if(0 == $model->parent_id)
        {
            $model->weight = 1 + \App\Scheme::where('project_id', $pid)->max('weight');
        }

        $v = $model->validate();

        if(! $v->fails() && $model->save())
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
        $model = \App\Scheme::where('project_id', $pid)
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

        $item = \App\Scheme::where('id',$itemId)->where('project_id',$pid)->first();
        $prev = \App\Scheme::where('id',$prevId)->where('project_id',$pid)->first();

        if(! $item){ abort(403); }

        if($prev)
        {
            \App\Scheme::where('project_id',   $prev->project_id)
                       ->where('parent_id',    $prev->parent_id)
                       ->where('weight', '>=', $prev->weight)
                       ->where('id',     '<=', $prev->id)
                       ->whereNotIn('id', [$item->id])
                       ->update([
                           'weight' => DB::raw('weight + 2'),
                       ]);
            $prevWeight = \App\Scheme::where('id',$prev->id)->value('weight');
            $item->update(['weight' => $prevWeight]);

            $item->weight = $prevWeight -1;
            $item->update();
        }
        else
        {
            $maxWeight = \App\Scheme::where('project_id',$pid)
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

        $model = \App\Scheme::where('project_id', $pid)
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
