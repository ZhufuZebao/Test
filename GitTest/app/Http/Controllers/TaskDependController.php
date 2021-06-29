<?php
/**
 * 工程管理ページのコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//use Request;
use DB;
use App\Contractor;
use App\TaskDepend;

use Session;

class TaskDependController extends \App\Http\Controllers\Controller
{
    public function createByAjax(Request $request)
    {
        $params = [
            'src_id' => $request->src_id,
            'dst_id' => $request->dst_id,
        ];
        $model = new TaskDepend();
        $model->fill($params);

        $validator = $model->validate();

        if($validator->fails())
            return 'ng';

        $model->save();

        return 'ok';
    }

    public function deleteByAjax(Request $request)
    {
        $model = TaskDepend::where('src_id', $request->src_id)
                           ->where('dst_id', $request->dst_id)
                           ->first();
        if(! $model)
            return 'ng';

        $model->delete();

        return 'ok';
    }

}
