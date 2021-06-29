<?php


namespace App\Http\Controllers\Web;

use App\Models\ChatMessage;
use App\Models\Node;
use App\Models\ProjectParticipant;
use App\Models\ReportFile;
use App\Models\Rev;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Report;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf; //use laravel-snappy
use App\Http\Facades\Common;

class ReportController extends \App\Http\Controllers\Controller
{
    /**
     * @var array
     */
    private $content;

    private $imageFileExtension = ['PNG','JPG','JPEG','GIF'];

    private $pagination = 10;

    const FILES_PATH_PREFIX = 'files';

    public function __construct()
    {
        $this->content = array();
    }

    /*
     * ケース名
     * */
    public function proList(Request $request)
    {
        //#2796【案件】他社社員ユーザを職人登録した際に、案件情報を見えないように変更する
        $projectController = new ProjectController();
        $userContactArrFilter=$projectController->getChatContact();

        $user = Auth::user();
        $keyword = Common::escapeDBSelectKeyword($request->get("searchWord"));
        $query = Project::with(['projectLocaleChief'])
            ->where(function ($q) {
                // 作成者と共有者
                $q->where('created_by', Auth::id());
                $q->orWhereHas('projectParticipant', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            })->whereNotIn('created_by',$userContactArrFilter);
        if (strlen($keyword) > 0) {
            $query->where(function ($q) use ($keyword) {
                $q->where('place_name', 'LIKE', "%{$keyword}%");
            });
        }
        $models = $query->get();
        return ['model' => $models, 'enterpriseId' => $user->enterprise_id];
    }

    /*
     * レポートの追加と変更
     * */
    public function reportSave()
    {
        $report = new Report();
        $user = new User();
        $id = request()->post('id');
        $name = request()->input('user_name');
        $user_id = $user->where('name', $name)->value('id');
        if (!$user_id) {
            return response()->json('no such user');
        }
        $comment = request()->input('comment');
        if ($id) {
            $model = $report::where('id', $id)->first();
            $model->project_id = request()->input('project_id');
            $model->report_date = request()->input('report_date');
            $model->user_id = $user_id;
            $model->location = request()->input('location');
            $model->type = request()->input('type');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();
            if (request()->hasFile('upfile')) {
                Db::table('report_files')->where('report_id', $id)->delete();
                foreach ($_FILES['upfile'] as $key => $value) {
                    $file_name = $comment[$key]['file_name'];
                    $file_name = $value['file_name'];
                    $str = urldecode($file_name);
                    $file_name = mb_convert_encoding($str, "UTF-8");
                    $img_path = $value['tmp_name'];
                    //$img_path = $_FILES['upfile']['tmp_name'];
                    $contents = file_get_contents($img_path);
                    if ($contents === false) {
                        return 'error1';
                    } else {
                        $disk = Storage::disk(config('app.imageUpload.disk'));
                        $disk->put(config('app.imageUpload.report') . 'r' . $model->id . '/' . $file_name, $contents);
                        //  Db::table('report_files')->insert(['report_id'=>$report->id,'comment'=>$comment[$key]['comment'],'file_path'=>$file_name,'order'=>$comment[$key]['order'],'created_at'=>date('Y-m-d H:i:s')]);
                        Db::table('report_files')->insert([
                            'report_id' => $model->id,
                            'comment' => $comment[$key]['comment'],
                            'file_path' => $file_name,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        } else {
            $report->project_id = request()->input('project_id');
            $report->report_date = request()->input('report_date');
            $report->user_id = $user_id;
            $report->location = request()->input('location');
            $report->type = request()->input('type');
            $report->created_at = date('Y-m-d H:i:s');
            $report->updated_at = null;
            $report->save();
            // return $report;
            //all image details

            if (request()->hasFile('upfile')) {
                foreach ($_FILES['upfile'] as $key => $value) {
                    $file_name = $comment[$key]['file_name'];
                    $str = urldecode($file_name);
                    $file_name = mb_convert_encoding($str, "UTF-8");
                    $img_path = $value['tmp_name'];
                    //$img_path = $_FILES['upfile']['tmp_name'];
                    $contents = file_get_contents($img_path);
                    if ($contents === false) {
                        return 'error1';
                    } else {
                        $disk = Storage::disk(config('app.imageUpload.disk'));
                        $disk->put(config('app.imageUpload.report') . 'r' . $report->id . '/' . $file_name, $contents);
                        //  Db::table('report_files')->insert(['report_id'=>$report->id,'comment'=>$comment[$key]['comment'],'file_path'=>$file_name,'order'=>$comment[$key]['order'],'created_at'=>date('Y-m-d H:i:s')]);
                        Db::table('report_files')->insert([
                            'report_id' => $report->id,
                            'comment' => $comment[$key]['comment'],
                            'file_path' => $file_name,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

            }
        }
        return 'success';
    }

    /*
     * レポート修正
    */
    public function updateReport(Request $request)
    {
        DB::beginTransaction();
        try {
            $report = new Report();
            $reportData = $request->input('report');
            $id = $reportData['id'];
            if ($id) {
                $model = $report::where('id', $id)->first();
                $model->report_date = $reportData['report_date'];
                $model->report_date_ed = $reportData['report_date_ed'];
                $model->project_name = $reportData['project_name'];
                $model->location = $reportData['location'];
                $model->report_user_name = $reportData['report_user_name'];
//                $model->type = $reportData['type'];
                $model->updated_at = date('Y-m-d H:i:s');
                $model->updated_by = Auth::id();
                $model->save();
            } else {
                $report->project_id = $reportData['project_id'];
                $report->report_date = $reportData['report_date'];
                $report->report_date_ed = $reportData['report_date_ed'];
                $report->project_name = $reportData['project_name'];
                $report->user_id = Auth::id();
                $report->location = $reportData['location'];
                $report->report_user_name = $reportData['report_user_name'];
                $report->file_path = '';
                $report->file_date = date('Y-m-d');
                $report->save();
                $id = $report->id;
            }

            //report files
            $reportFiles = $reportData['report_files'];
            $numFiles = count($reportFiles);
            $filesExists = Db::table('report_files')
                ->where('report_id', $id)
                ->select(['id'])
                ->get();
            $idExists = array();
            foreach ($filesExists as $v) {
                $idExists[] = $v->id;
            }
            if ($numFiles) {
                //編集の場合
                if (count($idExists)){
                    $arrKeep = array();
                    for ($i = 0; $i < $numFiles; $i++) {
                        if (isset($reportFiles[$i]['report_file_id'])===true) {
                            $arrKeep[] = $reportFiles[$i]['report_file_id'];
                            $reportUpdate = ReportFile::find($reportFiles[$i]['report_file_id']);
                            if (isset($reportFiles[$i]['comment'])) {
                                $reportUpdate->comment = $reportFiles[$i]['comment'];
                            } else {
                                $reportUpdate->comment = '';
                            }
                            $reportUpdate->report_date = date_format(date_create($reportFiles[$i]['report_date']),"Y/m/d");
                            if (isset($reportFiles[$i]['work_place'])) {
                                $reportUpdate->work_place = $reportFiles[$i]['work_place'];
                            } else {
                                $reportUpdate->work_place = '';
                            }
                            if (isset($reportFiles[$i]['weather'])) {
                                $reportUpdate->weather = $reportFiles[$i]['weather'];
                            } else {
                                $reportUpdate->weather = '';
                            }
                            $reportUpdate->file_path = $reportFiles[$i]['file_path'];
                            $reportUpdate->update();
                        } else {
//                            if ($reportFiles[$i]['type'] == 1){
//                                $file_path = $this->move($reportFiles[$i]['file_path'], $id);
//                                $reportFiles[$i]['file_path'] = $file_path;
//                            }
                            if ($reportFiles[$i]['file_path'] == 'images/no-image.png') {
                                $filePathTemp = $reportFiles[$i]['file_path'];
                            } else {
                                $filePathTemp = $reportFiles[$i]['id'].'/'.$reportFiles[$i]['revNo'].'/'.$reportFiles[$i]['fileName'];
                            }
                            $reportFileCreate = new ReportFile();
                            $reportFileCreate->report_id = $id;
                            $reportFileCreate->report_date = date_format(date_create($reportFiles[$i]['report_date']),"Y/m/d");

                            if (isset($reportFiles[$i]['work_place'])) {
                                $reportFileCreate->work_place = $reportFiles[$i]['work_place'];
                            } else {
                                $reportFileCreate->work_place = '';
                            }

                            if (isset($reportFiles[$i]['weather'])) {
                                $reportFileCreate->weather = $reportFiles[$i]['weather'];
                            } else {
                                $reportFileCreate->weather = '';
                            }

                            $reportFileCreate->file_path = $filePathTemp;

                            if (isset($reportFiles[$i]['comment'])) {
                                $reportFileCreate->comment = $reportFiles[$i]['comment'];
                            } else {
                                $reportFileCreate->comment = '';
                            }
                            $reportFileCreate->save();
                        }
                    }
                    $removeId = array_diff($idExists, $arrKeep);
                    DB::table('report_files')->whereIn('id', $removeId)->delete();
                } else {
                    //新規の場合
                    foreach ($reportFiles as $item)
                    {
                        $reportFileNew = new ReportFile();
                        $reportFileNew->report_id = $id;

                        if ($item['file_path'] != 'images/no-image.png') {
                            //画像があります
                            $reportFileNew->file_path =  $item['id'].'/'.$item['revNo'].'/'.$item['fileName'];
                        } else {
                            //NO image  画像は存在しません
                            $reportFileNew->file_path = $item['file_path'];
                        }

                        $reportFileNew->report_date = date_format(date_create($item['report_date']),"Y/m/d");

                        if (isset($item['weather'])) {
                            $reportFileNew->work_place = $item['work_place'];
                        } else {
                            $reportFileNew->work_place = '';
                        }
                        
                        if (isset($item['weather'])) {
                            $reportFileNew->weather = $item['weather'];
                        } else {
                            $reportFileNew->weather = '';
                        }

                        if (isset($item['comment'])){
                            $reportFileNew->comment = $item['comment'];
                        } else {
                            $reportFileNew->comment = '';
                        }
                        $reportFileNew->save();
                    }
                }

            } else {
                DB::table('report_files')->where('report_id', $id)->delete();
            }


            DB::commit();
            return ['report_id'=>$id];
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e);
            return response()->json([
                'status' => false
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    private function getDocImageFile($file_path)
    {
        $filePath = explode('/',$file_path);
        if($filePath[0] > 0) {
            $filePath = implode('/', [self::FILES_PATH_PREFIX, $filePath[0], $filePath[1], $filePath[2]]);
            $content = Storage::disk('s3doc')->get($filePath);
        } else {
            $filePath = $this->getSingleChatGroupFilePath(-$filePath[0]);
            $content = Storage::disk('s3')->get($filePath);
        }

        return $content;
    }

    /**
     * @param $id     table->report report_id
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getPDF()
    {
        //process pdf export
        $id = request()->input('id');
        $createName = request()->input('createName');
        $placeName = request()->input('placeName');
        $report = new Report();
        $res = $report::with(['project', 'user', 'reportFile'])->where('id', $id)->first()->toArray();
        //load s3 file
        $pathBase = sprintf('report/%s', 'r' . $id);
        $tmpArr = [];
        foreach ($res['report_file'] as $key => $value) {
            if ($value['file_path'] != 'images/no-image.png') {
                //画像があります
                $content = $this->getDocImageFile($value['file_path']);
                $pathArr =explode('/', $value['file_path']);
                //change file name
                $newExtname = '';
                if (strrpos($pathArr[2], '.')) {
                    $newExtname = substr($pathArr[2], strrpos($pathArr[2], '.'));
                }
                $newfilename = time().mt_rand(10000000,99999999).$newExtname;
                $value['file_path'] = $pathBase . '/f' . $pathArr[0] . '/' .  $pathArr[1] . '/' . $newfilename;
                $tmpArr[] = $value['file_path'];
                Storage::disk(config('web.pdfUpload.disk'))->put($value['file_path'], $content);
                $value['file_path'] = Storage::disk(config('web.pdfUpload.disk'))->url($value['file_path']);
            } else {
                //NO image  画像は存在しません
            }
            //作業日
            //[変更]-下の作業日の表示も「-」で統一してください。
//            $dateSplitTmp = explode('-',$value['report_date']);
            $dateWeekTmpNumber = date('w',strtotime($value['report_date']));
            $dateWeekTmpString = '';
            switch ($dateWeekTmpNumber)
            {
                //['月', '火', '水', '木', '金', '土', '日'],
                case 0:
                    //sunday
                    $dateWeekTmpString = '日';
                    break;
                case 1:
                    $dateWeekTmpString = '月';
                    break;
                case 2:
                    $dateWeekTmpString = '火';
                    break;
                case 3:
                    $dateWeekTmpString = '水';
                    break;
                case 4:
                    $dateWeekTmpString = '木';
                    break;
                case 5:
                    $dateWeekTmpString = '金';
                    break;
                case 6:
                    $dateWeekTmpString = '土';
                    break;
                default:
                    $dateWeekTmpString = '';

            }
            $value['report_date'] = $value['report_date'].'（'.$dateWeekTmpString.'）';
            
            //作業箇所
            //天気
            //作業内容
            $value['comment'] = nl2br($value['comment']);
            $value['comment'] = $this->getSubstr($value['comment'],0,1000);

            //また文章が途中で切れていることが分かるように 「…」を表示することは 可能でしょうか？
            //add style
            $space_tag = 0;
            $weather_tag = 0;
            if($value['work_place']) {
                $space_tag = 1;
            }
            if($value['weather']) {
                $weather_tag = 1;
            }
            $tag_sum = $space_tag + $weather_tag;

            switch($tag_sum) {
                case 2:
                    $value['row_td_style'] = 'row10td';
                    $value['row_num_style'] = 'row10p';
                    break;
                case 1:
                    $value['row_td_style'] = 'row11td';
                    $value['row_num_style'] = 'row11p';
                    break;
                case 0:
                    $value['row_td_style'] = 'row12td';
                    $value['row_num_style'] = 'row12p';
                    break;
                default:
                    $value['row_td_style'] = 'row10td';
                    $value['row_num_style'] = 'row10p';
                    break;
            }
            //また文章が途中で切れていることが分かるように 「…」を表示することは 可能でしょうか？ -END
            $res['report_file'][$key] = $value;
        }
        
        $res['createName'] = $createName;
        $res['placeName'] = $placeName;

        $pdf = PDF::loadView('inform.apppdf', $res)->setPaper('a4')->setOption('margin-top', 20);//todo:different type refer to different template file
        //make file path //todo
        $fileName = md5(time()) . ".pdf";
        // $pdf->save($fileName);
        $content = $pdf->output();
        $pdfPath = sprintf('upload/%s', $fileName);
        Storage::disk(config('web.pdfUpload.disk'))->put($pdfPath, $content);
        $fileName = Storage::disk(config('web.pdfUpload.disk'))->url($pdfPath);
        Storage::disk('local')->put($fileName, $content);
        //現場名_YYYYMMDD
        $pdfName = $res['project']['place_name'].'_'.date_format(date_create($res['report_date']),"Ymd").'.pdf';
        foreach($tmpArr as $tmp) {
            Storage::disk(config('web.pdfUpload.disk'))->delete($tmp);
        }
        $return['filePath'] = $fileName;
        DB::table('reports')
            ->where('id', $id)
            ->update([
                'file_path' => $fileName,
                //'file_path' => 'file/r'..$fileName,
                'file_date' => date('Y-m-d')
            ]);


        return response()->download($fileName,$pdfName,$headers = ['Content-Type' => 'application/pdf;charset=utf-8'])
            ->deleteFileAfterSend(true);
    }

    private function getSubstr($string, $start, $length) {
        if (mb_strlen($string, 'utf-8') > $length) {
            $str = mb_substr($string, $start, $length, 'utf-8');
            return $str . '...';
        } else {
            return $string;
        }
    }

    /**
     * 一目でわかるレポート
     * @param Request $request
     *
     * @return mixed
     */
    public function reportList(Request $request)
    {
        $report = new Report();
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $sortCol = $request->input('sortCol', 'updated_at');
        $order = $request->input('order', 'desc');
        $keyword = Common::escapeDBSelectKeyword($request->input('keyword'));
        //案件一覧の権限と同様-------start--------
        //project list Filter
        $projectListModal = $this->proList(new Request())['model'];
        $projectFilterIds = [];
        foreach ($projectListModal as $item){
            $projectFilterIds[] = $item['id'];
        }
        //案件一覧の権限と同様-------end--------
        $rule = $report::with(['project', 'user', 'reportFile','updatedBy'])->whereIn('project_id',$projectFilterIds)->orderBy($sortCol,
            $order);//案件一覧の権限と同様
        if (strlen($keyword) > 0) {
            $projectIds = array();
            $projectInfo = DB::table('projects')
                ->where('place_name', 'LIKE', "%$keyword%")
                ->select(['id'])
                ->get();
            foreach ($projectInfo as $v) {
                $projectIds[] = $v->id;
            }
            //案件一覧の権限と同様
            $rule->whereIn('project_id', $projectIds)->whereIn('project_id',$projectFilterIds);
        }

        $res = $rule->paginate($this->getPagesize($this->pagination))->toArray();
        foreach ($res['data'] as $k => $v) {
            if ($res['data'][$k]['file_date']) {
                $res['data'][$k]['file_date'] = date('Y年n月j日', strtotime($res['data'][$k]['file_date']));
            }
            if ($res['data'][$k]['updated_at']) {
                $res['data'][$k]['updated_at'] = date('Y年n月j日', strtotime($res['data'][$k]['updated_at']));
            }
        }

        return $res;
    }

    /**
     * 写真を報告する
     * @return mixed|string
     */
    public function getReportFile(){
        $id=request()->input('id');
        $report_id = request()->input('report_id');
        $name = request()->input('name');
        $type = request()->input('type');
        if ($type == 'reportPdf') {
            $filename = config('app.imageUpload.report') . 'p' . $report_id . '/' . $name;
            $disk = Storage::disk(config('app.imageUpload.disk'));
        } elseif ($type == 'projects') {
            $disk = Storage::disk('local');
            $file = Project::where('id', $id)->value('subject_image');
            $filename = config('app.imageUpload.project') . $file;

        } elseif ($type == 'report') {
            $disk = Storage::disk(config('app.imageUpload.disk'));
            $file = Db::table('report_files')->where('id', $id)->value('file_path');
            $filename = config('app.imageUpload.report') . 'r' . $report_id . '/' . $file;
            //dd($filename);
        } elseif ($type == 'doc') {
            $disk = Storage::disk(config('app.imageUpload.disk'));
            $filename = 'files/' . $name;
        } elseif ($type == 'group') {
            $disk = Storage::disk(config('app.imageUpload.disk'));
            $filename = 'upload/' . $name;
        }elseif ($type == 'tmp') {
            $disk = Storage::disk(config('app.imageUpload.disk'));
            $filename =  $name;
        }  else {
            $disk = Storage::disk(config('app.imageUpload.disk'));
            $filename = null;
        }
        try {
            if ($disk->exists($filename)) {
                $file = $disk->get($filename);
                return $file;
            }
        } catch (Exception $e) {
        }
        return 'nodata';
    }

    /*レポートの詳細*/
    public function reportDetail()
    {
        $id = request()->input('id');
        $report = new Report();
        $res = $report::where('id', $id)->with(['user', 'project'])->first()->toArray();
        if (!$res['report_date_ed']) {
            //init report_date_ed col
            $res['report_date_ed'] = $res['report_date'];
        }
        if (!$res['project_name']) {
            //init report_date_ed col
            $res['project_name'] = $res['project']['place_name'];
        }
        $res['report_files'] = Db::table('report_files')->where('report_id', $res['id'])->orderBy('id',
            'asc')->get()->toArray();
        foreach ($res['report_files'] as $key => $value) {
            if ($value->file_path) {
                if ($value->file_path == 'images/no-image.png') {
                    $value->fileName = 'NO IMAGE';
                } else {
                    $fileSpliceTemp = explode('/',$value->file_path);
                    $value->fileName = end($fileSpliceTemp);
                }
                $value->report_file_id = $value->id;
                $value->file_path_url = $value->file_path;    //todo : document file path
            } else {
                $value->file_path_url = null;
            }
            $res['report_files'][$key] = $value;
        }
        return response()->json($res);
    }

    public function createReportForm()
    {
        $projectId = request()->input('id');
        $projectInfo = DB::table('projects')
            ->where('id', $projectId)
            ->first();
        $userId = Auth::id();
        $userInfo = DB::table('users')
            ->where('id', $userId)
            ->first();
        $res['id'] = 0;
        $res['location'] = $projectInfo->place_name;
        $res['report_user_name'] = $userInfo->name;
        $res['project'] = $projectInfo;
        $res['project_id'] = $projectId;
        $res['project_name'] = $projectInfo->place_name;
        $res['report_date'] = date('Y-m-d');
        $res['report_date_ed'] = date('Y-m-d');
        $res['report_files'] = array();
        $res['type'] = "1";
        $res['user'] = $userInfo;
        $res['user_id'] = $userId;

        return response()->json($res);
    }

    /*レポート写真を削除*/
    public function deleteFile(Request $request)
    {
        $id = $request->input('id');
        $report_id = $request->input('report_id');
        $filename = Db::table('report_files')->where('id', $id)->value('file_path');
        $dir = config('app.imageUpload.report') . 'r' . $report_id . '/';
        $disk = Storage::disk(config('app.imageUpload.disk'));
        if ($disk->exists($dir . $filename)) {
            $disk->delete($dir . $filename);
        }
        Db::table('report_files')->where('id', $id)->delete();
        return 'success';
    }

    /*レポートを削除*/
    public function deteteReport()
    {
        $id = request()->input('id');
        $report = new Report();
        $opFlag = $report::whereIn('id', $id)->delete();
        $file = DB::table('report_files')->whereIn('report_id', $id)->select('id', 'file_path')->get()->toArray();
        //todo need to fix the image path and change the method of deleting files
//        foreach ($file as $key => $value) {
//            $filename = $value->file_path;
//            $dir = config('app.imageUpload.report') . 'r' . $id . '/';
//            $disk = Storage::disk(config('app.imageUpload.disk'));
//            if ($disk->exists($dir . $filename)) {
//                $disk->delete($dir . $filename);
//            }
//        }
        ReportFile::whereIn('report_id', $id)->delete();
        $result['delItemsNum'] = $opFlag;
        return $result;
    }

    //PDFファイルの導出
    public function exportPdf(Request $request)
    {
        $reportData = $request->input('report');
        $updateFlag = $this->updateReportData($reportData);
        if ($updateFlag) {
            $report = new Report();
            //update report
            $res = $report::with(['project', 'user', 'reportFile'])->where('id', $updateFlag)->first()->toArray();
            foreach ($res['report_file'] as $key => $value) {
                if ($value['file_path']) {
                    $value['file_path'] = '/api/getReportFile?id=' . $value['id'] . '&report_id=' . $updateFlag;
                    $value['file_path'];
                }
                $res['report_file'][$key] = $value;
            }
            $pdf = PDF::loadView('inform.apppdf', $res);
            //make file path //todo
            $fileName = md5(time()) . ".pdf";
            $pdf->save($fileName);
            $pdfName = 'testFileName';//todo

            return response()->download($fileName, $pdfName,
                $headers = ['Content-Type' => 'application/pdf;charset=utf-8']);
        } else {
            $res['flag'] = false;
            return $res;
        }
    }

    private function createNodeSyncResponse($project_id)
    {
        $nodes = Node::findAll($project_id);

        $nodes = array_map(function ($e) {
            return $this->toNodeJson($e);
        }, $nodes);

        $siteweb_db = config('const.db_database_site');
        $project = DB::table("projects")
            ->leftJoin("users", "users.id", "=", "projects.created_by")
            ->where("projects.id", $project_id)
            ->where("projects.deleted_at", null)
            ->whereNull("users.deleted_at")
            ->select([
                "projects.id",
                "projects.group_id",
                "users.name as userName"
            ])
            ->first();
        //add project chat-group
        $groupId = $project->group_id;
        $projectCreatedBy = $project->userName;

        //get groups which include the user logging in
        $userGroups = DB::table("chatgroups")
            ->where('user_id', Auth::id())
            ->whereNull('deleted_at')
            ->select([
                'group_id'
            ])
            ->get();
        $userGroupsArr = array();
        foreach ($userGroups as $groupItem) {
            $userGroupsArr[] = $groupItem->group_id;
        }
        //get main group info
        $mainGroup = DB::table("groups")
            ->where('id', $groupId)
            ->whereNull('deleted_at')
            ->first();
        $nodes[] = $this->makeNodeItemByGroup($mainGroup);
        //check if there are child-group
        $childGroup = DB::table("groups")
            ->where('parent_id', $groupId)
            ->whereIn('id', $userGroupsArr)
            ->whereNull('deleted_at')
            ->get();
        $groupChild = array();
        foreach ($childGroup as $group) {
            $groupChild[] = $group->id;
            $nodes[] = $this->makeNodeItemByGroup($group);
        }

        $groupChild[] = $groupId;
        //get all files belong to group
        $files = DB::table("chat_files")
            ->leftJoin("users", "users.id", "=",
                "chat_files.upload_user_id")
            ->select([
                "chat_files.id",
                "chat_files.group_id",
                "chat_files.file_name",
                "chat_files.file_size",
                "chat_files.created_at",
                "users.name as uploadUserName"
            ])
            ->whereIn("chat_files.group_id", $groupChild)
            ->whereNull("chat_files.deleted_at")
            ->whereNull("users.deleted_at")
            ->get();
        foreach ($files as $file) {
            $nodes[] = $this->makeNodeItemByFile($file);
        }

        //サムネイル処理
        foreach ($nodes as $nodeKey => $nodeValue) {
            $nodes[$nodeKey]['childNodes']
                = $this->getChildren($nodeValue['id'], $nodes);
            $nodes[$nodeKey]['parentNodes'] = $this->getParent($nodeValue['id'],
                $nodes);
        }

        return [
//            'sync' => [
'nodes' => $nodes,
//            ],
        ];
    }

    private function toNodeJson($row)
    {
        return [
            'id' => (int)$row->node_id,
            'parent' => is_null($row->node_id_parent) ? 0 : (int)$row->node_id_parent,
            'name' => $row->node_name,
            'type' => (int)$row->node_type,
            'owner' => $row->owner,
            'size' => (int)$row->file_size ? (int)$row->file_size : 0,
            'time' => $row->last_updated,
            'revNo' => is_null($row->rev_no) ? 0 : (int)$row->rev_no,
            'fileName' => $row->file_name ? $row->file_name : '',
            'fileType' => (int)$row->node_type > 0 ? $this->checkType($row->node_name) : '',
        ];
    }

    private function makeNodeItemByFile($file)
    {
        $arr = array();
        $arr['id'] = -$file->id;
        $arr['parent'] = -$file->group_id;
        $arr['name'] = $file->file_name;
        $arr['type'] = 1;
        $arr['owner'] = $file->uploadUserName;
        $arr['size'] = $file->file_size;
        $arr['time'] = $file->created_at;
        $arr['revNo'] = 0;
        $arr['fileName'] = $file->file_name;
        $arr['fileType'] = $this->checkType($file->file_name);
        return $arr;
    }

    private function checkType($fileName)
    {
        $arr = explode('.', $fileName);
        if (count($arr) > 1) {
            return strtoupper(end($arr));
        } else {
            return '';
        }
    }

    //get a node's child nodes all
    private function getChildren($p_id, $array)
    {
        $subs = array();
        foreach ($array as $item) {
            if ($item['parent'] == $p_id) {
                $subs[] = $item['id'];
                $subs = array_merge($subs, $this->getChildren($item['id'], $array));
            }
        }
        return $subs;
    }

    //get a node's parent nodes all
    private function getParent($p_id, $array)
    {
        $subs = array();
        foreach ($array as $item) {
            if ($item['id'] == $p_id) {
                $subs[] = $item['id'];
                $subs = array_merge($subs, $this->getParent($item['parent'], $array));
            }
        }
        return $subs;
    }

    private function makeNodeItemByGroup($groupInfo)
    {
        $arr = array();
        //get group admin
        $owner = $this->getGroupAdmin($groupInfo->id);
        if ($groupInfo->parent_id) {
            $groupInfo->parent_id = -$groupInfo->parent_id;
        } else {
            $groupInfo->parent_id = null;
        }
        $arr['id'] = -$groupInfo->id;
        $arr['parent'] = $groupInfo->parent_id ? $groupInfo->parent_id : 0;
        $arr['name'] = $groupInfo->name;
        $arr['type'] = 0;
        $arr['owner'] = $owner;
        $arr['size'] = 0;
        $arr['time'] = $groupInfo->created_at;
        $arr['revNo'] = 0;
        $arr['fileName'] = '';
        $arr['fileType'] = '';
        return $arr;
    }

    private function getGroupAdmin($group_id)
    {
        $userInfo = DB::table("chatgroups")
            ->leftJoin("users", "users.id", "=", "chatgroups.user_id")
            ->select([
                "users.name as userName"
            ])
            ->where("chatgroups.group_id", $group_id)
            ->where("chatgroups.admin", 1)
            ->whereNull("chatgroups.deleted_at")
            ->whereNull("users.deleted_at")
            ->first();
        if ($userInfo) {
            return $userInfo->userName;
        } else {
            return 'null';
        }
    }

    //get image list
    public function getImageList(Request $request)
    {
        $user = Auth::user();
        $projectId = $request->get("projectId");
        $doc_url = config('web.doc_url');

        $docmgr_nodes = $this->createNodeSyncResponse($projectId)['nodes'];

        $res_docmgr_revs = [];
        foreach ($docmgr_nodes as $rev)
        {
            //type = 1 => file ; type = 0 => folder
            if ($rev['type'] && $rev['fileName']) {
                //files
                //画像拡張名を取得
                $extension = $rev['fileType'];

                //画像拡張名の判断
                if ($extension && in_array($extension,$this->imageFileExtension)){
                    $res_docmgr_revs[] = $rev;
                }
            } else {
                //folder
            }
        }

        //document type = 3
        foreach ($res_docmgr_revs as $key=>$rev)
        {
            $filePathArr = [];
            if ($rev['id'] < 0) {
                //chat files
                $res_docmgr_revs[$key]['type'] = '1';
                $filePathArr = $this->showFile($rev['id'], $rev['revNo']);
                $res_docmgr_revs[$key]['file_path'] = $rev['id'] . '/' . $rev['revNo'] . '/'.$rev['fileName'];
                if(array_key_exists('error', $filePathArr)) {
                    //add default image file path
                    $res_docmgr_revs[$key]['file_path_url'] = 'images/no-image.png';
                } else {
                    $res_docmgr_revs[$key]['file_path_url'] = $filePathArr['fileUrl'];
                }
            } else {
                //document files
                $res_docmgr_revs[$key]['type'] = '2';
                $filePathArr = $this->showFile($rev['id'], $rev['revNo']);
                $res_docmgr_revs[$key]['file_path'] = $rev['id'] . '/' . $rev['revNo'] . '/'.$rev['fileName'];
                if(array_key_exists('error', $filePathArr)) {
                    //add default image file path
                    $res_docmgr_revs[$key]['file_path_url'] = 'images/no-image.png';
                } else {
                    $res_docmgr_revs[$key]['file_path_url'] = $filePathArr['fileUrl'];
                }
            }

        }

        $nodeFolders = [];
        foreach ($docmgr_nodes as $nodeItem)
        {
            if ($nodeItem['type'] === 0) {
                $nodeFolders[] = $nodeItem;
            }
        }

        return  ['revs' => $res_docmgr_revs,'folders' => $nodeFolders];
    }

    /**
     * //拡張名を取得
     *
     * @param $fileName
     *
     * @return bool|string
     */
    private function getSuffix($fileName){
        $pos = strpos($fileName,'.',1);
        $suffix = substr($fileName,$pos+1);
        return $suffix;
    }


    /**
     *同じnode_idの場合、rev_noの最大値のデータを取ります
     *
     * @param        $arr
     * @param string $key
     *
     * @return array
     */
    private function array_unset_tt($arr,$key='node_id'){
        $res = array();
        foreach ($arr as $value) {
            if(isset($res[$value[$key]])){
                //rev_noの最大値のデータを取ります
                if ($value['rev_no'] > $res[$value[$key]]['rev_no']){
                    $res[$value[$key]] = $value;
                }
            }else{
                //not have
                $res[$value[$key]] = $value;
            }
        }
        return array_values($res);
    }

    /**
     * object to array cover
     *
     * @param $object
     *
     * @return array
     */
    private function object2array_pre(&$object) {
        if (is_object($object)) {
            $arr = (array)($object);
        } else {
            $arr = &$object;
        }
        if (is_array($arr)) {
            foreach($arr as $varName => $varValue){
                $arr[$varName] = $this->object2array($varValue);
            }
        }
        return $arr;
    }

    /**
     * object to array
     *
     * @param $object
     *
     * @return mixed
     */
    private function object2array(&$object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }

    public function uploadReportImage(Request $request)
    {
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            return false;
        }

        //file upload success
        if ($file->isValid()) {
            $realPath = $file->getRealPath();
            $fileName = 'TMP' . md5(time()) . "." . $ext;
            $str = file_get_contents($realPath);
            Storage::disk(config('web.imageUpload.disk'))->put($fileName, $str);
        }



        $res['id'] = 0;
        $res['comment'] = '';
        $res['created_at'] = null;
        $res['type'] = 1;
        $res['file_name'] = $fileName;
        $res['file_path'] = $fileName;
        $res['file_path_url'] = '/api/getReportFile?name='.$fileName.'&type=tmp';
        $res['report_id'] = 0;
        $res['updated_at'] = null;
        $result['data'] = $res;

        return $result;
    }

    public function downloadFile(Request $request)
    {
        $index = 1;
        $files = public_path() . str_replace('/shokunin', '', $request->get('path'), $index);
        $name = basename($files);
        return response()->download($files, $name, $headers = ['Content-Type' => 'application/pdf;charset=utf-8']);
    }

    /**
     * move file
     *
     * @param $url
     * @param $report_id
     *
     * @return mixed|string|null
     */
    public function move($url, $report_id)
    {
        $disk = Storage::disk(config('app.imageUpload.disk'));
        if ($disk->exists($url)) {
            // $b = strpos($url, 'TMP');
            //$name = explode('/', $url);
            //$fName = end($name);
            $fName=$url;
            $moveUrl = config('app.imageUpload.report') . 'r' . $report_id . '/' . $fName;
            if ($disk->exists($moveUrl)) {
                return 'already';
            } else {
                $disk->move($url, config('app.imageUpload.report') . 'r' . $report_id . '/' . $fName);
                return $fName;
            }
        }
        return null;
    }

    private function showFile($nodeId, $revNo)
    {
        if ($nodeId > 0) {
            try {
                // リビジョン情報取得
                $rev = Rev::findFirst([
                    'node_id' => $nodeId,
                    'rev_no' => $revNo,
                ]);

                $filePath = implode('/', [self::FILES_PATH_PREFIX, $rev->node_id, $rev->rev_no, $rev->file_name]);

                // ファイルがない
                if (!Storage::disk('s3doc')->exists($filePath)) {
                    Log::info(__METHOD__ . ': file not exists' . $filePath);
                    return ['error' => 'FILE_NOT_EXISTS'];
                }

                $url = Storage::disk('s3doc')->temporaryUrl($filePath, now()->addMinutes(1)); // 1 minute time limit

                return ['fileUrl' => $url];
            } catch (Exception $e) {
                Log::error($e);
                DB::rollback();

                return ['error' => 'UNKNOWN_ERROR'];
            }
        } else {
            try {
                //chat-group file
                $filePath = $this->getSingleChatGroupFilePath(-$nodeId);
                // ファイルがない
                if (!Storage::disk(config('web.imageUpload.disk'))->exists($filePath)) {
                    Log::info(__METHOD__ . ': chat file not exists' . $filePath);

                    return ['error' => 'FILE_NOT_EXISTS'];
                }

                $url = Storage::disk(config('web.imageUpload.disk'))->temporaryUrl($filePath, now()->addMinutes(1)); // 1 minute time limit

                return ['fileUrl' => $url];
            } catch (Exception $e) {
                Log::error($e);
                DB::rollback();

                return ['error' => 'UNKNOWN_ERROR'];
            }
        }
    }

    //get chat group file's S3 path
    private function getSingleChatGroupFilePath($nodeId)
    {
        //make s3 path
        $fileS3Path = '';

        $fileInfo = DB::table("chat_files")
            ->where("id", $nodeId)
            ->first();


        if ($fileInfo) {
            $groupId = $fileInfo->group_id;
            $fileName = $fileInfo->file_name;
            $fileS3Path = sprintf('upload/g%s/%s', $groupId, $fileName);
        }

        return $fileS3Path;
    }
}