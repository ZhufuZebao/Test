@extends('layouts.app_no_nav')

@section('header')
    <title>パスワードを変更</title>
@endsection

@section('content')
    <div class="l-login-wrapper">

        <h2 class="l-login-remail">パスワードを変更いたしました</h2>

        <div class="close-button success-button white"><a href="{{ url('/login') }}">ログイン画面に移動</a></div>
        <div class="reset-foot">
            <h1 class="">shokunin</h1>
        </div>

    </div>
@endsection