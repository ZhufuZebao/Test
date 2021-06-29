@extends('layouts.app')

@section('header')
<title>登録画面</title>
@endsection

@section('content')

<!--container-->
<div class="container low user">
    <h1>登録画面</h1>

    <form>
    <dl>
        <dt>ユーザID</dt>
        <dd><input type="email" value="abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>パスワード</dt>
        <dd><input type="password" value="**********"></dd>

        <dt>誕生日</dt>
        <dd><input type="date" value="1980/12/12"></dd>

        <dt>性別</dt>
        <dd>
            <select>
            <option value="1" selected="selected">男</option>
            <option value="2">女</option>
            </select>
        </dd>

        <dt>メールアドレス</dt>
        <dd><input type="email" value="abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>携帯メールアドレス</dt>
        <dd><input type="email" value="abcde@fghij.co.jp"><span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>郵便番号</dt>
        <dd><input type="text" value="1234567"></dd>

        <dt>都道府県</dt>
        <dd>
            <select name="pref_id">
                <option value="" selected>都道府県</option>
                <option value="1">北海道</option>
                <option value="2">青森県</option>
                <option value="3">岩手県</option>
                <option value="4">宮城県</option>
                <option value="5">秋田県</option>
                <option value="6">山形県</option>
                <option value="7">福島県</option>
                <option value="8">茨城県</option>
                <option value="9">栃木県</option>
                <option value="10">群馬県</option>
                <option value="11">埼玉県</option>
                <option value="12">千葉県</option>
                <option value="13">東京都</option>
                <option value="14">神奈川県</option>
                <option value="15">新潟県</option>
                <option value="16">富山県</option>
                <option value="17">石川県</option>
                <option value="18">福井県</option>
                <option value="19">山梨県</option>
                <option value="20">長野県</option>
                <option value="21">岐阜県</option>
                <option value="22">静岡県</option>
                <option value="23">愛知県</option>
                <option value="24">三重県</option>
                <option value="25">滋賀県</option>
                <option value="26">京都府</option>
                <option value="27">大阪府</option>
                <option value="28">兵庫県</option>
                <option value="29">奈良県</option>
                <option value="30">和歌山県</option>
                <option value="31">鳥取県</option>
                <option value="32">島根県</option>
                <option value="33">岡山県</option>
                <option value="34">広島県</option>
                <option value="35">山口県</option>
                <option value="36">徳島県</option>
                <option value="37">香川県</option>
                <option value="38">愛媛県</option>
                <option value="39">高知県</option>
                <option value="40">福岡県</option>
                <option value="41">佐賀県</option>
                <option value="42">長崎県</option>
                <option value="43">熊本県</option>
                <option value="44">大分県</option>
                <option value="45">宮崎県</option>
                <option value="46">鹿児島県</option>
                <option value="47">沖縄県</option>
            </select>
        </dd>

        <dt>それ以降の住所</dt>
        <dd><input type="text" value="〇〇区〇〇町1-1-1"></dd>

        <dt>電話番号</dt>
        <dd><input type="text" value="0312345678"></dd>

        <dt>携帯電話番号</dt>
        <dd><input type="text" value="09012345678"></dd>

        <dt>会社名・団体名</dt>
        <dd><input type="text" value="あいうえ設備"></dd>

        <dt>部署名・役職</dt>
        <dd><input type="text" value=""></dd>
    </dl>
    </form>

    <div class="button green"><a href="{{ url('/user/confirmation') }}">入力内容を確認</a></div>
</div>
<!--/container-->

@endsection
