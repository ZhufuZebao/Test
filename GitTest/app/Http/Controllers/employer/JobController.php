<?php
namespace App\Http\Controllers\Employer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\JobOffer;
use App\JobOfferMessage;
use App\JobVacancy;
use App\JobSearchModel;

/**
 * 求人管理
 * 企業が 一覧・検索・詳細・追加・編集・指名 できる
 */
class JobController extends \App\Http\Controllers\Controller
{
    /**
     * 求人を停止する
     * @return 200 | 404 Not found | 403 Forbidden
     */
    public function close(Request $request, $id)
    {
        $model  = $this->findModel($id);

        if($model->status->isClosed())
        {
            $request->session()->flash('msg-error',"すでに停止しています");
        }

        $model->close();

        $model->update();
        $request->session()->flash('msg-success',"停止しました");
        
        return redirect()->route('job.show', $model->id);
    }

    /**
     * 
     * @return 200 | 404 Not found
     */
    public function copy($id)
    {
        $orig  = $this->findModel($id);
        $model = JobVacancy::make($orig->getAttributes());
        
        return view('/employer/job/edit', [
            'model' => $model,
        ]);
    }

    /**
     * method GET
     * @return 200 | 404 Not found
     */
    public function create(Request $request)
    {
        $model = new JobVacancy();
        $offer = new JobOffer();

        if($request->input('worker_id', 0))
        {
            $offer->worker_id = $request->get('worker_id');
        }

        return view('/employer/job/edit', [
            'model' => $model,
            'offer' => $offer,
        ]);
    }

    /**
     * var $id integer
     * @return Model | 404 Not found
     */
    public function edit($id)
    {
        //TODO: 自分に所有権がある ID のみ編集可能
        $model = $this->findModel($id);
        $offer = new JobOffer();

        return view('/employer/job/edit', [
            'model' => $model,
            'offer' => $offer,
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

        return view('/employer/job/index', [
            'profile'=> $profile,
            'query'  => $query,
            'models' => $models,
            'projects'=>[],
            'reports'=>[],
            
        ]);
    }

    /**
     * ある求人について、ある職人とのメッセージ履歴を表示
     * method GET | POST
     * @return 200 | 403
     */
    public function messages($id, $worker_id)
    {
        $model = $this->findModel($id);

        if(! in_array(Auth::id(), [$model->user_id, $worker_id]))
        {
            // 自分が（所有する｜投稿した）求人のみ閲覧可能
            abort(403, "許可がありません");
        }

        $offer = $model->offers()->where('worker_id','=',$worker_id)->first();
        $mails = $offer->messages()->orderBy('id','DESC')->paginate();

        foreach($offer->messages as $msg)
        {
            if($msg->read_at){ continue; }

            $msg->read_at = DB::raw('NOW()');
            $msg->update();//既読にする
        }

        return view('/job/messages', [
            'model' => $model,
            'offer' => $offer,
            'mails' => $mails,//未読フラグが残っている
        ]);
        
    }

    /**
     * 求人を公開する
     * method GET
     * @return 200 | 404 Not found | 403 Forbidden
     */
    public function open(Request $request, $id)
    {
        $model  = $this->findModel($id);

        if($model->status->isOpen())
        {
            $request->session()->flash('msg-error',"すでに公開中です");
        }
        $model->open();

        $model->update();
        $request->session()->flash('msg-success',"公開しました");
        
        return redirect()->route('job.show', $model->id);
    }

    /**
     * 企業が、ある職人に対して、ある求人を指定して指名する
     * method GET | POST
     * return 200 304
     */
    public function recruit($worker_id, \App\Http\Requests\JobOfferCreateRequest $request)
    {
        //TODO: 終了した ID は応募不可
        $worker = \app\User::findOrFail($worker_id);

        $id = $request->input('vacancy_id');

        if($id)
        {
            $model = $this->findModel($id);

            if(true == $model->offers()->where('worker_id', $worker_id)->exists())
            {
                abort(404, "この求人に同一の職人を指名済みです");
            }

            if($this->createOffer($model, $worker_id, Auth::id()))
            {
                $request->session()->push('msg.success',"求人「{$model->name}」へ応募しました");
                return redirect()->route('job.messages',['id'=>$id,'worker_id'=>$worker_id]);
            }
        }
        
        $jobs = \App\JobVacancy::where('user_id','=',Auth::id())
                               ->where('ed_date','>',\DB::raw('NOW()'));

        return view('/employer/job/recruit', [
            'worker' => $worker,
            'jobs'   => $jobs->paginate(1000),//paginate移動中にtextareaが消える不具合があるので
        ]);
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

        return view('/employer/job/index', [
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

        return view('/employer/job/show', [
            'model' => $model,
            'mails' => $mails,
        ]);
    }

    public function store(Request $request)
    {
        $model = new JobVacancy();
        $offer = new JobOffer();
        $mesg  = new JobOfferMessage();

        $model->fill($request->all());
        $model->user_id = Auth::id();
        $v1 = $v2 = $model->validate();

        if($request->input('worker_id', 0))
        {
            $offer->worker_id = $request->get('worker_id');
            $mesg->fill($request->all());
            $v2 = $mesg->validate();
        }

        if(! $v1->fails() && (0 == $offer->worker_id) || ! $v2->fails()) // validation success
        {
            $model->save();
            
            if($offer->worker_id)
            {
                $offer->save();
                $mesg->save();
            }

            $request->session()->flash('msg-success',"保存しました");
            return redirect()->route('employer.job.show', $model->id);
        }

        return view('/employer/job/edit', [
            'model'  => $model,
            'offer'  => $offer,
            'errors' => $v1->errors(),
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
            $request->session()->flash('msg-success',"保存しました");
            return redirect()->route('job.show', $model->id);
        }

        return view('/employer/job/edit', [
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
        elseif(in_array($action, ['edit','update','recruit','open','close','copy']))
        {
            if($user_id != $model->user_id) // 自分が作成した求人かどうか
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
