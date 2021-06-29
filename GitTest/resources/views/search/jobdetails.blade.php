@extends('layouts.app')

@section('header')
<title>求人検索</title>
@endsection

@section('content')

<!--container-->
<div class="container low serch job-details">
    <h1>求人</h1>

    <div class="title">{{ $data->name }}</div>
    <div clss="cliant">{{ $data->contractor_name }} - {{ $data->contractor_addr }}</div>
    <div class="pay">{{ $data->salary }}</div>

    <dl>
        <dt>【仕事内容】</dt>
        <dd>
            <pre>{{ $data->contents }}</pre>
        </dd>

        <dt>【経験者募集】</dt>
        <dd>
            <pre>{{ $data->experiense }}</pre>
        </dd>

        <dt>【雇用形態】</dt>
        <dd>{{ $data->contract_name }}</dd>

        <dt>【給与】</dt>
        <dd>{{ $data->salary }}</dd>

        <dt>【応募条件】</dt>
        <dd>
            <pre>{{ $data->condition }}</pre>
        </dd>

        <dt>【勤務地】</dt>
        <dd>{{ $data->place }}</dd>

        <dt>【勤務時間】</dt>
        <dd>{{ substr($data->st_time, 0, 5) }}～{{ substr($data->ed_time, 0, 5) }}</dd>
    </dl>

    <div class="button green pencil"><a href="{{ url('/apply/id/'. $id) }}">今すぐ応募する</a></div>

    </form>
</div>
<!--/container-->

@endsection
