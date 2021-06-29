<?php

namespace App\Http\Services;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// use JapaneseDate\DateTime as JapaneseDateTime;

class CommonService
{
    /**
     * ファイルアップロード
     * @param Request $request
     * @param string $type
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function upload(Request $request, $type = '')
    {
        $fileKey = $request->fileKey ?? 'file';
        if ($request->hasFile($fileKey)) {
            $file = $request->file($fileKey);
            // ファイルアップロードok
            if ($file->isValid()) {
                // ファイルメッセージについての取得
                $ext = $file->getClientOriginalExtension();     // 拡張子
                $realPath = $file->getRealPath();               // 暫時パス
                $originalName = $file->getClientOriginalName(); // ファイル名
                $newName = $originalName;
                if (is_numeric($type)) {
                    // チャット画面でのファイル送信
                    $newName = date('YmdHis') . $originalName;
                } // プロフィール画像
                elseif ($type == 'users') {
                    $newName = sprintf('%s.%s', Auth::id(), $ext);
                } // プロフィール画像
                elseif ($type == 'groups') {
                    $newName = sprintf('%s.%s', $request->group_id, $ext);
                } // 案件画像
                elseif ($type == 'projects') {
                    $newName = sprintf('%s.%s', $request->project_id, $ext);
                }
                //配置ファイルにuploads
                $filename = $this->getFilePath($type, $newName);
                $fileStream = file_get_contents($realPath);
                Storage::disk(config('web.imageUpload.disk'))->put($filename, file_get_contents($realPath));
                //PDF
                $fileNameArr = explode('.', $filename);
                $ext = $fileNameArr[count($fileNameArr) - 1];
                if (strtoupper($ext) === 'PDF' && !(Storage::disk(config('web.pdfUpload.disk'))->exists($filename))) {
                    Storage::disk(config('web.pdfUpload.disk'))->put($filename, $fileStream);
                }

                return $newName;
            }
        }
        Log::warning('no file uploaded');
        return '';
    }

    /**
     * ファイルを取得
     * @param $type
     * @param $file_name
     * @param bool $isDownload
     * @return \Illuminate\Http\Response
     */
    public function getFile($type, $file_name, $isDownload = false)
    {
        $file = $this->getFilePath($type, $file_name);

        $disk = Storage::disk(config('web.imageUpload.disk'));

        try {
            $contents = $disk->get($file);
            $mimeType = $disk->mimeType($file);
            $headers = [
                'Content-Type' => $mimeType,
            ];
            if ($isDownload) {
                if (is_numeric($type) && $this->isValidDateFileName($file_name)) {
                    $file_name = Str::substr($file_name, 14);
                }
                $headers['Content-Disposition'] = 'attachment; filename="' . urlencode($file_name) . '"';
            }
            return response($contents)->withHeaders($headers);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * ファイル名中の日付チェック
     * @param string $fileName
     * @return bool
     */
    private function isValidDateFileName(string $fileName)
    {
        if (Str::length($fileName) < 14) {
            return false;
        }
        $date = Str::substr($fileName, 0, 14);
        $unixTime = strtotime($date);
        if (!$unixTime) {
            return false;
        }
        if (date('YmdHis', $unixTime) == $date) {
            return true;
        }
    }

    /**
     * ファイルサイズを取得
     * @param $type
     * @param $file_name
     * @return int
     */
    public function getFileSize($type, $file_name)
    {
        $file = $this->getFilePath($type, $file_name);
        $disk = Storage::disk(config('web.imageUpload.disk'));
        try {
            return $disk->size($file);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ファイルのパスを構成
     * @param $type
     * @param $file_name
     * @return string
     */
    private function getFilePath($type, $file_name)
    {
        if (is_numeric($type)) {
            // チャット画面でのファイル送信
            // バケット/upload/g3/ファイル名
            return sprintf('upload/g%s/%s', $type, $file_name);
        } // プロフィール画像
        elseif ($type == 'users' || $type == 'groups' || $type == 'projects') {
            // バケット/users/100.jpg
            // バケット/groups/3.jpg
            // バケット/projects/3.jpg
            return sprintf('%s/%s', $type, $file_name);
        } else {
            return 'not_found.png';
        }
    }

    /**
     * 都道府県、区市町村を搜索
     * @param Request $request
     * @return string
     */
    public function zipCloud(Request $request)
    {
        $result = '';
        try {
            $url = "http://zipcloud.ibsnet.co.jp/api/search?zipcode=" . $request->get('zipcode');
            if ($url != null) {
                $res = file_get_contents($url);
                $resJson = json_decode($res, true);
                $result = $resJson['results'][0];
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return $result;
    }

    /**
     * 一か月前後の六曜と祝日を取得
     * @param $monthDate
     * @return array
     */
    public function getJapaneseMonthWithOthers($monthDate)
    {
        try {
            $date = new JapaneseDateTime($monthDate);
        } catch (\Exception $e) {
            return [];
        }
//        $date->setDay(1);
        // $date->setDate($year, $month, 1);
        $date->modify('+-8 days');
        $monthData = [];
        for ($i = 1; $i <= 48; $i++) {
            $monthData[$date->format('Y/m/d')] = [
                'six_weekday' => $date->six_weekday_text,
                'holiday' => $date->holiday_text,
                'dayOfWeek' => $date->dayOfWeek
            ];
            $date->modify('+1 days');
        }
        return $monthData;
    }

    public function getJapaneseThreeMonthWithOthers($monthDate)
    {
        try {
            $date = new JapaneseDateTime($monthDate);
        } catch (\Exception $e) {
            return [];
        }
        $date->setDay(1);
        // $date->setDate($year, $month, 1);
        $date->modify('+-8 days');
        $monthData = [];
        for ($i = 1; $i <= 108; $i++) {
            $monthData[$date->format('Y/m/d')] = [
                'six_weekday' => $date->six_weekday_text,
                'holiday' => $date->holiday_text,
                'dayOfWeek' => $date->dayOfWeek
            ];
            $date->modify('+1 days');
        }
        return $monthData;
    }
    /**
     * 一週間の六曜と祝日を取得
     * @param $date
     * @return array
     */
    public function getJapaneseWeek($date)
    {
        try {
            $dt = new JapaneseDateTime($date);
        } catch (\Exception $e) {
            return [];
        }
        $week = $dt->dayOfWeek;
        // 日曜日前に変更
        $dt->modify('+-' . ($week + 1) . ' days');
        $weekData = [];
        for ($i = 1; $i <= 7; $i++) {
            $dt->modify('+1 days');
            $weekData[$dt->format('Y/m/d')] = [
                'six_weekday' => $dt->six_weekday_text,
                'holiday' => $dt->holiday_text,
                'dayOfWeek' => $dt->dayOfWeek
            ];
        }
        return $weekData;
    }

    /**
     *  検索キーワードを変換する
     *  DB select like '%' ' _'  を変換する
     *  検索機能で「_」では検索できない
     * @param $str
     * @return mixed
     */
    public function escapeDBSelectKeyword($str)
    {
        $like_escape_char = '\\';

        return str_replace([$like_escape_char, '%', '_'], [
            $like_escape_char.$like_escape_char,
            $like_escape_char.'%',
            $like_escape_char.'_',
        ], $str);
    }
}
