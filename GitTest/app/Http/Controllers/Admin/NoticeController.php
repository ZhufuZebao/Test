<?php
/**
 *お知らせ
 */

namespace App\Http\Controllers\Admin;


use App\Models\SysNotice;
use App\Models\SysNoticeAlreadyRead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NoticeController extends \App\Http\Controllers\Controller
{
    private $pagination = 10;

    protected function getPagesize($default = 30)
    {
        return Input::get('pagesize', $default);
    }

    /**
     * お知らせに一覧を取得
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNoticeList(Request $request)
    {
        $type = $request->get('type');
        if ($type){
            $model = SysNotice::paginate($this->getPagesize($this->pagination))->toArray();
        }else{
            $nowtime = date("Y-m-d");
            $model = SysNotice::where('st_date', '<=', $nowtime)
                ->where('ed_date', '>=', $nowtime)
                ->orderBy('st_date', 'desc')
                ->get()->toArray();

            $noticeIds = [];
            foreach ($model as $k =>$item)
            {
                //htmlタグトランスコード
                //ラベルを削除
                $model[$k]['content'] = $item['content'];

                //get notice_id
                $noticeIds[] = $item['id'];
            }

            $alreadyReadIds = SysNoticeAlreadyRead::whereIn('sys_notice_id',
                $noticeIds)->where('user_id', Auth::id())->pluck('sys_notice_id')->toArray();

            foreach ($model as $k =>$item)
            {
                if (in_array($model[$k]['id'],$alreadyReadIds)){
                    $model[$k]['alreadyRead'] = true;
                } else {
                    $model[$k]['alreadyRead'] = false;
                }
            }
        }
        return $model;
    }

    /**
     * お知らせを新規
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNotice(Request $request)
    {
        $data = $request->input('notice');
        $model = new SysNotice();
        try {
            $model->st_date = date('Y-m-d H:i:s', strtotime($data['st_date']));
            $model->ed_date = date('Y-m-d H:i:s', strtotime($data['ed_date']));
            $model->title = $data['title'];
            $model->content = $data['content'];//htmlタグトランスコード
            $validate = $model->validate();
            if ($validate->fails()) {
                return $this->json($validate->errors()->all());
            }else{
                $model->save();
                return $this->json();
            }
        } catch (\Exception $e) {
            Log::error($e);
            return $this->json("登録中にエラーが発生しました");
        }

    }

    /**
     * お知らせdetailを取得
     * @param Request $request
     *
     * @return mixed
     */
    public function getNoticeDetail(Request $request)
    {
        $id = $request->input('id');
        $model = SysNotice::where('id',$id)->first();
        return $model;

    }

    /**
     * お知らせを編集
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editNotice(Request $request)
    {
        $id = $request->input('id');
        $data = $request->input('notice');

        try {
            $model = SysNotice::find($id);
            $model->st_date = date('Y-m-d H:i:s', strtotime($data['st_date']));
            $model->ed_date = date('Y-m-d H:i:s', strtotime($data['ed_date']));
            $model->title = $data['title'];
            $model->content = $data['content'];//htmlタグトランスコード
            $model->save();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->json("変更中にエラーが発生しました");
        }
        return $this->json();
    }

    /**
     * キーワード検索
     * @param Request $request
     *
     * @return mixed
     */
    public function searchNotice(Request $request)
    {
        $keyword = $request->input('keyword');
        $keyword = htmlentities($keyword);      //keywordをフィルタ
        $keyword = htmlspecialchars($keyword);  //keywordをフィルタ

        $model = SysNotice::where(function ($query) use ($keyword) {
                $query->orWhere('title', 'LIKE', "%{$keyword}%");
                $query->orWhere('content', 'LIKE', "%{$keyword}%");
            })
            ->paginate($this->getPagesize($this->pagination))->toArray();

        foreach ($model['data'] as $k =>$item)
        {
            //htmlタグトランスコード
            //ラベルを削除
            $model['data'][$k]['content'] =$item['content'];
        }
        return $model;
    }
}
