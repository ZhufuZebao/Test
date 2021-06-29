<?php
/**
 * \App\UserProfile のコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UserProfileFormRequest;

use App\UserProfile;

/**
 * profile - 自分のプロファイルを編集する
 */
class UserProfileController extends \App\Http\Controllers\Controller
{
    /**
     * ユーザが自分のプロファイルを編集する
     * method GET
     * @return 200 OK
     */
    public function edit()
    {
        $model = $this->findModel();

        return view('/profile/edit', [
            'model' => $model,
        ]);
    }

    /**
     * ユーザが自分のプロファイルをプレビューする
     * method GET
     * @return 200 OK
     */
    public function index()
    {
        $model = $this->findModel();

        return view('/profile/index', [
            'model' => $model,
        ]);
    }

    /**
     * ユーザが自分の写真を閲覧する
     * method GET
     * @var $id integer
     * @return 200 OK
     */
    public function photo()
    {
        $model = $this->findModel();

        if(! $model->photo)
            abort(404, "見つかりません");

        return response(Storage::get($model->photo));
    }

    /**
     * ユーザが自分のプロファイルを更新する
     * method POST
     * @return 200 OK
     */
    public function update(UserProfileFormRequest $request)
    {
        $model = $this->findModel();
        $model->fill($request->all());

        if($basename = $this->updateFileIfExist($request))
        {
            $model->photo = $basename;
        }
        $v = $model->validate();

        if(false === $v->fails())
        {
            $request->session()->flash('msg-success',"プロファイルを更新しました");
            $model->save();
        }

        return view('/profile/edit', [
            'model'  => $model,
            'errors' => $v->errors(),
        ]);
    }

    private function updateFileIfExist($request)
    {
        if(! $file = $request->file('image'))
        {
            return null;
        }

        $basename = $file->store(UserProfile::PHOTO_PATH);

        return $basename;
    }

    /**
     * Model を返す
     * 
     * @return Model | 500 Server Error
     */
    private function findModel()
    {
        $id    = Auth::id();
        $model = UserProfile::findOrNew($id);

        if(null === $model->id)
            $model->id = $id;

        if(! $model)
            abort(500, "システムエラー: user_profile.{$id} not found");

        return $model;
    }

}
