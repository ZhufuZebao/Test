<?php
/**
 * ユーザー新規登録ののクラス
 *
 * @author  Miyamoto
 */

namespace App\Http\Controllers\Auth;

use App\User;
use App\Pref;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use DB;
use Session;
//use Illuminate\Contracts\Auth\Authenticatable;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * ユーザー登録画面
     *
     */
    public function showRegistrationForm()
    {
        $prefs = Pref::get();

        // セッションから取得
        $pref = Session::get('pref');
        $addr = Session::get('addr');

        // セッションから一旦削除
        Session::forget('pref');
        Session::forget('addr');

        return view('/auth/register', [
                'prefs' => $prefs,
                'pref'  => $pref,
                'addr'  => $addr,
        ]);
    }

    /**
     * 入力確認画面
     *
     * @param   Request $request    FORMからのリクエストパラメータ
     */
    public function confirmation(Request $request)
    {
        $validator = $this->validator($request->all())->validate();

        $prefs = Pref::get();

        $request->flash();

        return view('/auth/confirmation', [
            'data'  => $request->all(),
            'prefs' => $prefs
        ]);
    }

    /**
     * 入力情報登録
     *
     * @param   Request $request    FORMからのリクエストパラメータ
     */
    public function postRegister(Request $request)
    {
        $data = [
            'name'     => $request->old('name'),
            'email'    => $request->old('email'),
            'password' => $request->old('password'),
            'birth'    => $request->old('birth'),
            'sex'      => $request->old('sex'),
            'zip'      => $request->old('zip'),
            'pref'     => $request->old('pref'),
            'addr'     => $request->old('addr'),
            'telno1'   => $request->old('telno1'),
            'telno2'   => $request->old('telno2'),
            'comp'     => $request->old('comp'),
            'class'    => $request->old('class'),
        ];
//print_r($data);
//exit();

        $validator = $this->validator($data);
        $this->create($data);

        return redirect($this->redirectTo);
    }

    /**
     * 入力検証
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($request)
    {
//print_r($request);
        $validator = Validator::make($request, [
            'name'     => 'required|max:100',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'zip'      => 'zipcode', //'string|max:8|regex:/^\d{3}-\d{4}$/',
            'addr'     => 'max:192',
            'telno1'   => 'telno',   // 'string|max:13|regex:/^[0-9]{2,5}-[0-9]{2,4}-[0-9]{3,4}$/',
            'telno2'   => 'mobileno',   //['max:13', 'regex:/^\d{3}-\d{4}-\d{4}$/'],
            'comp'     => 'max:64',
            'class'    => 'max:32',
        ], [
            'name.required'     => '名前を入力してください。',
            'name.max'          => '名前は全角50文字以内で入力してください。',
            'email.required'    => 'ユーザID（メールアドレス）を入力してください。',
            'email.max'         => 'ユーザID（メールアドレス）は255文字以内で入力してください。',
            'email.unique'      => 'ユーザID（メールアドレス）が既に登録されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.min'      => 'パスワードは6文字以上で入力してください。',
            'password.confirmed'=> 'パスワードと確認用パスワードが違います。',
            'zip.zipcode'       => '郵便番号を 999-9999 の形で入力してください。',
            'addr.max'          => '住所は全角80文字以内で入力してください。',
            'telno1.telno'      => '電話番号は13文字以内で正しく入力してください。',
            'telno2.mobileno'   => '携帯電話番号を正しく入力してください。',
            'comp.max'          => '会社名・団体名は全角32文字以内で入力してください。',
            'class.max'         => '部署名・役職は全角32文字以内で入力してください。',
        ]);

        return $validator;
    }

    /**
     * ユーザー情報新規登録
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'birth'    => $data['birth'],
            'sex'      => $data['sex'],
            'zip'      => $data['zip'],
            'pref'     => $data['pref'],
            'addr'     => $data['addr'],
            'telno1'   => $data['telno1'],
            'telno2'   => $data['telno2'],
            'comp'     => $data['comp'],
            'class'    => $data['class'],
        ]);
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
