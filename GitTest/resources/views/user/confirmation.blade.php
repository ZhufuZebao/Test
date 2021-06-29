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

    <form name="input_form" method="POST" action="{{ url('/user/regist') }}" class="form" novalidate>
        {{ csrf_field() }}
    </form>

    <dl>
        <dt>ユーザID</dt>
        <dd>{{ $data['m_id'] }}<span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>パスワード</dt>
        <dd>**********</dd>

        <dt>誕生日</dt>
        <dd>{{ $data['m_birth'] }}</dd>

        <dt>性別</dt>
        <dd>男</dd>

        <dt>メールアドレス</dt>
        <dd>{{ $data['m_mail1'] }}</dd>

        <dt>携帯メールアドレス</dt>
        <dd>{{ $data['m_mail2'] }}</dd>

        <dt>住所</dt>
        <dd>〒{{ $data['m_zip'] }} {{ $prefs[$data['m_pref']] }}{{ $data['m_addr'] }}1</dd>

        <dt>電話番号</dt>
        <dd>{{ $data['m_telno1'] }}</dd>

        <dt>携帯電話番号</dt>
        <dd>{{ $data['m_telno2'] }}</dd>

        <dt>会社名・団体名</dt>
        <dd>{{ $data['m_comp'] }}</dd>

        <dt>部署名・役職</dt>
        <dd>{{ $data['m_class'] }}</dd>
    </dl>

    <div class="button green"><a href="javascript:submitPage();">登録</a></div>
    <div class="button gray pencil"><a href="{{ url('/user/change') }}">変更</a></div>
</div>
<!--/container-->

@endsection

