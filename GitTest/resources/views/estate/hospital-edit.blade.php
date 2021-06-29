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
        <h2>{{ array_get($model,'estate.location_name') }} ＞ {{ array_get($model,'estate.project_name') }} ＞ 病院 ＞ {{ $model->id ? "編集" : "新規" }} </h2>

        {!! Form::open(['method'=>'post']) !!}
		<ul class="project-list clearfix">
            <li>
			    <div class="project-list-item">
				    <dl class="clearfix">

					    <dt class="icon-s"></dt>
					    <dd>
                            病院名
                            &nbsp;
                            {!! Form::text('name', $model->name, ['placeholder'=>"病院名"]) !!}
                            @if ($errors->has('name'))
                                <div class="error">{{ $errors->first('name') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            電話番号
                            &nbsp;
                            {!! Form::text('tel', $model->tel, ['placeholder'=>""]) !!}
                            @if ($errors->has('tel'))
                                <div class="error">{{ $errors->first('tel') }}</div>
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
