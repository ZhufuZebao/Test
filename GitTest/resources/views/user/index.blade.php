@extends('layouts.app')

@section('header')
<title>登録画面</title>
@endsection

@section('content')

<!--container-->
<div class="container low user">
    <h1>登録画面</h1>

    <div class="log-in">
        <div class="button white"><a href="{{ url('user/registrationl') }}">新規会員登録</a></div>
        <div class="button green"><a href="{{ url('uer/confirmation') }}">会員情報の確認!!</a></div>
    </div>
</div>
<!--/container-->

@endsection
