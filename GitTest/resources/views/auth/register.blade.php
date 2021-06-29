@extends('layouts.app')

@section('header')
<title>登録画面</title>
@endsection

@section('content')

<!--container-->
<div class="container low user">
    <h1>登録画面</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/confirmation') }}">
    {{ csrf_field() }}
    <input type="hidden" name="backpage" value="register" />

    <dl>
        <dt>名前<span class="required">*</span></dt>
        <dd>
            <input type="text" name="name" id="name" value="{{ old('name') }}" size="10" placeholder="例）山田　太郎" style="ime-mode:active;">

            @if ($errors->has('name'))
                <span class="help-block">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif
        </dd>

        <dt>ユーザID<span class="required">*</span></dt>
        <dd>
            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="例）abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span>

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </dd>

        <dt>パスワード<span class="required">*</span></dt>
        <dd>
            <input type="password" name="password" id="password" value="">

            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </dd>

        <dt>確認用パスワード<span class="required">*</span></dt>
        <dd>
            <input type="password" name="password_confirmation" id="password_confirmation" value="">

            @if ($errors->has('password_confirmation'))
                <span class="help-block">
                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                </span>
            @endif
        </dd>

        <dt>誕生日</dt>
        <dd><input type="date" name="birth" id="birth" value="{{ old('birth') }}" placeholder="例）1980/01/01"></dd>

        <dt>性別</dt>
        <dd>
            <select name="sex">
            <option value="1" id="sex_1" selected="selected">男</option>
            <option value="2" id="sex_2">女</option>
            </select>
        </dd>

<?php /*
        <dt>メールアドレス</dt>
        <dd><input type="email" name="mail1" id="mail1" value="{{ old('email') }}" placeholder="例）abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>携帯メールアドレス</dt>
        <dd><input type="email" name="mail2" id="mail2" value="" placeholder="例）abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>
*/ ?>
        <dt>郵便番号 <button onClick="searchAddr()">住所検索</button></dt>
        <dd>
            <input type="text" name="zip" id="zip" value="{{ old('zip') }}" placeholder="例）530-0001" style="ime-mode:disabled;">

            @if ($errors->has('zip'))
                <span class="help-block">
                    <strong>{{ $errors->first('zip') }}</strong>
                </span>
            @endif
        </dd>

        <dt>都道府県</dt>
        <dd>
            <select name="pref" id="pref">
            <option value=""></option>
            @foreach($prefs as $index => $name)
                <option value="{{ $index }}" @if(old('pref') == $index || $pref == $index) selected @endif>{{ $name }}</option>
            @endforeach
            </select>
        </dd>

        <dt>それ以降の住所</dt>
        <dd>
            <input type="text" name="addr" id="addr" value="{{ $addr != '' ? $addr : old('addr') }}" style="ime-mode:active;">

            @if ($errors->has('addr'))
                <span class="help-block">
                    <strong>{{ $errors->first('addr') }}</strong>
                </span>
            @endif
        </dd>

        <dt>電話番号</dt>
        <dd>
            <input type="text" name="telno1" id="telno1" value="{{ old('telno1') }}" placeholder="例）03-1111-2222" style="ime-mode:disabled;">

            @if ($errors->has('telno1'))
                <span class="help-block">
                    <strong>{{ $errors->first('telno1') }}</strong>
                </span>
            @endif
        </dd>

        <dt>携帯電話番号</dt>
        <dd>
            <input type="text" name="telno2" id="telno2" value="{{ old('telno2') }}" placeholder="例）090-1111-2222" style="ime-mode:disabled;">

            @if ($errors->has('telno2'))
                <span class="help-block">
                    <strong>{{ $errors->first('telno2') }}</strong>
                </span>
            @endif
        </dd>

        <dt>会社名・団体名</dt>
        <dd>
            <input type="text" name="comp" id="comp" value="{{ old('comp') }}" style="ime-mode:active;">

            @if ($errors->has('comp'))
                <span class="help-block">
                    <strong>{{ $errors->first('comp') }}</strong>
                </span>
            @endif
        </dd>

        <dt>部署名・役職</dt>
        <dd>
            <input type="text" name="class" id="class" value="{{ old('class') }}" style="ime-mode:active;">

            @if ($errors->has('class'))
                <span class="help-block">
                    <strong>{{ $errors->first('class') }}</strong>
                </span>
            @endif
        </dd>
    </dl>
    </form>

    <div class="button green"><a href="javascript:document.input_form.submit();">入力内容を確認</a></div>
</div>
<!--/container-->

<script>
function searchAddr() {
    document.input_form.action = '/searchAddr';
    document.input_form.submit();
}
</script>
@endsection

