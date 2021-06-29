@extends('layouts.app')

@section('header')
<title>求人検索</title>
@endsection

@section('content')

<!--container-->
<div class="container low user">
    <h1>登録画面</h1>

    <div class="log-in">
    @if (!Auth::check())
        <div class="button white"><a href="{{ url('/register') }}">新規会員登録</a></div>
    @endif
        <div class="button green"><a href="{{ url('/userconfirm') }}">会員情報の確認</a></div>
    </div>
</div>
<!--/container-->

@endsection
