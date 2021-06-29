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
use App\History;
use App\Evaluation;

class EvaluationController extends \App\Http\Controllers\Controller
{
    /**
     * 評価のトップページ
     *
     */
    public function index()
    {
        $historys = History::get(Auth::id());
//print_r($historys);

        return view('/evaluation/index', [
                'historys' => $historys,
        ]);
    }

    /**
     * 依頼主の評価を登録するページ（受注履歴の一覧）
     *
     */
    public function list()
    {
        $historys = History::get(Auth::id());

        $evaluations = Evaluation::get(Auth::id());

        $unrated = count($historys) - count($evaluations);

        return view('/evaluation/list', [
                'historys'      => $historys,
                'evaluations'   => $evaluations,
                'unrated'       => $unrated,
        ]);
    }

    /**
     * 自分の評価を見るページ
     *
     */
    public function my()
    {
        $historys = History::get(Auth::id());

        $evaluations = Evaluation::get(Auth::id());

        $unrated = count($historys) - count($evaluations);

        return view('/evaluation/my', [
                'historys'      => $historys,
                'evaluations'   => $evaluations,
                'unrated'       => $unrated,
        ]);
    }

    /**
     * 評価を新規で投稿するページ
     *
     * @param   int     $project_id     プロジェクトID
     */
    public function new($project_id)
    {
        $historys = History::get(Auth::id(), $project_id);

        $evaluations = Evaluation::get(Auth::id(), $project_id);

        $unrated = count($historys) - count($evaluations);

        return view('/evaluation/new', [
                'historys'      => $historys[0],
                'evaluations'   => $evaluations[$project_id],
        ]);
    }

    /**
     * 評価を更新するページ
     *
     * @param   int     $project_id     プロジェクトID
     */
    public function edit($project_id)
    {
        $historys = History::get(Auth::id(), $project_id);

        $evaluations = Evaluation::get(Auth::id(), $project_id);

        $unrated = count($historys) - count($evaluations);

        return view('/evaluation/edit', [
                'historys'      => $historys[0],
                'evaluations'   => $evaluations[$project_id],
        ]);
    }

    /**
     * 評価をデータベースに登録する
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function regist(Request $request)
    {
        $result = Validator::make($request->all(), [
                'satisfaction'  => 'required',
                'comment'       => 'required|max:1000',
        ], [
                'satisfaction.required' => '満足度を選択してください。',
                'comment.required'      => '本文を入力してください。',
                'comment.max'           => '本文は全角500文字以内で入力してください。',
        ])->validate();

        $ret = Evaluation::set(
                Auth::id(),
                $request->input('project_id'),
                $request->input('satisfaction'),
                $request->input('comment')
            );

        return redirect('/evaluation');
    }
}
