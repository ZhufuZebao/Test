@extends('layouts.app')

@section('header')
<title>求人検索</title>
@endsection

@section('content')

<!--container-->
<div class="container low serch apply">
    <h1>求人</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/applyregist') }}">
        {{ csrf_field() }}
        <input type="hidden" name="id" id="id" value="{{ $id }}" />

        <dl>
            <dt>名前</dt>
            <dd>{{ $data['name'] }}</dd>

            <dt>ユーザID</dt>
            <dd>{{ $data['email'] }}<span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

            <dt>電話番号</dt>
            <dd>{{ $data['telno1'] }}</dd>

            <dt>携帯電話番号</dt>
            <dd>{{ $data['telno2'] }}</dd>

            <dt>質問・自己PR・備考</dt>
            <dd>{{ $data['notes'] }}</dd>
        </dl>

        <div class="button green"><a href="javascript:document.input_form.submit();">登録</a></div>
        <div class="button gray pencil"><a href="{{ url('/apply/id/'. $id) }}">変更</a></div>

    </form>

</div>
<!--/container-->

@endsection

