<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 显示所有应用程序的用户列表。
     *
     * @return Response
     */
    public function index()
    {
        $users = DB::table('user')->get();

        return view('user.index', ['user' => $users]);
    }
}