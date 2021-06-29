@extends('layouts.app')

@section('header')
<title>登録画面</title>
<script>
function submitPage() {
    document.input_form.submit();
}
</script>
@endsection

@section('content')

<!--container-->
<div class="container low user">
    <h1>登録画面</h1>

    <form name="input_form" method="POST" action="{{ url('/userupdate') }}" class="form" novalidate>
<?php /*
    <form name="input_form" method="POST" action="/register" class="form" novalidate>
*/ ?>
        {{ csrf_field() }}
    </form>

    <dl>
        <dt>名前</dt>
        <dd>{{ $data['name'] }}</dd>

        <dt>ユーザID</dt>
        <dd>{{ $data['email'] }}<span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>パスワード</dt>
        <dd>**********</dd>

        <dt>誕生日</dt>
        <dd>{{ $data['birth'] }}</dd>

        <dt>性別</dt>
        <dd>男</dd>

<?php /*
        <dt>メールアドレス</dt>
        <dd>{{ $data['mail1'] }}</dd>

        <dt>携帯メールアドレス</dt>
        <dd>{{ $data['mail2'] }}</dd>
*/ ?>
        <dt>住所</dt>
        <dd>@if ($data['zip']) 〒{{ $data['zip'] }} @endif @if ($data['pref'] != '') {{ $prefs[$data['pref']] }} @endif {{ $data['addr'] }}</dd>

        <dt>電話番号</dt>
        <dd>{{ $data['telno1'] }}</dd>

        <dt>携帯電話番号</dt>
        <dd>{{ $data['telno2'] }}</dd>

        <dt>会社名・団体名</dt>
        <dd>{{ $data['comp'] }}</dd>

        <dt>部署名・役職</dt>
        <dd>{{ $data['class'] }}</dd>
    </dl>

    <div class="button green"><a href="javascript:submitPage();">登録</a></div>
    <div class="button gray pencil"><a href="{{ url('/usermodify') }}">変更</a></div>
</div>
<!--/container-->

@endsection

