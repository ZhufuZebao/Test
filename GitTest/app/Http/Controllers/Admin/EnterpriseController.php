<?php
/**
 * Created by PhpStorm.
 * User: P0123443
 * Date: 2020/06/01
 * Time: 14:47
 */

namespace App\Http\Controllers\Admin;


use App\Models\Operator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EnterpriseController extends \App\Http\Controllers\Controller
{
    public function login()
    {
        try {
            $email = request('email');
            $password = request('password');
            $userModel = new User();
            $userModel->email = $email;
            $userModel->password = $password;
            $userVal = $userModel->systemValidate();
            if (!$userVal->passes()) {
                return back()->withInput()->withErrors($userVal);
            }

            $sys_email = User::where('email', $email)->count();
            $sys_user_id = User::where('email', $email)->pluck('id')->first();
            if (!$sys_email) {
                $error = trans('messages.error.errorPassword');
                throw new \Exception($error);
            }
//            $sys_admin = User::where('email', $email)->where('sys_admin', '1')->count();
            $sys_admin = Operator::where('user_id',$sys_user_id)->count();
            if (!$sys_admin) {
                $error = trans('messages.error.sysadmin');
                throw new \Exception($error);
            }
            $operator = Operator::where(function ($q1) use ($email) {
                $q1->whereHas('user', function ($query) use ($email) {
                    $query->where('email',$email);
                });
            })->get();
            if ($operator) {
                if (Auth::attempt(['email' => $email, 'password' => $password],true)) {
                    $user = User::where('email', $email)->first();
                    User::where('email', $email)->update(['ip'=>$_SERVER["REMOTE_ADDR"],
                        'remember_token'=>request('_token')]);
                    Auth::login($user);

                    return redirect('/admin');
                } else {
                    $error = trans('messages.error.errorPassword');
                    throw new \Exception($error);
                }
            } else {
                $error = trans('messages.error.errorPassword');
                throw new \Exception($error);
            }

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return back()->withInput()->withErrors(['email' => $msg]);
        }
    }

    public function loginForm(){
        return view('auth.systemLogin');
    }
}
