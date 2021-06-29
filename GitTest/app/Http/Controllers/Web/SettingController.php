<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User;

class SettingController extends \App\Http\Controllers\Controller
{
    /**
     *設定総目録
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function postReset(Request $request)
    {
        $oldpassword = $request->input('saveForm')['oldPassword'];
        $password = $request->input('saveForm')['password'];
        $data = $request->all();
        $rules = [
            'saveForm.password' => 'required|between:6,20',
        ];
        $messages = [
            'required' => '新パスワードが空です',
            'between' => '新パスワード必要が6〜20桁',
        ];
        $validator = Validator::make($data, $rules, $messages);

        $user = Auth::user();
        if (!Hash::check($oldpassword, $user->password)) {
            return '0';
        }
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        $update = array(
            'password' => bcrypt($password),
        );
        User::where('id', $user->id)->update($update);
        Auth::logout();
        return '1';

    }

    public function getUser()
    {

        $row = Auth::user();
        return $row;
    }

    public function editUser(Request $request)
    {
        $msg = Auth::user();
        $last_name = $request->input('saveForm')['last_name'];
        $first_name = $request->input('saveForm')['first_name'];
        $pref = $request->input('saveForm')['pref'];
        $row = [
            'name' => ($last_name . ' ' . $first_name),
            'last_name' => $last_name,
            'first_name' => $first_name,

            'category' => $request->input('saveForm')['category'],
            'category1' => $request->input('saveForm')['category1'],
            'category2' => $request->input('saveForm')['category2'],
            'category3' => $request->input('saveForm')['category3'],

            'specialty' => $request->input('saveForm')['specialty'],
            'zip' => $request->input('saveForm')['zip'],
            'pref' =>$pref,
            'addr_code' =>$request->input('saveForm')['addr_code'],

            'area1' => ($request->input('saveForm')['area1'] == 0 ? null : $request->input('saveForm')['area1']),
            'area2' => ($request->input('saveForm')['area2'] == 0 ? null : $request->input('saveForm')['area2']),
            'area3' => ($request->input('saveForm')['area3'] == 0 ? null : $request->input('saveForm')['area3']),
            'area4' => ($request->input('saveForm')['area4'] == 0 ? null : $request->input('saveForm')['area4']),
            'area5' => ($request->input('saveForm')['area5'] == 0 ? null : $request->input('saveForm')['area5']),

            'birth' => $request->input('saveForm')['birth'],
            'sex' => $request->input('saveForm')['sex'],
            'license' => $request->input('saveForm')['license'],
            'career' => $request->input('saveForm')['career'],
            'belong' => $request->input('saveForm')['belong'],
            'skill' => $request->input('saveForm')['skill'],
            'desired_condition' => $request->input('saveForm')['desired_condition'],

            'flag1' => ($request->input('saveForm')['flag1'] == '1' ? 1 : 0),
            'flag2' => ($request->input('saveForm')['flag2'] == '1' ? 1 : 0),
            'flag3' => ($request->input('saveForm')['flag3'] == '1' ? 1 : 0),

            'telno1' => $request->input('saveForm')['telno1'],
            'dream' => $request->input('saveForm')['dream'],
            'motto' => $request->input('saveForm')['motto'],
            'things_to_realize' => $request->input('saveForm')['things_to_realize'],

            'updated_at' => date("Y/m/d H:i:s"),
        ];
        User::where('id', $msg->id)->update($row);
    }


}
