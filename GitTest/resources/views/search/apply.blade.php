@extends('layouts.app')

@section('header')
<title>求人検索</title>
@endsection

@section('content')

<!--container-->
<div class="container low serch apply">
    <h1>求人</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/applyconfirm') }}">
    {{ csrf_field() }}
    <input type="hidden" name="id" id="id" value="{{ $id }}" />

    <dl>
        <dt>ユーザID</dt>
        <dd>
            {{ $user->email }}
            <input type="hidden" name="email" id="email" value="{{ $user->email }}">

<?php /*
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" placeholder="例）abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span>


            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
*/ ?>
        </dd>

<?php /*
        <dt>パスワード</dt>
        <dd>
            <input type="password" name="password" id="password" value="">


            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </dd>

        <dt>確認用パスワード</dt>
        <dd>
            <input type="password" name="password_confirmation" id="password_confirmation" value="">


            @if ($errors->has('password_confirmation'))
                <span class="help-block">
                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                </span>
            @endif
        </dd>
*/ ?>
        <dt>名前</dt>
        <dd>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" size="10" placeholder="例）山田　太郎" style="ime-mode:active;">

            @if ($errors->has('name'))
                <span class="help-block">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif
        </dd>

        <dt>電話番号</dt>
        <dd>
            <input type="text" name="telno1" id="telno1" value="{{ old('telno1', $user->telno1) }}" placeholder="例）03-1111-2222" style="ime-mode:disabled;">

            @if ($errors->has('telno1'))
                <span class="help-block">
                    <strong>{{ $errors->first('telno1') }}</strong>
                </span>
            @endif
        </dd>

        <dt>携帯電話番号</dt>
        <dd>
            <input type="text" name="telno2" id="telno2" value="{{ old('telno2', $user->telno2) }}" placeholder="例）090-1111-2222" style="ime-mode:disabled;">

            @if ($errors->has('telno2'))
                <span class="help-block">
                    <strong>{{ $errors->first('telno2') }}</strong>
                </span>
            @endif
        </dd>

        <dt>質問・自己PR・備考</dt>
        <dd>
            <textarea name="notes" id="notes"  style="ime-mode:active;">{{ old('notes') }}</textarea>

            @if ($errors->has('notes'))
                <span class="help-block">
                    <strong>{{ $errors->first('notes') }}</strong>
                </span>
            @endif
        </dd>
    </dl>

    <div class="button green"><a href="javascript:document.input_form.submit();">入力内容を確認</a></div>

    </form>
</div>
<!--/container-->

@endsection
