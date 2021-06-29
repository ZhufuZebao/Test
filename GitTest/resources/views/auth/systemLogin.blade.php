@extends('layouts.app_no_nav')

@section('header')
    <title>ログイン</title>
@endsection

@section('content')
    <!--login-wrapper-->
    <div class="l-login-wrapper promisor">
        <div class="main">
            <h1 class="title">shokunin></h1>
            <span class="title_span">管理コンソール</span>
        </div>
        <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/adminLogin') }}">
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

            <div class="login-button white"  onclick="login()"><a href="javascript:void(0)">ログイン</a></div>
        </form>

    </div>
    <script type="text/javascript">
        function login() {
            sessionStorage.removeItem('_user_info');
            document.input_form.submit();
        }
    </script>
@endsection
