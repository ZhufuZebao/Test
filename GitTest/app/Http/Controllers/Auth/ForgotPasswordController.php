<?php
/**
 * ログインパスワードを忘れた方への処理クラス
 *
 * @author  Miyamoto
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validateEmail(Request $request)
    {
        $rule       = [
                'email' => 'required|email'
        ];
        $messages   = [];
        $attributes = [
                'email' => 'メールアドレス',
        ];

        $this->validate(
                $request,
                $rule,
                $messages,
                $attributes
        );

    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    protected function sendResetLinkResponse($response)
    {
        return view('auth.passwords.sent');
    }

}
