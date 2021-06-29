<?php
/**
 * 工程管理ページのコントローラー
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
use App\Contractor;
use App\Project;
use App\Calendar;
use App\Staff;
use App\Part;
use App\Process;


use App\GanttTask;
use App\GanttLink;
use Dhtmlx\Connector\GanttConnector;


use Session;

class StepController extends \App\Http\Controllers\Controller
{
    /**
     * 工程管理のトップページ
     *
     */
    public function index()
    {
        $contractors = Contractor::get();

        return view('/step/index', [
                'contractors' => $contractors,
        ]);
    }

    /**
     * 工程管理・新規作成のページ
     *
     */
    public function new()
    {
        $contractors = Contractor::get();

        return view('/step/new', [
                'contractors' => $contractors,
        ]);
    }

    // test by reiko mori
    public function create__()
    {
	$contractors = Contractor::get();

	return view('/task/index', [
	       'contractors' => $contractors,
	       ]);
    }
  
    public function create() {
        $connector = new GanttConnector(null, "PHPLaravel");
        $connector->render_links(new GanttLink(), "id", "source,target,type");
        //$connector->render_table(new GanttTask(),"id","start_date,duration,text,progress,parent");

	return view('/task/index', [
	       'contractors' => Contractor::get(),
	       ]);
}

    /**
     * 工程管理・新規作成のページの次のページ（カレンダ）
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function process(Request $request)
    {
        $result = Validator::make($request->all(), [
                'project_name'  => 'required|max:64',
                'contractor'    => 'required',
                'st_date'       => 'required',
                'ed_date'       => 'required',
                'manager'       => 'required|max:16',
                'author'        => 'required|max:16',
                'create_date'   => 'required',
        ], [
                'project_name.required' => 'プロジェクト名を入力してください。',
                'project_name.max'      => 'プロジェクト名は全角64文字以内で入力してください。',
                'contractor.required'   => '下請け業者を選択してください。',
                'st_date.required'      => '開始日を入力してください。',
                'ed_date.required'      => '完了予定日を入力してください。',
                'manager.required'      => '責任者を入力してください。',
                'manager.max'           => '責任者は全角16文字以内で入力してください。',
                'author.required'       => '作成者を入力してください。',
                'author.max'            => '作成者は全角16文字以内で入力してください。',
                'create_date.required'  => '作成日を入力してください。',
        ])->validate();

//        $result = $this->validator($request->all())->validate();

        $request->flash();

        $ref = $request->input('ref');
        if ($ref == '') {
            $ref = 0;
        }
//echo 'ref='. $ref. '<br>';

        // カレンダーを作成
        $cal = new Calendar();
        $calendar = $cal->makeCalendar($ref);

        // 表示用の年月を算出
        $display_y = (int)date('Y');
        $display_m = (int)date('m');
        $display_m = $display_m + $ref;
        if ($display_m < 1) {
            $display_m = 12 + $display_m;
            $display_y -= 1;
        } else if ($display_m > 12) {
            $display_m = $display_m - 12;
            $display_y += 1 ;
        }

        $staff = Staff::get($request->input('contractor'));

        $part = Part::get();

        $project_id = $request->input('project_id');

        if ($project_id) {
            $projects = Project::getProject($project_id);

        } else {
            $projects = Project::get(
                    $request->input('project_name'),
                    $request->input('contractor'),
                    $request->input('st_date'),
                    $request->input('ed_date'),
                    $request->input('manager'),
                    $request->input('author'),
                    $request->input('create_date')
            );
        }
//print_r($projects);

        $processs= null;
        if (!empty($projects)) {
            $processs= Process::get($projects[0]->id, $display_y, $display_m);
        }
//var_dump($processs);

        return view('/step/process', [
                'ref'       => $ref,
                'calendar'  => $calendar,
                'display_y' => $display_y,
                'display_m' => $display_m,
                'today_y'   => (int)date('Y'),
                'today_m'   => (int)date('m'),
                'today_d'   => (int)date('d'),
                'staff'     => $staff,
                'part'      => $part,
                'projects'  => $projects[0],
                'processs'  => $processs,
        ]);
    }

    /**
     * 工程管理（カレンダ）のページ
     *
     * @param   int     $ref    ・・・ -1=1か月前, 0=当月, 1=1か月後・・・
     * @param   int     $id     プロジェクトID
     */
    public function process2($ref, $id)
    {
        if ($ref == '') {
            $ref = 0;
        }

        $projects = Project::getProject($id);

        // カレンダーを作成
        $cal = new Calendar();
        $calendar = $cal->makeCalendar($ref);

        // 表示用の年月を算出
        $display_y = (int)date('Y');
        $display_m = (int)date('m');
        $display_m = $display_m + $ref;
        if ($display_m < 1) {
            $display_m = 12 + $display_m;
            $display_y -= 1;
        } else if ($display_m > 12) {
            $display_m = $display_m - 12;
            $display_y += 1 ;
        }

        $processs = Process::get($projects[0]->id, $display_y, $display_m);
        //var_dump($processs);

        $staff = Staff::get($projects[0]->contractor_id);

        $part = Part::get();

        return view('/step/process', [
                'ref'       => $ref,
                'calendar'  => $calendar,
                'display_y' => $display_y,
                'display_m' => $display_m,
                'today_y'   => (int)date('Y'),
                'today_m'   => (int)date('m'),
                'today_d'   => (int)date('d'),
                'staff'     => $staff,
                'part'      => $part,
                'projects'  => $projects[0],
                'processs'  => $processs,
        ]);
    }

    /**
     * プロジェクト検索結果のページ
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function project(Request $request)
    {
        $result = Validator::make($request->all(), [
                'project_name'  => 'max:128',
                'manager'       => 'max:16',
                'author'        => 'max:16',
        ], [
                'project_name.max'  => 'プロジェクト名は全角64文字以内で入力してください。',
                'manager.max'       => '責任者は全角16文字以内で入力してください。',
                'author.max'        => '作成者は全角16文字以内で入力してください。',
        ])->validate();

        $request->flash();

//$data = Session::all();
//print_r($data);

        $contractors = Contractor::get();

        $projects = Project::get(
                $request->input('project_name'),
                $request->input('contractor'),
                $request->input('st_date'),
                $request->input('ed_date'),
                $request->input('manager'),
                $request->input('author'),
                $request->input('create_date')
        );

        return view('/step/project', [
                'input'         => $request->all(),
                'contractors'   => $contractors,
                'projects'      => $projects,
        ]);
    }

    /**
     * 工程を登録する
     *
     * @param   Request     $request    フォームからのリクエストパラメータ
     */
    public function regist(Request $request)
    {
        $result = Validator::make($request->all(), [
                'part'          => 'required',
                'staff'         => 'required',
                'st_expected'   => 'required',
                'ed_expected'   => 'required',
        ], [
                'part.required'          => '内容 x',
                'staff.required'         => '担当 x',
                'st_expected.required'   => '開始 x',
                'ed_expected.required'   => '終了 x',
        ])->validate();

        if ($request->input('project_name') != '') {
            $projects = Project::set(
                    $request->input('project_name'),
                    $request->input('contractor'),
                    $request->input('st_date'),
                    $request->input('ed_date'),
                    $request->input('manager'),
                    $request->input('author'),
                    $request->input('create_date')
            );
        }
//print_r($projects);

        if (empty($projects)) {
            $projects = Project::get(
                    $request->input('project_name'),
                    $request->input('contractor'),
                    $request->input('st_date'),
                    $request->input('ed_date'),
                    $request->input('manager'),
                    $request->input('author'),
                    $request->input('create_date')
                    );
        }
//print_r($projects);
//exit();

        Process::set(
                $projects[0]->id,
                $request->input('contractor'),
                $request->input('part'),
                $request->input('staff'),
                $request->input('st_expected'),
                $request->input('ed_expected'),
                $request->input('st_result'),
                $request->input('ed_result')
        );

        $request->flash();

        $contractors = Contractor::get();

        $ref = $request->input('ref');

//        return $this->process($request);
        return redirect('/processsheet/ref/'. $ref. '/id/'. $projects[0]->id);
    }

    /**
     * 工程を全て削除する
     *
     * @param   int     $ref    ・・・ -1=1か月前, 0=当月, 1=1か月後・・・
     * @param   int     $id     プロジェクトID
     */
    public function delete($ref, $id)
    {
        DB::table('processs')->where('project_id', '=', $id)->delete();

        return redirect('/processsheet/ref/'. $ref. '/id/'. $id);
    }

    /**
     * 工程を１件削除する
     *
     * @param   int     $ref        ・・・ -1=1か月前, 0=当月, 1=1か月後・・・
     * @param   int     $project_id プロジェクトID
     * @param   int     $process_id 工程ID
     * @return unknown
     */
    public function onedelete($ref, $project_id, $process_id)
    {
        DB::table('processs')
        ->where('project_id', '=', $project_id)
            ->where('id', '=', $process_id)
            ->delete();

        return redirect('/processsheet/ref/'. $ref. '/id/'. $project_id);
    }
}
