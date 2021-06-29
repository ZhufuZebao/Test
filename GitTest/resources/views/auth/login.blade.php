@extends('layouts.app_no_nav')

@section('header')
<meta name="description" content="すでに、現場管理アプリ「SITE」のIDをお持ちの会員様のログインページです。無料トライアルIDをお持ちの方もこちらからログインをしていただけます。">
<title>ログイン</title>
@endsection

@section('content')
<!--login-wrapper-->
<div class="l-login-wrapper">

    <h1>ログイン</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/login') }}">
        {!! csrf_field() !!}

        <ul>
            <li><input type="email" name="email" value="{{ old('email') }}" placeholder="メールアドレス">
                @if ($errors->has('email'))
                    <li class="error">
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    </li>
                @endif
            </li>
            <li><input type="password" name="password" placeholder=" パスワード">
                @if ($errors->has('password'))
                    <li class="error">
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    </li>
                @endif
            </li>
        </ul>

        <div class="l-remember-wrap l-logindiv-margin">
            <input type="checkbox" name="remember" id="myCheck">
            <label for="myCheck"></label>
            <label>ログインしたままにする</label>
        </div>

        <div class="login-button white"  onclick="login()"><a href="javascript:void(0)">登录</a></div>

        <div class="l-forgat-wrap"><a href="{{ url('/password/reset') }}">忘记密码了？</a></div>
        <div class="l-forgat-wrap l-logindiv-margin"><a href="{{ config('web.termsOfUse') }}" target="_blank">利用規約</a></div>

    </form>

    <footer class="footer-nav-version">
        {{ config('web.vision') }}
    </footer>
</div>
<script type="text/javascript">
  function login() {
      sessionStorage.removeItem('_user_info');
      document.input_form.submit();
  }
</script>
<style>
    .footer-nav-version{
        text-align:right;
    }
</style>
@endsection
