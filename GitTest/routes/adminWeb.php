<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ログアウト
use Illuminate\Support\Facades\Route;
//【管理コンソール】運営側の管理画面再修正
    use Illuminate\Support\Facades\Auth;
    Route::get('/admin/', function () {
        //【管理コンソール】運営側の管理画面再修正
        if (Auth::check()) {
            return view('admin');
        } else {
            return redirect('/loginForm');
        }
    });
Route::post('/adminLogin', 'Admin\EnterpriseController@login');
Route::get('/loginForm', 'Admin\EnterpriseController@loginForm');
Route::middleware('admin')->namespace('Admin')->group(function () {
    Route::get('/loginIndex', 'EnterpriseController@loginIndex');
});
Route::get('admin/logout', 'Auth\LoginController@logout');

