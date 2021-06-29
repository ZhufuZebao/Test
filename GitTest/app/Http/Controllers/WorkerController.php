<?php
/**
 * プロフィールに基づいて職人を検索・表示する
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\UserProfile;
use App\UserProfileSearchModel;

/**
 * 職人検索
 */
class WorkerController extends \App\Http\Controllers\Controller
{
    private $pagination = 10;

    /**
     * method GET | POST
     * @return 200
     */
    public function index(Request $request)
    {
        $query  = new UserProfileSearchModel();
        $query->init($request->all());
        $models = $query->search()->paginate($this->pagination);

        return view('/worker/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * 職人を指名して新しい仕事を作成
     * method GET
     * @return 200 OK
     */
    public function newoffer($id)
    {
        return redirect()->route('job.create',['worker_id'=>$id]);
    }

    /**
     * method GET
     * @return 200
     */
    public function photo($id)
    {
        $model = UserProfile::find($id);

        if(!$model)//TODO
            return response(Storage::get(public_path('/images/user.png')));

        return response(Storage::get($model->photo));
    }

    /**
     * 登録中の仕事をこの職人に指名
     * method POST
     * @return 200 OK
     */
    public function propose($id, Request $request)
    {
        $model = UserProfile::findOrFail($id);

        //TODO
        return "工事中です";

        return redirect()->route('job.edit')->with('worker_id',$id);
    }

    /**
     * method POST
     * @return 200 OK
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }

    /**
     * method GET
     * @return 200 OK
     */
    public function show($id)
    {
        $model = UserProfile::find($id);
        if(! $model)
        {
            $model = new UserProfile();
            $model->user_id = $id;
        }

        return view('/worker/show', [
            'model' => $model,
        ]);
    }

}
