@extends('layouts.app')

@section('header')
<title>受注履歴</title>
@endsection

@section('content')

<!--container-->
<div class="container low history poroject">
    <h1>受注履歴</h1>

    <dl class="step-top">
        <dt>プロジェクト名</dt>
        <dd>{{ $projects->name }}</dd>

        <dt>下請け業者</dt>
        <dd>{{ $projects->contractor_name }}</dd>

        <dt>開始日</dt>
        <dd>{{ $projects->st_date }}</dd>

        <dt>完了予定日</dt>
        <dd>{{ $projects->ed_date }}</dd>

        <dt>責任者</dt>
        <dd>{{ $projects->manager }}</dd>

        <dt>作成者</dt>
        <dd>{{ $projects->author }}</dd>

        <dt>作成日</dt>
        <dd class="calendar">{{ $projects->create_date }}</dd>
    </dl>

    <div class="button back"><a href="{{ url('/history') }}">受注履歴トップへ戻る</a></div>
</div>
<!--/container-->

@endsection
