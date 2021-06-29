@php
$bodyStyle = 'low project';
@endphp

@extends('layouts.app')

@section('header')
    <title>案件一覧 | {{ config('app.name') }}</title>
    <style type="text/css">
     .project-list-item dd { max-width: 100% }
    </style>
@endsection

@section('content')

	<header>
		<h1><a href="{{ route('project.index') }}">案件</a></h1>

		<div class="title-wrap">

			<dl class="project-serch clearfix">
				<dt>案件番号</dt>
				<dd><input tupe="text"></dd>

				<dt>施工期間</dt>
				<dd><input tupe="text"></dd>

				<dt>場所</dt>
				<dd><input tupe="text"></dd>

				<dt>フリーワード</dt>
				<dd><input tupe="text"></dd>
			</dl>

			<div class="button-s">検索</div>
		</div>
	</header>

	<!--project-wrapper-->
	<section class="project-wrapper">
        <h2>{{ array_get($model,'estate.location_name') }} ＞ {{ array_get($model,'estate.project_name') }} ＞ 工種別責任者 ＞ {{ $model->id ? "編集" : "新規" }} </h2>

        {!! Form::open(['method'=>'post']) !!}
		<ul class="project-list clearfix">
            <li>
			    <div class="project-list-item">
				    <dl class="clearfix">

					    <dt class="icon-s"></dt>
					    <dd>
                            <?php
                            $jobs = [];
                            foreach(DB::table('job_types')->get() as $job)
                            $jobs[$job->id] = $job->name;
                            ?>
                            工種
                            &nbsp;
                            {!! Form::select('job_type_id', $jobs, $model->job_type_id) !!}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            工種別責任者
                            &nbsp;
                            {!! Form::text('chief', $model->chief, ['placeholder'=>""]) !!}
                            @if ($errors->has('chief'))
                                <div class="error">{{ $errors->first('chief') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            責任者
                            電話番号
                            &nbsp;
                            {!! Form::text('chief_tel', $model->chief_tel, ['placeholder'=>""]) !!}
                            @if ($errors->has('chief_tel'))
                                <div class="error">{{ $errors->first('chief_tel') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            &nbsp;
                            {!! Form::submit("保存ボタン") !!}
                        </dd>

                    </dl>
                </div>
            </li>

		</ul>
        {!! Form::close() !!}

        @if ($errors->any())
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        @endif

	</section>
	<!--/project-wrapper-->

        </div>
        <!--/container-->

@endsection
