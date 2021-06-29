@extends('layouts.app')

@section('header')
<title>工程管理</title>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css">
@endsection

@section('content')

<!--container-->
<div class="container low step serch">
    <h1>工程管理</h1>

    @foreach ($projects as $project)
    <dl class="step-top">
        <dt>プロジェクト名</dt>
        <dd>{{ $project->name }}</dd>

        <dt>下請け業者</dt>
        <dd>〇〇〇設備</dd>

        <dt>開始日</dt>
        <dd>{{ $project->st_date }}</dd>

        <dt>完了予定日</dt>
        <dd>{{ $project->ed_date }}</dd>

        <dt>責任者</dt>
        <dd>{{ $project->manager }}</dd>

        <dt>作成者</dt>
        <dd>{{ $project->author }}</dd>

        <dt>作成日</dt>
        <dd class="calendar">{{ $project->create_date }}</dd>
    </dl>

    <div class="button green"><a href="{{ url('/processsheet/ref/0/id/'. $project->id) }}">工程表へ</a></div>
    <div class="button gray pencil"><a href="{{ url('/step') }}">条件を変更する</a></div>
    @endforeach
</div>
<!--/container-->

@endsection

@section('footer2')

<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>

<script>
$('#datepicker-01').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: 'images/icon-schedule.png'
});
</script>
<script>
$('#datepicker-02').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: 'images/icon-schedule.png'
});
</script>
<script>
$('#datepicker-03').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: 'images/icon-schedule.png'
});
</script>
@endsection