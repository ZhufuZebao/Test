<?php
/**
 * アルバムテーブル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Album extends Model
{
    /**
     * カテゴリーテーブルからデータを取得
     *
     * @return  array   取得したデータ
     */
    static function get($group_id, $user_id)
    {
$sql = <<<EOF
select  id, name, user_id
from    albums
where   group_id = ?
order by created_at desc
EOF;

        $data = DB::select($sql, [
                $group_id,
        ]);
        //print_r($data);


        $albumFilePath = '/var/www/laravel/shokunin/storage/app/photo/albums/';

        if (is_array($data)) {
            $dir1 = $albumFilePath. 'g'. $group_id;

            $tmp = $data;

            foreach($tmp as $key => $items) {

                $dir2 = $dir1. '/u'. $items->user_id;
                $dir  = $dir2. '/a'. $items->id;

                $filesArray = self::getFileName($dir);

                $data[$key]->files = $filesArray;

                if (is_array($filesArray)) {
                    $tmp = [];
                    foreach($filesArray as $key2 => $filename) {
                        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
                            while( ($file = readdir($handle)) !== false ) {
                                if( filetype( $path = $dir . '/'. $file ) == "file" ) {
                                    // $file: ファイル名
                                    // $path: ファイルのパス
                                    if ($file == $filename) {
                                        $encoded_data = base64_encode(file_get_contents($path));
                                        $tmp[$key2] = $encoded_data;
                                    }
                                }
                            }
                        }
                    }
                    $data[$key]->images = $tmp;
                } else {
                    $data[$key]->images = "";
                }
            }
        }

        return $data;
    }

    /**
     * 指定されたフォルダ内のファイル名をすべて取得
     *
     * @param   string  $dir
     * @return  array
     */
    static private function getFileName($dir){

        // ディレクトリの存在確認&ハンドルの取得
        if(is_dir($dir) && $handle = opendir($dir)){

            $files = array();

            // ディレクトリ読み込み
            while (($file = readdir($handle)) !== false) {

                if ($file == '..' || $file == '.') {
                    continue;
                }

                // ファイルタイプがfileの場合のみ処理する(ディレクトリとかでない場合)
                if (filetype($path = $dir. '/'. $file) == "file") {

//                    array_push($files, $path);
                    array_push($files, $file);
                }
            }
            return $files;
        }
    }
}