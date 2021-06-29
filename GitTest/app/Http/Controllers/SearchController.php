<?php
/**
 * 求人検索ページのコントローラー
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
use App\Work;
use App\User;
use App\Applicant;

class SearchController extends \App\Http\Controllers\Controller
{
    /**
     * 求人検索のトップページ
     *
     */
    public function index()
    {
        return view('/search/index');
    }

    /**
     * 求人検索結果ページ
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function search(Request $request)
    {
        $input = [
            'keyword'   => $request->input('keyword'),
            'place'     => $request->input('place'),
        ];

        $result = $this->validator($input);

        $request->flash();

        $data = Work::getList($input['keyword'], $input['place']);

        return view('/search/result', [
            'input' => $input,
            'data'  => $data,
        ]);
    }

    /**
     * 求人情報の詳細ページ
     *
     * @param   int     $id     求人ID
     */
    public function jobdetails($id)
    {
        $data = Work::getJobDetails($id);

        return view('/search/jobdetails', [
            'id'    => $id,
            'data'  => $data[0]
        ]);
    }

    /**
     * 求人に応募するページ
     *
     * @param   int     $id     求人ID
     */
    public function apply($id)
    {
        if (\Auth::check()) {

            $data = Work::getJobDetails($id);

            $user = new User;
            $userData = $user->where('id', Auth::id())->first();

            return view('/search/apply', [
                'id'    => $id,
                'user'  => $userData
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * 応募入力内容の確認ページ
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function confirm(Request $request)
    {
        if (\Auth::check()) {

            $validator = $this->validator2($request->all())->validate();

            $request->flash();

            return view('/search/confirm', [
                    'id'   => $request->input('id'),
                    'data' => $request->all()
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * 応募入力内容をデータベースに登録する
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function regist(Request $request)
    {
        if (\Auth::check()) {

            $data = [
                'job_id'   => $request->input('id'),
                'user_id'  => Auth::id(),
                'name'     => $request->old('name'),
                'telno1'   => $request->old('telno1'),
                'telno2'   => $request->old('telno2'),
                'notes'    => $request->old('notes')
            ];
//exit();

            $validator = $this->validator2($data)->validate();

            $this->create($data);
            return redirect('/applycomplete/id/'. $request->input('id'));

        } else {
            return redirect('/login');
        }
    }

    /**
     * 応募完了ページ
     *
     * @param   int     $id     求人ID
     */
    public function complete($id)
    {
        if (\Auth::check()) {

            $applicant = new Applicant;
            $data = $applicant->where('job_id', $id)->where('user_id', Auth::id())->first();

            return view('/search/complete', [
                    'data' => $data
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * 求人検索の検索キーワード入力検証
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
                'keyword' => 'required|max:1000',
                'place'   => 'required|max:190',
        ]);

        return $validator;
    }

    /**
     * 応募内容の入力検証
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator2(array $data)
    {
        $validator = Validator::make($data, [
                'name'     => 'required|max:100',
                'telno1'   => 'telno',
                'telno2'   => 'mobileno',
                'notes'    => 'max:1000',
        ], [
                'name.required'     => '名前を入力してください。',
                'name.max'          => '名前は全角50文字以内で入力してください。',
                'telno1.telno'      => '電話番号は13文字以内で正しく入力してください。',
                'telno2.mobileno'   => '携帯電話番号を正しく入力してください。',
                'notes.max'         => '質問・自己PR・備考は全角500文字以内で入力してください。',
        ]);

        return $validator;
    }

    /**
     * 応募内容をデータベースに登録する
     *
     * @param   array   $data   応募内容
     */
    protected function create(array $data)
    {
        return Applicant::create([
                'job_id'   => $data['job_id'],
                'user_id'  => $data['user_id'],
                'name'     => $data['name'],
                'telno1'   => $data['telno1'],
                'telno2'   => $data['telno2'],
                'notes'    => $data['notes'],
        ]);
    }
}
