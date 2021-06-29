<?php
/**
 * 受注履歴ページのコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use App\Http\Requests;
use App\Http\Controllers\Controller;
//use Request;
use DB;
use App\History;
use App\Project;
use App\Contractor;
use App\Pref;

class HistoryController extends \App\Http\Controllers\Controller
{
    /**
     * 受注履歴のトップページ
     *
     */
    public function index()
    {
        $historys = History::get(Auth::id());
//print_r($historys);

        return view('/history/index', [
                'historys' => $historys,
        ]);
    }

    /**
     * プロジェクトの詳細ページ
     *
     * @param   int     $id     プロジェクトID
     */
    public function project($id)
    {
        $projects = Project::getProject($id);
        //print_r($projects);

        return view('/history/project', [
                'projects' => $projects[0],
        ]);
    }

    /**
     * 下請け業者の詳細ページ
     *
     * @param   int     $id     下請け業者ID
     */
    public function contractor($id)
    {
        $contractors = Contractor::getContractor($id);
        //print_r($projects);

        $prefs = Pref::get();

        return view('/history/contractor', [
                'contractors' => $contractors[0],
                'prefs' => $prefs,
        ]);
    }
}
