<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\JobOfferMessage;
use App\JobOfferMessageSearchModel;

/**
 * 求人メッセージの検索と閲覧
 */
class JobMessageController extends \App\Http\Controllers\Controller
{
    /**
     * method GET | POST
     * @return 200
     */
    public function index()
    {
        $query  = new JobOfferMessageSearchModel();
        $models = $query->search()->paginate($this->getPagesize());

        return view('/job-message/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * 受信箱
     */
    public function inbox(Request $request)
    {
        $query  = new JobOfferMessageSearchModel();
        $query->init($request->all());

        $query->filter = 'inbox';

        $models = $query->search()->paginate($this->getPagesize());
        return view('/job-message/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * 送信箱
     */
    public function outbox(Request $request)
    {
        $query  = new JobOfferMessageSearchModel();
        $query->init($request->all());

        $query->filter = 'outbox';

        $models = $query->search()->paginate($this->getPagesize());
        return view('/job-message/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * 職人に返信する
     * method POST
     * @return 200 OK
     */
    public function reply($id, Request $request)
    {
        $offer = \App\JobOffer::find($id);
	    if(Auth::id() != $offer->vacancy->user_id) { abort(403); }

        $model = new JobOfferMessage();
        $model->vacancy_id = $offer->vacancy_id;
        $model->sender_id  = Auth::id();
        $model->receiver_id= $offer->worker_id;

        $model->fill($request->all()); 
        $v = $model->validate();

        if(false === $v->fails())
        {
            $model->save();
        }

        $hired = $request->input('hired', null);
        if(null !== $hired)
        {
            $offer->hired = $hired;
            $offer->update();
        }

        return redirect()->route('job.messages',['id'=>$offer->vacancy_id,'worker_id'=>$offer->worker_id])
			 ->with(['message' => $v->errors()->first()]);
    }

    /**
     * method POST
     * @return 200 OK
     */
    public function search(Request $request)
    {
        $query = new JobOfferMessageSearchModel();
        $query->init($request->all());
        $models = $query->search()->paginate($this->getPagesize());

        return view('/job-message/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    /**
     * method GET
     * @return 200
     */
    public function show($id)
    {
        $model = $this->findModel($id);

        if(! $model->read_at &&
           (Auth::id() != $model->sender_id))
	{
	    $model->read_at = date('Y-m-d H:i:s'); // 既読にする
	    $model->update();
        }

        return view('/job-message/show', [
            'model' => $model,
        ]);
    }

    /**
     * ある求人について職人が企業にメッセージを送信する
     * method POST
     * @return 200 OK
     */
    public function store($id, Request $request)
    {
        $job   = \App\JobVacancy::find($id);
        $model = new JobOfferMessage();

        $model->vacancy_id = $job->id;
        $model->sender_id  = Auth::id();
        $model->receiver_id= $job->user_id;

        $model->fill($request->all());
        $v = $model->validate();

        if(false === $v->fails())
        {
            $model->save();
        }

        $value = $request->input('accepted', null);
        if(null !== $value)
        {
            $offer = $model->offer;
            $offer->accepted = $value;
            $offer->update();
        }

        return redirect()->route('job.show',['id'=>$id])
			 ->with(['message' => $v->errors()->first()]);
    }

    /**
     * 未読のみ
     */
    public function unread(Request $request)
    {
        $query  = new JobOfferMessageSearchModel();
        $query->init($request->all());

        $query->filter = 'unread';

        $models = $query->search()->paginate($this->getPagesize());
        return view('/job-message/index', [
            'query'  => $query,
            'models' => $models,
        ]);
    }

    private function findModel($id)
    {
        $model = JobOfferMessage::findOrFail($id);

        // validation
        $user_id = Auth::id();
        if(($user_id == $model->sender_id) ||
           ($user_id == $model->receiver_id))
        {
            return $model;
        }

        // valiation failed
        abort(404);
    }

}
