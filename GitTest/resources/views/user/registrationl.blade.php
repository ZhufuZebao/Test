@extends('layouts.app')

@section('header')
<title>登録画面</title>
@endsection

@section('content')

<!--container-->
<div class="container low user">
    <h1>登録画面</h1>

    <form name="input_form" method="POST" action="{{ url('/user/confirmation') }}" class="form" novalidate>
    {{ csrf_field() }}
    <dl>
        <dt>ユーザID</dt>
        <dd><input type="email" name="m_id" id="m_id" value="" placeholder="例）abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>パスワード</dt>
        <dd><input type="password" name="m_password" id="m_password" value=""></dd>

        <dt>名前</dt>
        <dd><input type="text" name="m_name1" id="m_name1" value="" size="10" placeholder="例）山田"> <input type="text" name="m_name2" id="m_name2" value="" size="10" placeholder="例）太郎"></dd>

        <dt>誕生日</dt>
        <dd><input type="date" name="m_birth" id="m_birth" placeholder="例）1980/01/01"></dd>

        <dt>性別</dt>
        <dd>
            <select name="m_sex">
            <option value="1" id="m_sex_1" selected="selected">男</option>
            <option value="2" id="m_sex_2">女</option>
            </select>
        </dd>

        <dt>メールアドレス</dt>
        <dd><input type="email" name="m_mail1" id="m_mail1" value="" placeholder="例）abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>携帯メールアドレス</dt>
        <dd><input type="email" name="m_mail2" id="m_mail2" value="" placeholder="例）abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>郵便番号</dt>
        <dd><input type="text" name="m_zip" id="m_zip" value="" placeholder="例）530-0001"></dd>

        <dt>都道府県</dt>
        <dd>
            <select name="m_pref" id="m_pref">
            @foreach($prefs as $index => $name)
                <option value="{{ $index }}" @if(old('m_pref') == $index) selected @endif>{{ $name }}</option>
            @endforeach
            </select>
        </dd>

        <dt>それ以降の住所</dt>
        <dd><input type="text" name="m_addr" id="m_addr" value=""></dd>

        <dt>電話番号</dt>
        <dd><input type="text" name="m_telno1" id="m_telno1" value="" placeholder="例）03-1111-2222"></dd>

        <dt>携帯電話番号</dt>
        <dd><input type="text" name="m_telno2" id="m_telno2" value="" placeholder="例）090-1111-2222"></dd>

        <dt>会社名・団体名</dt>
        <dd><input type="text" name="m_comp" id="m_comp" value=""></dd>

        <dt>部署名・役職</dt>
        <dd><input type="text" name="m_class" id="m_class" value=""></dd>
    </dl>
    </form>

    <div class="button green"><a href="javascript:document.input_form.submit();">入力内容を確認</a></div>
</div>
<!--/container-->

@endsection
