@extends('layouts.app')

@section('header')
<title>求人検索</title>
@endsection

@section('content')

<!--container-->
<div class="container low serch apply">
    <h1>求人</h1>

    <p>以下の内容で応募しました。</p>

    <dl>
        <dt>名前</dt>
        <dd>{{ $data['name'] }}</dd>

<?php /*
        <dt>ユーザID</dt>
        <dd>{{ $data['email'] }}</dd>
*/ ?>
        <dt>電話番号</dt>
        <dd>{{ $data['telno1'] }}</dd>

        <dt>携帯電話番号</dt>
        <dd>{{ $data['telno2'] }}</dd>

        <dt>質問・自己PR・備考</dt>
        <dd>{{ $data['notes'] }}</dd>
    </dl>

    <div class="back"><a href="{{ url('/search') }}">求人トップへ戻る</a></div>

    </form>

</div>
<!--/container-->

@endsection

