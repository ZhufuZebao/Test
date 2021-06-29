@extends('layouts.app')

@section('header')
<title>求人検索</title>
@endsection

@section('content')

<!--container-->
<div class="container low serch">
    <h1>求人</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/search') }}">
    {{ csrf_field() }}
    <dl class="serch-top">
        <dt>キーワード<span>（職種・キーワード・会社名など）</span></dt>
        <dd><input type="text" name="keyword" id="keyword" value="{{ old('keyword') }}"></dd>

        <dt>勤務地<span>（都道府県名または市区町村名）</span></dt>
        <dd><input type="text" name="place" id="place" value="{{ old('place') }}" placeholder="例）東京都"></dd>
    </dl>

    <div class="button green"><a href="javascript:document.input_form.submit();">検索</a></div>
    </form>
</div>
<!--/container-->

@endsection
