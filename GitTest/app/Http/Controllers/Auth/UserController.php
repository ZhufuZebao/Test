<?php
/**
 * ユーザー情報の登録確認・更新等のクラス
 *
 * @author  Miyamoto
 */

namespace App\Http\Controllers\Auth;

use App\User;
use App\Pref;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use DB;
use Session;

//use Illuminate\Contracts\Auth\Authenticatable;

class UserController extends Controller
{
    protected $redirectTo = '/';

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 登録確認ページ
     *
     * @return view
     */
    public function confirm()
    {
        if (\Auth::check()) {
            $user = new User;
            $userData = $user->where('id', Auth::id())->first();

            $data = [
                    'name'      => $userData->name,
                    'email'     => $userData->email,
                    'password'  => $userData->password,
                    'birth'     => $userData->birth,
                    'sex'       => $userData->sex,
                    'zip'       => $userData->zip,
                    'pref'      => $userData->pref,
                    'addr'      => $userData->addr,
                    'telno1'    => $userData->telno1,
                    'telno2'    => $userData->telno2,
                    'comp'      => $userData->comp,
                    'class'     => $userData->class,
                    'file'      => $userData->file,
            ];

            $prefs = Pref::get();

            return view('/auth/confirm', [
                'id'        => Auth::id(),
                'data' => $data,
                'prefs' => $prefs
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * ユーザーの画像アップロード
     *
     */
    public function upload()
    {
//print_r($_FILES);
//echo ini_get('upload_max_filesize'). '<br>';
//echo ini_get('post_max_size'). '<br>';
//echo ini_get('memory_limit'). '<br>';

//exit();
        $name = null;
        if (isset($_FILES["file"]) && $_FILES["file"]["tmp_name"]) {
            list($file_name, $file_type) = explode(".", $_FILES['file']['name']);
            //ファイル名を日付と時刻にしている。
            //$name = Auth::id(). '_'. time(). '.'. $file_type;
            $name = Auth::id(). '.'. $file_type;
            $dir = '/var/www/html/shokunin/photo/users';
            $tmp = $_FILES['file']['tmp_name']; //. '/'. $_FILES['file']['name'];
//echo 'tmp='. $tmp. '<br>';
//echo 'file='. ($dir. "/". $name). '<br>';

            if ($ret = move_uploaded_file($tmp, $dir. "/". $name)) {
                chmod($dir."/".$name, 0644);
                var_dump($dir. "/". $name);
            }
//echo 'ret='. $ret. '<br>';
//print_r(error_get_last());

            $user = new User;
            $data = $user->where('id', Auth::id())->first();
            $data->file = $name;
            $data->save();
        }
//echo 'name='. $name. '<br>';
//exit();

        return redirect('/userconfirm');
    }

    /**
     * 登録変更ページ
     *
     * @return  view
     */
    public function modify()
    {
        if (\Auth::check()) {
            $user = new User;
            $userData = $user->where('id', Auth::id())->first();

            $prefs = Pref::get();

            // セッションから取得
            $pref = Session::get('pref');
            $addr = Session::get('addr');

            // セッションから一旦削除
            Session::forget('pref');
            Session::forget('addr');

            return view('/auth/modify', [
                'id'    => Auth::id(),
                'user'  => $userData,
                'prefs' => $prefs,
                'pref'  => $pref,
                'addr'  => $addr,
            ]);

        } else {
            return redirect('/login');
        }
    }

    /**
     * 変更確認ページ
     *
     * @param   Request $request    FORMからのリクエストパラメータ
     * @return  view
     */
    public function modconfirm(Request $request)
    {
        $request->flash();

        $validator = $this->validator($request->all())->validate();

        $prefs = Pref::get();

        return view('/auth/modconfirm', [
            'data'  => $request->all(),
            'prefs' => $prefs,
        ]);
    }

    /**
     * 更新処理
     *
     * @param   Request $request    FORMからのリクエストパラメータ
     * @return  リダイレクト
     */
    protected function update(Request $request)
    {
        $user = new User;
        $userData = $user->where('id', Auth::id())->first();

        if ($userData) {
            // 変更
            $userData->name     = $request->old('name');
            //$userData->email    = $request->old('email');
            $userData->password = bcrypt($request->old('password'));
            $userData->birth    = $request->old('birth');
            $userData->sex      = $request->old('sex');
            $userData->zip      = $request->old('zip');
            $userData->pref     = $request->old('pref');
            $userData->addr     = $request->old('addr');
            $userData->telno1   = $request->old('telno1');
            $userData->telno2   = $request->old('telno2');
            $userData->comp     = $request->old('comp');
            $userData->class    = $request->old('class');

            $userData->save();
        }

        return redirect($this->redirectTo);
    }

    /**
     * 入力検証
     *
     * @param   array  $data
     * @return  \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($request)
    {
        $validator = Validator::make($request, [
                'name'     => 'required|max:100',
//                'email'    => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
                'zip'      => 'zipcode', //'string|max:8|regex:/^\d{3}-\d{4}$/',
                'addr'     => 'max:192',
                'telno1'   => 'telno',   // 'string|max:13|regex:/^[0-9]{2,5}-[0-9]{2,4}-[0-9]{3,4}$/',
                'telno2'   => 'mobileno',   //['max:13', 'regex:/^\d{3}-\d{4}-\d{4}$/'],
                'comp'     => 'max:64',
                'class'    => 'max:32',
//                'file'     => 'image|max:3000',
        ], [
                'name.required'     => '名前を入力してください。',
                'name.max'     => '名前は全角50文字以内で入力してください。',
//                'email.required'     => 'ユーザID（メールアドレス）を入力してください。',
//                'email.max'     => 'ユーザID（メールアドレス）は255文字以内で入力してください。',
//                'email.unique'     => 'ユーザID（メールアドレス）が既に登録されています。',
                'password.required'     => 'パスワードを入力してください。',
                'password.min'     => 'パスワードは6文字以上で入力してください。',
                'password.confirmed'     => 'パスワードと確認用パスワードが違います。',
                'zip.zipcode'     => '郵便番号を 999-9999 の形で入力してください。',
                //'zip.string'     => '郵便番号を 999-9999 の形で入力してください。',
                //'zip.max'     => '郵便番号を 999-9999 の形で入力してください。',
                //'zip.regex'     => '郵便番号を 999-9999 の形で入力してください。',
                'addr.max'     => '住所は全角80文字以内で入力してください。',
                //'telno1.string'     => '電話番号を入力してください。',
                //'telno1.max'     => '電話番号は13文字以内で入力してください。',
                //'telno1.regex'     => '電話番号を正しく入力してください。',
                'telno1.telno'     => '電話番号は13文字以内で正しく入力してください。',

                //'telno2.max'     => '携帯電話番号は13文字以内で入力してください。',
                //'telno2.regex'     => '携帯電話番号を正しく入力してください。',
                'telno2.mobileno'     => '携帯電話番号を正しく入力してください。',
                'comp.max'     => '会社名・団体名は全角32文字以内で入力してください。',
                'class.max'     => '部署名・役職は全角32文字以内で入力してください。',
                'file.image'    => '画像ファイルをアップロードしてください。',
//                'file.max'     => '画像ファイルは３Ｍ以内でお願いします。',
        ]);

        return $validator;
    }

    /**
     * 郵便番号から住所検索
     *
     * @param   Request $request    FORMからのリクエストパラメータ
     */
    public function searchAddr(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'zip'   => 'required|zipcode',
        ], [
                'zip.required'  => '郵便番号を 999-9999 の形で入力してください。',
                'zip.zipcode'   => '郵便番号を 999-9999 の形で入力してください。',
        ])->validate();

        $request->flash();

        $zipcode = str_replace('-', '', $request->input('zip'));

        $data = DB::table('zipdatas')->where('zipcode', $zipcode)->first();

        if ($data) {
            $id = Pref::getId($data->state);
            $addr = $data->city;
            if (!mb_strpos('以下に掲載が', $data->town) && !mb_strpos('次のビルを除く', $data->town)) {
                $addr .= $data->town;
            }

            $request->session()->flash('pref', $id);
            $request->session()->flash('addr', $addr);
            $request->session()->reflash();
        }

        $backpage = $request->input('backpage');

        return redirect('/'. $backpage);
    }
}
