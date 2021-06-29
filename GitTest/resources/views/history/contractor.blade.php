@extends('layouts.app')

@section('header')
<title>会社概要</title>
@endsection

@section('content')

<!--container-->
<div class="container low cliant">
    <h1>会社概要</h1>

    <dl class="step-top">
        <dt>会社名</dt>
        <dd>{{ $contractors->name }}</dd>

        <dt>設立</dt>
        <dd>{{ $contractors->establishment_y }}年{{ (int)$contractors->establishment_m }}月</dd>

        <dt>所在地</dt>
        <dd>〒{{ $contractors->zip }} {{ $prefs[$contractors->pref] }}{{ $contractors->addr }}</dd>

        <dt>代表者</dt>
        <dd>{{ $contractors->representative }}</dd>

        <dt>事業内容</dt>
        <dd>{{ $contractors->contents }}</dd>
    </dl>
</div>
<!--/container-->

@endsection
