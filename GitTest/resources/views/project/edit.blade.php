@php
$bodyStyle = 'low project';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->name }} | 案件 | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css">
    <script>
     $('#datepicker-01').datepicker({
         showOn: 'button',
         buttonImageOnly: true,
         buttonImage: '/images/icon-schedule.png'
     });
    </script>
@endsection

@section('content')

	<header>
		<h1><a href="project.html">案件</a></h1>

		<ul class="header-nav project">
			<li><a href="{{ route('project.index') }}">一覧</a></li>
			<li class="current"><a href="{{ route('project.show',['id'=>$model->id]) }}">詳細</a></li>
			<li><a href="{{ route('project.create') }}">新規登録</a></li>
		</ul>

		<div class="title-wrap">
			<h2>
                案件編集【{{ sprintf('%08d', $model->id) }} {{ $model->name }}】
            </h2>
		</div>
	</header>

    {!! Form::open(['method'=>'post','files'=>true]) !!}
	<!--project-wrapper-->
	<section class="project-wrapper">
		<div class="project-deteil-wrap clearfix">

            @if($model->id && $model->photo)
			<img src="{{ route('project.photo',['id'=>$model->id]) }}" data-action="zoom">
            @else
			<img src="{{ url('/') }}/images/project-000001.png" data-action="zoom">
            @endif
            
			<dl class="clearfix">
                @if($model->id)
				<dt class="no icon-s">案件番号</dt>
				<dd>
                    {!! Form::text('id', $model->id, ['disabled'=>"disabled"]) !!}
                </dd>
                @endif

				<dt class="name icon-s">案件名</dt>
				<dd>
                    {!! Form::text('name', $model->name, ['placeholder'=>""]) !!}
                    @if ($errors->has('name'))
                        <div class="error">{{ $errors->first('name') }}</div>
                    @endif
                </dd>

				<dt class="schedule icon-s">施工期間</dt>
				<dd class="period">
                    <input type="date" name="st_date" value="{{ $model->st_date }}" id="datepicker-01">
                    ～
                    {!! Form::date('ed_date', $model->ed_date) !!}
                    @if ($errors->has('st_date'))
                        <div class="error">{{ $errors->first('st_date') }}</div>
                    @endif
                    @if ($errors->has('ed_date'))
                        <div class="error">{{ $errors->first('ed_date') }}</div>
                    @endif
                </dd>

				<dt class="place icon-s">場所</dt>
                <dd>
                    {!! Form::text('foobar', "東京都千代田区神田三崎町",['disabled'=>"disabled"]) !!}<!-- TBD -->
                </dd>

				<dt class="place icon-s">担当会社</dt>
                <dd>
                    <?php
                    $contractors = [];
                    foreach(\App\Contractor::all() as $c)
                    {
                    $contractors[$c->id] = $c->name;
                    }
                    ?>
                    {!! Form::select('contractor_id', $contractors, $model->contractor_id) !!}
                    @if ($errors->has('contractor_id'))
                        <div class="error">{{ $errors->first('contractor_id') }}</div>
                    @endif
                </dd>

			</dl>

			<div class="clearfix"></div>
            {!! Form::file('image') !!}
            @if ($errors->has('image'))
                <div class="error">{{ $errors->first('image') }}</div>
            @endif

			<div class="button-wrap clearfix">
				<div class="button-lower remark">
                    <button type="submit">保存</button>
                </div>
                @if($model->id)
				<div class="button-lower"><a href="{{ route('project.create') }}">新規案件を登録する</a></div>
                @endif
				<div class="button-lower"><a href="{{ route('project.index') }}">案件一覧をみる</a></div>
			</div>

		</div>
	</section>
	<!--/project-wrapper-->
    {!! Form::close() !!}

	<!--
    以下の内容を、他所のページへ引っ越しします

    <div class="btn-group float-right">
        <a href="{{ route('scheme.gantt',['id'=>$model->id]) }}" class="btn btn-light">計画工程表</a>
        <a href="{{ route('task.gantt',['id'=>$model->id]) }}" class="btn btn-light">実施工程表</a>
        <button class="btn btn-primary">両方</button>
    </div>

    @if( 0 == $model->schemes()->exists() )
        <p class="text-muted">
            計画工程表はまだありません
            <a href="{{ route('scheme.edit',['id'=>$model->id]) }}" class="btn btn-outline-success">新規作成</a>
        </p>
    @else
        <div class="container">
            <div id="gantt_scheme" style='width:100%; height:80%;'></div>
        </div>

        <div class="btn-group float-right">
            <a class="btn btn-info" href="{{ route('scheme.edit',['id'=>$model->id]) }}">編集</a>
        </div>
        <button onClick="scheme2pdf()">PDF</button>

        <script>
         gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
         gantt.config.readonly = true;
         gantt.config.columns=[
             {name:"text",       tree:  true },
             {name:"start_date", align: "center" },
             {name:"duration",   align: "center" }
         ];
         gantt.init("gantt_scheme");
         gantt.load("{{ route('scheme.json',['id'=>$model->id]) }}", "json");

         function scheme2pdf()
         {
             var div = document.getElementById('gantt_scheme');

             html2canvas(div).then(canvas => {
                 document.body.appendChild(canvas);

                 var base64 = canvas.toDataURL("image/png").replace("data:image/png;base64,", "");

                 form = document.getElementById('form-generate-pdf');
                 elem = document.getElementById('input-hidden-base64');
                 elem.value = base64;
                 form.submit();
             });
         }

        </script>
    @endif

    @if( 0 == $model->tasks()->exists() )
        <p class="text-muted">
            実施工程表はまだありません
            <a href="{{ route('task.edit',['id'=>$model->id]) }}" class="btn btn-outline-success">新規作成</a>
        </p>
    @else
        <div class="container">
            <div id="gantt_task" style='width:100%; height:80%;'></div>
        </div>

        <div class="btn-group float-right">
            <a class="btn btn-info" href="{{ route('task.edit',['id'=>$model->id]) }}">編集</a>
        </div>
        <button onClick="task2pdf()">PDF</button>

        <script>
         gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
         gantt.config.readonly = true;
         gantt.config.columns=[
             {name:"text",       tree:  true },
             {name:"start_date", align: "center" },
             {name:"duration",   align: "center" }
         ];
         gantt.init("gantt_task");
         gantt.load("{{ route('task.json',['id'=>$model->id]) }}", "json");

         function task2pdf()
         {
             var div = document.getElementById('gantt_task');

             html2canvas(div).then(canvas => {
                 document.body.appendChild(canvas);

                 var base64 = canvas.toDataURL("image/png").replace("data:image/png;base64,", "");

                 form = document.getElementById('form-generate-pdf');
                 form.action = "{{ route('task.pdf',['id'=>$model->id]) }}";
                 elem = document.getElementById('input-hidden-base64');
                 elem.value = base64;
                 form.submit();
             });
         }

        </script>
    @endif

    -->
@endsection

