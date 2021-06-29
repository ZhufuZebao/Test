@php
$bodyStyle = 'low project';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->name }} | 案件 | {{ config('app.name') }}</title>
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
                案件詳細【{{ sprintf('%08d', $model->id) }} {{ $model->name }}】
            </h2>
		</div>
	</header>

	<!--project-wrapper-->
	<section class="project-wrapper">
		<div class="project-deteil-wrap clearfix">

            @if($model->photo)
			    <img src="{{ route('project.photo',['id'=>$model->id]) }}" data-action="zoom">
            @else
			    <img src="{{ url('/') }}/images/project-000001.png" data-action="zoom">
            @endif

			<dl class="clearfix">
				<dt class="no icon-s">案件番号</dt>
				<dd>{{ sprintf('%08d', $model->id) }}</dd>

				<dt class="name icon-s">案件名</dt>
				<dd>{{ $model->name }}</dd>

				<dt class="schedule icon-s">施工期間</dt>
				<dd><a href="{{ route('scheme.gantt',['id'=>$model->id]) }}">{{ \Carbon\Carbon::parse($model->st_date)->format('Y/m/d')}}～{{ \Carbon\Carbon::parse($model->ed_date)->format('Y/m/d')}}</a></dd>

				<dt class="place icon-s">場所</dt>
                <dd>東京都千代田区神田三崎町<!-- TBD --></dd>

				<dt class="place icon-s">担当会社</dt>
                <dd><a href="{{ route('contractor.show',['id'=>$model->contractor_id]) }}">{{ ($c = $model->contractor) ? $c->name : null }}</a></dd>

			</dl>

			<div class="clearfix"></div>

			<div class="button-wrap clearfix">
				<div class="button-lower"><a href="{{ route('project.edit',['id'=>$model->id]) }}">この案件を編集する</a></div>
				<div class="button-lower"><a href="{{ route('project.create') }}">新規案件を登録する</a></div>
				<div class="button-lower"><a href="{{ route('scheme.gantt',['id'=>$model->id]) }}">工程表</a></div>
			</div>

		</div>
	</section>
	<!--/project-wrapper-->

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
