@extends('layouts.app')

@section('header')
<title>求人検索</title>
@endsection

@section('content')

<!--container-->
<div class="container low serch list">
    <h1>求人</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/search') }}">
    {{ csrf_field() }}
    <input type="hidden" name="place" id="place" value="{{ old('place') }}">

    <div class="change">
        <input type="text" name="keyword" id="keyword" value="{{ old('keyword') }}">
        <div class="button gray"><a href="javascript:document.input_form.submit();">条件変更</a></div>
    </div>

    <div class="clearfix"></div>

@if (count($data) == 0)
    <div class="result">検索結果 0件</div>
    <p>検索条件に該当する求人情報はありませんでした。</p>

@else
    <div class="result">検索結果 1-{{ count($data) }} / 1件目</div>

    <ul>
        @foreach ($data as $key => $item)
        <li>
            <a href="{{ url('/jobdetails/id/'. $item->id) }}">
                <span class="title">{{ $item->name }}</span>
                <span class="cliant">{{ $item->contractor_name }}</span>
                <span class="pay">{{ $item->salary }}</span>
                <span class="status">{{ $item->contract_name }}</span>
                <span class="other">{{ $item->contents }}</span>
            </a>
        </li>
        @endforeach
    </ul>
@endif

    </form>

</div>
<!--/container-->

@endsection
