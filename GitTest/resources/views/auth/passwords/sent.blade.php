@extends('layouts.app_no_nav')

@section('header')
<title>パスワード・リセット</title>
@endsection

@section('content')
<div class="l-login-wrapper">

    <h2 class="l-login-remail">メールを送信いたしました</h2>
    <p class="l-login-text">{{ request()->get('email') }}にパスワード再設定URLを記載したメールを送信しました。</p>
    <p class="l-login-text"> 24時間以内にパスワードの再設定を行ってください。 </p>

<div class="reset-foot">
    <h1 class="">shokunin</h1>
</div>

</div>
@endsection
