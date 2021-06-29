@extends('layouts.app_no_nav')

@section('header')
<title>パスワード・リセット</title>
@endsection
<!-- Main Content -->
@section('content')
<div class="l-login-wrapper reset">

    <h1>shokunin</h1>

    <h2>ご登録のメールアドレスに </h2>
    <h2>パスワード再設定URLを送信いたします。</h2>

    <div class="panel panel-default">
        <div class="panel-body">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/password/email') }}">
                {!! csrf_field() !!}

                <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="メールアドレス">

                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif

                <div class="login-button white"><a href="javascript:document.input_form.submit();">パスワードを再設定する</a></div>

                <div class="l-forgat-wrap l-logindiv-margin"><a href="{{ url('/login') }}">←ログイン画面に戻る</a></div>
            </form>

        </div>
    </div>
</div>
@endsection
