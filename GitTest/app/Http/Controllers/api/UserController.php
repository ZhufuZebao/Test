<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Usertemporary;
use Response;
use DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\AuthKeySend;
use App\Mail\RegistarUserMail;

class UserController extends Controller
{
    private $_userFilePath = '/var/www/laravel/shokunin/storage/app/photo/users/';

    public function __construct(){
        $this->content = array();
    }

    /**
     * ログイン認証
     *
     * @return unknown
     */
    public function login(){

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $this->content = $user;
            $this->content['token'] =  $user->createToken('mobile')->accessToken;
//            $this->content['user']  = $user;
            $status = 200;
        }
        else{
            $this->content['error'] = "Unauthorised";
            $status = 401;
        }

        return response()->json($this->content, $status);
    }

    /**
     * アクセストークンからのログイン認証
     * アクセストークン認証自体はrouteのmiddlewareにて実行済
     */
    public function loginByToken() {
        $user = Auth::user();
        $this->content = $user;
        $status = 200;
        return response()->json($this->content, $status);
    }

    /**
     * ログインユーザー情報取得
     * @return ユーザー情報
     */
    public function details(){
        return response()->json(['user' => Auth::user()]);
    }

    /**
     * アカウント管理
     * @return アカウント管理
     */
    public function usualAccount(){
        $status = 200;
        $accessToken = Auth::user()->token();
        $accessToken->revoke();

        $res = [
            'status'        => '0000',
            'message'       => ''
        ];

        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }


    /**
     * ログアウト　アクセストークン削除
     * @return 処理ステータス
     */
    public function logout(){
        $status = 200;
        $accessToken = Auth::user()->token();
        $accessToken->revoke();

        $res = [
            'status'        => '0000',
            'message'       => ''
        ];

        return response()->json($res, $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * emailと認証キーを登録
     *
     */
    public function insertUserTemp(Request $request) {

        $email= $request->input('email');

        $user = DB::table('users')->where('email', $email)->first();

        if (!empty($user)) {
            return 'NG';
        }


        # 32文字のランダムな文字列を作成.
        $auth_key = str_random(32);

        DB::table('usertemporarys')->insert([
                'email'        => $request->input('email'),
                'authkey'      => $auth_key,
                'created_at'   => date("Y/m/d H:i:s"),
        ]);

        // メール送信
        $to = $email;
        Mail::to($to)->send(new AuthKeySend($auth_key));

        if(count(Mail::failures()) > 0){
            return 'MailNG';
        }
        return 'OK';
    }

    /**
     * 認証キーの照合
     *
     */
    public function checkAuthKey(Request $request) {

        $auth_key= $request->input('auth_key');

        return Usertemporary::checkAuthKey($auth_key);
    }

    /**
     * 新規ユーザー登録
     *
     * @param Request $request
     * @return json
     */
    public function insertUser(Request $request) {

        $id = DB::table('users')->insertGetId([
                'name'          => $request->input('last_name') . ' '. $request->input('first_name'),
                'email'         => $request->input('email'),
                'password'      => bcrypt($request->input('password')),

                'last_name'     => $request->input('last_name'),
                'first_name'    => $request->input('first_name'),

                'created_at'    => date("Y/m/d H:i:s"),
                'updated_at'    => date("Y/m/d H:i:s"),
        ]);


        $user = DB::table('users')->where('id', $id)->first();

        // メール送信 ここでメール送信だけエラーが出ても登録はできているのでひとまずエラー拾わない
        $to = $user->email;
        Mail::to($to)->send(new RegistarUserMail($user));

        return response()->json($user);
    }

    /**
     * ユーザー情報更新
     *
     * @param Request $request
     * @return json
     */
    public function updateUser(Request $request) {

        DB::table('users')->where('id', Auth::id())
        ->update([
                'name'          => $request->input('last_name') . ' '. $request->input('first_name'),

                'last_name'     => $request->input('last_name'),
                'first_name'    => $request->input('first_name'),

                'birth'         => $request->input('birth'),
                'sex'           => $request->input('sex'),
                //'zip'           => $request->input('zip'),
                'pref'          => $request->input('pref'),
                'addr_code'     => $request->input('addr_code'),
                //'addr'          => $request->input('addr'),
                'telno1'        => $request->input('telno1'),


                'flag1'         => ($request->input('flag1') == 'true' ? 1 : 0),
                'flag2'         => ($request->input('flag2') == 'true' ? 1 : 0),
                'flag3'         => ($request->input('flag3') == 'true' ? 1 : 0),

                'area1'         => ($request->input('area1') == 0 ? null : $request->input('area1')),
                'area2'         => ($request->input('area2') == 0 ? null : $request->input('area2')),
                'area3'         => ($request->input('area3') == 0 ? null : $request->input('area3')),
                'area4'         => ($request->input('area4') == 0 ? null : $request->input('area4')),
                'area5'         => ($request->input('area5') == 0 ? null : $request->input('area5')),

                'category1'     => $request->input('category1'),
                'category2'     => $request->input('category2'),
                'category3'     => $request->input('category3'),

                'category'      => $request->input('category'),
                'specialty'     => $request->input('specialty'),
                'license'       => $request->input('license'),
                'career'        => $request->input('career'),
                'belong'        => $request->input('belong'),
                'skill'         => $request->input('skill'),

                'desired_condition' => $request->input('condition'),
                'dream'             => $request->input('dream'),
                'motto'             => $request->input('motto'),
                'things_to_realize' => $request->input('realize'),

                'updated_at'    => date("Y/m/d H:i:s"),
        ]);

        return 'OK';
    }

    /**
     * ユーザーの詳細情報を取得する
     *
     * @return JSON
     */
    public function getUserDetails(){

        $data = User::get(Auth::id());

        if (isset($data[0])) {
//print_r($data[0]);
            $data[0]->image = "";
            if ($data[0]->file) {
                $filename = $this->_userFilePath. $data[0]->file;
                try {
                    $encoded_data = base64_encode(file_get_contents($filename));
                    $data[0]->image = $encoded_data;
                } catch (Exception $e) {

                }
            }

            return response()->json($data[0]);
        }

        return null;
    }

    /**
     * 職種・対応可能な分野を全件取得
     *
     * @return unknown
     */
    public function getUserCategory() {

        $category1 = DB::table('mst_category1')->orderBy('id')->get();

        $category2 = DB::table('mst_category2')->orderBy('category1_id')->orderBy('id')->get();

        $category3 = DB::table('mst_category3')->orderBy('category1_id')->orderBy('category2_id')->orderBy('id')->get();

//print_r($category1);
//print_r($category2);
//print_r($category3);

        return response()->json([
                'category1' => $category1,
                'category2' => $category2,
                'category3' => $category3
        ]);
    }

    /**
     * 指定された都道府県内の市区町村を取得
     *
     * @param Request $request
     * @return JSON | NULL
     */
    public function getCity(Request $request) {

        $pref = $request->input('pref');

        if ($pref) {
            if ($pref != '－選択－') {
                $data = DB::table('mst_address_mini')
                ->select('officialCode as id', 'city')
                ->where('pref', $pref)
                ->orderBy('officialCode')
                ->get();

                return response()->json($data);
            }
        }

        return null;
    }

    /**
     * 都道府県を全件取得
     *
     * @return JSON
     */
    public function getPref() {

        $data = DB::table('prefs')
        ->select('id', 'name')
//        ->orderBy('order')
        ->get();

        return response()->json($data);

    }

    /**
     * ユーザーの画像を取得する
     */
    public function getUserPhoto(Request $request) {
        $id = $request->input('user_id');

        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = User::find($id);
        if ($user->file) {
            $filename = $this->_userFilePath. $user->file;
            return @readfile($filename);
        }
    }

    /**
     * ユーザーの画像を更新する
     */
    public function uploadPhotoFile() {

        $user_id    = $_POST['user_id'];
        $file_name  = $_POST['file_name'];

        if (isset($_FILES["upfile"]) && $_FILES["upfile"]["tmp_name"]) {

            $dir = $this->_userFilePath;

            //ディレクトリを作成してその中にアップロードしている。
            if (!file_exists($dir)) {
                //$ret = mkdir($dir, 0755);
                $ret = mkdir($dir, 0777);
                //echo "ret=". $ret. '<br>';
            }
            if (move_uploaded_file($_FILES['upfile']['tmp_name'], $dir. "/". $file_name)) {
                chmod($dir. "/". $file_name, 0644);
                //        var_dump($dir. "/". $name);
                echo "ファイル ".  basename( $_FILES['upfile']['name']). " をアップロードしました。";

                DB::table('users')->where('id', $user_id)->update(['file' => $file_name]);

            } else{
                echo "エラーが発生しました。";
            }
        }

    }


    /**
     * ユーザーの画像を取得する
     */
    public function getUserPhotoImages(Request $request) {

        if (!Auth::check()) {
            return redirect('/login');
        }

        $arrayImages = array();
        $tmp = array();

        // ディレクトリのパス
        $dir = $this->_userFilePath;

        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
            while( ($file = readdir($handle)) !== false ) {
                if( filetype( $path = $dir . $file ) == "file" ) {
                    // $file: ファイル名
                    // $path: ファイルのパス
                    $encoded_data = base64_encode(file_get_contents($path));

                    $tmp['filename'] = $file;
                    $tmp['image'] = $encoded_data;
                    $arrayImages[] = $tmp;
                }
            }
        }

        return response()->json($arrayImages);
    }
}
