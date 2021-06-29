<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\JobOffer;
use App\JobOfferMessage;
use App\JobVacancy;
use App\JobSearchModel;

/**
 * 求人管理
 * 誰でも 一覧・検索 できる
 * 企業は 追加・編集・指名 できる
 * 職人は 応募 できる
 */
class JobController extends \App\Http\Controllers\Controller
{
    /**
     * 登録中の仕事
     * method GET | POST
     * @return 200
     */
    public function current()
    {
        $query  = new JobSearchModel();
        $query->posted = true;
        $models = $query->search()
                        ->paginate($this->getPagesize());

        return view('/job/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * 過去の仕事
     * method GET | POST
     * @return 200
     */
    public function history(Request $request)
    {
        $query  = new JobSearchModel();
        $query->past = 1;
        $query->fill($request->all());
        $models = $query->search()
                        ->where('user_id',Auth::id())
                        ->paginate($this->getPagesize());

        return view('/job/history', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * method GET | POST
     * @return 200
     */
    public function index()
    {
        $query  = new JobSearchModel();
        $models = $query->search()->paginate($this->getPagesize(5));
        $user_id= Auth::id();
        $profile= \App\UserProfile::findOrNew($user_id);

        return view('/job/dashboard', [
            'profile'=> $profile,
            'query'  => $query,
            'models' => $models,
            'projects'=>[],
            'reports'=>[],

        ]);
    }

    /**
     * 職人が、この求人に応募する
     * method POST
     * return 200
     */
    public function propose($id, Request $request)
    {
        $model   = $this->findModel($id);
        $user_id = Auth::id();

        if(true == $model->offers()->where('worker_id', $user_id)->exists())
        {
            abort(404, "応募済みです");
        }

        if($this->createOffer($model, $user_id, $user_id))
        {
            $request->session()->push('msg.success',"求人「{$model->name}」へ応募しました");
            return redirect()->route('job.show',['id'=>$id]);
        }

        return redirect()->route('job.show',['id'=>$id]);
    }


    private function createOffer($model, $worker_id, $user_id)
    {
        $offer = new JobOffer();
        $offer->vacancy_id = $model->id;
        $offer->worker_id  = $worker_id;
        $offer->created_by = $user_id;

        $mesg  = new JobOfferMessage();
        $mesg->fill(request()->all());
        $mesg->vacancy_id  = $model->id;
        $mesg->sender_id   = $user_id;
        $mesg->receiver_id = ($user_id != $worker_id) ? $worker_id : $model->user_id;

        DB::beginTransaction();

        if(! $offer->save() || ! $mesg->save() )
        {
            DB::rollback();
            return false;
        }

        DB::commit();

        return true;
    }

    /**
     * method POST
     * @return 200 OK
     */
    public function search(Request $request)
    {
        $query = new JobSearchModel();
        $query->init($request->all());
        $query->fill($request->all());

        $models = $query->search()->paginate($this->getPagesize());

        return view('/job/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    public function show($id)
    {
        $model = $this->findModel($id);
        $query = $model->messages();

        if(Auth::id() != $model->user_id)
        {
            //ユーザはこの求人の所有者ではないので、閲覧に制限をかける
            return redirect()->route('job.messages',['id'=>$id,'worker_id'=>Auth::id()]);
        }

        $mails = $query->orderBy('id','DESC')->paginate();//全件表示

        return view('/job/show', [
            'model' => $model,
            'mails' => $mails,
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
        $user_id = Auth::id();
        $model   = JobVacancy::find($id);

        if(! $model)
            abort(404, "見つかりません");

        $match = $action = null;
        if(preg_match('/@([a-z]+$)$/', \Route::currentRouteAction(), $match))
        {
            $action = array_pop($match);
        }

        if('show' == $action)
        {
            if(false == $model->isAccesible($user_id))
                abort(403, "許可がありません");
        }
        elseif('propose' == $action)
        {
            // 公開中の ID のみ応募可能
            if(false == $this->isActive())
                abort(403, "許可がありません");
        }

        return $model;
    }

}
