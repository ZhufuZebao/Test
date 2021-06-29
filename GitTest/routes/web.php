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
/* Route::get('/{any?}', function () {
    return view('test');
})->where('any', '.+');
 */
// ログアウト
use Illuminate\Support\Facades\Route;

Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/email_change_verification/{token}', 'Web\PublicController@verifiedEmail')->name('verified_email');
Route::get('/participant/invitation/{token}', 'Web\PublicController@verifiedInvitation');
Route::middleware('certificationPicture')->group(function () {
    Route::get('/file/{type}/{file_name}', 'Web\PublicController@getFile');
    Route::get('/download/{type}/{file_name}', 'Web\PublicController@download');
});
// トップ画面
Route::get('/pub/', 'Web\PublicController@index')->name('public');
Route::get('/', 'Web\DashboardController@index')->name('home');
Route::get('/dashboard/schedule-of-day', 'DashboardController@scheduleOfDay')->name('dashboard.schedule-of-day');

Route::auth();
Route::get('/password/change/success', 'Auth\ResetPasswordController@success');

