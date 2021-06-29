@extends('layouts.app_no_nav')

@section('header')
<title>パスワード初期化処理</title>
@endsection

@section('content')
<div class="l-login-wrapper reset">

    <h1>site</h1>
    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/password/reset') }}">
        {!! csrf_field() !!}
        <input type="hidden" name="token" value="{{ $token }}">

        <ul>
            <li><input type="email" name="email" value="{{ old('email') }}" placeholder="メールアドレス"></li>
            @if ($errors->has('email'))
                <li class="error">
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                </li>
            @endif
            <li><input type="password" name="password" placeholder="新しいパスワード"></li>
            @if ($errors->has('password'))
                <li class="error">
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                </li>
            @endif
            <li><input type="password" name="password_confirmation" placeholder="新しいパスワード（確認用）"></li>
            @if ($errors->has('password_confirmation'))
                <li class="error">
                    <span class="help-block">
                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                    </span>
                </li>
            @endif
            <li class="error" style="margin-top: 30px">
                    <span class="help-block" style="color: #000000">
                        <strong>※パスワードは6～100文字、使える文字は半角英数字、半角ハイフン(-)です。</strong>
                    </span>
            </li>
        </ul>
        <div class="login-button white"><a href="javascript:document.input_form.submit();">パスワードを変更</a></div>
   </form>

</div>
@endsection
