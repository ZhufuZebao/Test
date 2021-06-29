<?php
/**
 * 評価ページのコントローラー
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
use App\Faq;

class FaqController extends \App\Http\Controllers\Controller
{

    /**
     * コンストラクタ
     *
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * 教えてサイトのトップページ
     *
     */
    public function index()
    {
        $faqs = Faq::get();

        return view('/faq/index', [
                'faqs'          => $faqs,
        ]);
    }

    /**
     * 教えてサイト（カテゴリー別）のページ
     *
     * @param   int     $id     カテゴリーID
     */
    public function category($id)
    {
        $category_name = DB::table('categorys')->where('id', $id)->value('name');

        $faqs = Faq::get($id);

        return view('/faq/category', [
                'category_name' => $category_name,
                'faqs'          => $faqs,
        ]);
    }

    /**
     * 記事詳細のページ
     *
     * @param   int     $id     記事ID
     */
    public function detail($id)
    {
        $faqs = Faq::get('', $id);

        return view('/faq/detail', [
                'faqs'          => $faqs[0],
        ]);
    }

    /**
     * 返信するページ
     *
     * @param   int     $id     記事ID
     */
    public function reply($id)
    {
        $faqs = Faq::get('', $id);

        return view('/faq/reply', [
                'faqs'          => $faqs[0],
        ]);
    }

    /**
     * 返信確認ページ
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function confirm(Request $request)
    {
        $result = Validator::make($request->all(), [
                'comment'       => 'required|max:1000',
        ], [
                'comment.required'      => '本文を入力してください。',
                'comment.max'           => '本文は全角500文字以内で入力してください。',
        ])->validate();

        $request->flash();

        $faqs = Faq::get('', $request->input('id'));

        return view('/faq/confirm', [
                'faqs'      => $faqs[0],
                'input'     => $request->all(),
        ]);
    }

    /**
     * 返信をデータベースに登録する
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function regist(Request $request)
    {
        $result = Validator::make($request->all(), [
                'comment'       => 'required|max:1000',
        ], [
                'comment.required'      => '本文を入力してください。',
                'comment.max'           => '本文は全角500文字以内で入力してください。',
        ])->validate();

        $request->flash();

        $faqs = Faq::get('', $request->input('id'));

        Faq::set($faqs[0], Auth::id(), $request->input('comment'));

        return redirect('/faq');
    }
}
