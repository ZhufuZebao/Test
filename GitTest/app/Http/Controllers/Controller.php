<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class Controller extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    protected function getPagesize($default = 30)
    {
        return Input::get('pagesize', $default);
    }

    protected function error($e,$errors = ['登録中にエラーが発生しました'])
    {
        Log::error($e);
        return $this->json($errors);
    }

    protected function json($errors = [], $params = [], $status = 200)
    {
        $data = [];
        if (!empty($errors)) {
            $data['result'] = 1;
            $data['errors'] = $errors;
        } else {
            $data['result'] = 0;
        }
        if (!empty($params)) {
            $data['params'] = $params;
        }
        return response()->json($data, $status);
    }
    /**
     * カスタムのjsonフォーマットに戻る
     */
    protected function Response($head,$message,$data){
        if (!empty($message)){
            return response()->json(['message'=>$message], $head,['status' => $head]);
        } else {
            return response()->json($data, $head,['status' => $head]);
        }
    }

}
