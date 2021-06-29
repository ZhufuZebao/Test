<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpController extends Controller
{
    /**
     * パスワードリセット完了画面表示
     */
    public function resetFinish()
    {
        return view('sp.resetfinish');
    }
}
