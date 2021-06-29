@extends('layouts.app')

@section('header')
<title>工程管理</title>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css">
@endsection

@section('content')

<!--container-->
<div class="container low step new">
    <h1>工程管理</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/processsheet') }}">
    {{ csrf_field() }}
    <input type="hidden" name="ref" id="ref" value="0">

    <dl class="step-top">
        <dt>
            プロジェクト名

            @if ($errors->has('project_name'))
                <span class="help-block">
                    <strong>{{ $errors->first('project_name') }}</strong>
                </span>
            @endif
        </dt>
        <dd>
            <input type="text" name="project_name" id="project_name" value="{{ old('project_name') }}">
        </dd>

        <dt>
            下請け業者

            @if ($errors->has('contractor'))
                <span class="help-block">
                    <strong>{{ $errors->first('contractor') }}</strong>
                </span>
            @endif
        </dt>
        <dd>
            <select name="contractor" id="contractor">
                <option value=""></option>
                @foreach ($contractors as $key => $item)
                <option value="{{ $key }}"@if ($key == old('contractor')) selected @endif>{{ $item }}</option>
                @endforeach
            </select>
        </dd>

        <dt>
            開始日

            @if ($errors->has('st_date'))
                <span class="help-block">
                    <strong>{{ $errors->first('st_date') }}</strong>
                </span>
            @endif
        </dt>
        <dd class="calendar">
<?php /*
            <input type="date" name="st_date" id="datepicker-01" value="{{ old('st_date') }}">
*/ ?>
            <input type="text" name="st_date" id="datepicker-01" value="{{ old('st_date') }}">
        </dd>

        <dt>
            完了予定日

            @if ($errors->has('ed_date'))
                <span class="help-block">
                    <strong>{{ $errors->first('ed_date') }}</strong>
                </span>
            @endif
        </dt>
        <dd class="calendar">
<?php /*
            <input type="date" name="ed_date" id="datepicker-02" value="{{ old('ed_date') }}">
*/ ?>
            <input type="text" name="ed_date" id="datepicker-02" value="{{ old('ed_date') }}">
        </dd>

        <dt>
            責任者

            @if ($errors->has('manager'))
                <span class="help-block">
                    <strong>{{ $errors->first('manager') }}</strong>
                </span>
            @endif
        </dt>
        <dd>
            <input type="name" name="manager" id="manager" value="{{ old('manager') }}">
        </dd>

        <dt>
            作成者

            @if ($errors->has('author'))
                <span class="help-block">
                    <strong>{{ $errors->first('author') }}</strong>
                </span>
            @endif
        </dt>
        <dd>
            <input type="name" name="author" id="author" value="{{ old('author') }}">
        </dd>

        <dt>
            作成日

            @if ($errors->has('create_date'))
                <span class="help-block">
                    <strong>{{ $errors->first('create_date') }}</strong>
                </span>
            @endif
        </dt>
        <dd class="calendar">
<?php /*
            <input type="date" name="create_date" id="datepicker-03" value="{{ old('create_date') }}">
*/ ?>
            <input type="text" name="create_date" id="datepicker-03" value="{{ old('create_date') }}">
        </dd>
    </dl>
    </form>

    <div class="button green"><a href="javascript:document.input_form.submit();">工程表へ</a></div>
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
  buttonImage: '/images/icon-schedule.png'
});
</script>
<script>
$('#datepicker-02').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: '/images/icon-schedule.png'
});
</script>
<script>
$('#datepicker-03').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: '/images/icon-schedule.png'
});
</script>
@endsection
