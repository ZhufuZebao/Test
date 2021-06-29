<?php
/**
 * ログインパスワード・リセット用のクラス
 *
 * @author  Miyamoto
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo;

    public function redirectTo() {
        if ($this->isSmartphone()) {
            $this->redirectTo = '/sp/resetfinish';
        } else {
            $this->redirectTo = '/password/change/success';
        }
        return $this->redirectTo;
}

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $validatePassword = function (array $credentials) {
            [$password, $confirm] = [
                $credentials['password'],
                $credentials['password_confirmation'],
            ];

            return $password === $confirm;
        };
        $this->broker()->validator($validatePassword);
    }

    public function success()
    {
        return view('auth.passwords.success');
    }

    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        // パスワードルール変更 #1524
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', 'min:6', 'max:100', 'regex:/^[0-9a-zA-Z-]*$/'],
        ];
    }

    public function isSmartphone() {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        if( (strpos($ua,'iPhone') !== false) || (strpos($ua,'iPod') !== false) || (strpos($ua,'Android') !== false) ) {
            return true;
        } else {
            return false;
        }
    }
}
