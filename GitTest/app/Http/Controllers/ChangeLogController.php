<?php
/**
 * ChangeLogのコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use DB;
use App\ChangeLog;
use Session;

/**
 * 変更履歴閲覧
 */
class ChangeLogController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $models = ChangeLog::where('id','>=',1)->paginate(50);

        return view('/change-log/index', [
            'models' => $models,
        ]);
    }

    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/change-log/show', [
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
        $model = ChangeLog::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
