@php
$bodyStyle = 'low process';
@endphp

@extends('layouts.app')

@section('header')
<title>工程一覧 | {{ config('app.name') }}</title>
@endsection

@section('content')

	<header>
		<h1><a href="{{ route('scheme.index') }}">工程</a></h1>

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

	<!--report-wrapper-->
	<section class="report-wrapper">

		<ul class="report-serch clearfix">
			<li class="report-project-no">
				ID
				<span class="button-s sort"></span>
			</li>
			<li class="report-project-name">
				Name
				<span class="button-s sort"></span>
			</li>
			<li class="report-date">
				開始日
				<span class="button-s sort"></span>
			</li>
			<li class="report-date">
				終了日
				<span class="button-s sort"></span>
			</li>
			<li class="report-author">
				場所
				<span class="button-s sort"></span>
			</li>
			<li class="report-work-place">
				担当会社
				<span class="button-s sort"></span>
			</li>
		</ul>

		<ul>
            @foreach($models as $model)
			    <li class="clearfix">
                    <a href="{{ route('scheme.gantt',['id'=>$model->project_id]) }}">
					    <span class="report-project-no">{{ sprintf('%08d', $model->id) }}</span>
					    <span class="report-project-name">{{ $model->name }}</span>
					    <span class="report-date">{{ $model->st_date }}</span>
					    <span class="report-date">{{ $model->ed_date }}</span>
					    <span class="report-author">{{ $model->location }}</span>
					    <span class="report-work-place">
                        @if($c = array_get($model, 'project.contractor'))
                            {{ $c->name }}
                        @endif
                        </span>
				    </a>
			    </li>
            @endforeach
		</ul>

    </section>

@endsection
