@php
$bodyStyle = 'low report';
@endphp

@extends('layouts.app')

@section('header')
    <title>日報一覧 | {{ config('app.name') }}</title>
@endsection

@section('content')

	<header>
		<h1><a href="report.html">日報</a></h1>

		<ul class="header-nav friend">
			<li class="current"><a href="{{ route('report.index') }}">一覧</a></li>
			<li><a href="report-detail.html">詳細</a></li>
			<li><a href="{{ route('report.create') }}">新規</a></li>
		</ul>

		<div class="title-wrap">

            {!! Form::open(['method'=>'post','files'=>false]) !!}

			<h2>日報一覧</h2>

            <div><button class="button-s" type="submit">検索</button></div>

			<dl class="header-friend-serch clearfix">
				<dt>フリーワード</dt>
				<dd>
                    {!! Form::text('keyword', $query->keyword, ['placeholder'=>""]) !!}
                </dd>
			</dl>

            {!! Form::close() !!}

		</div>
	</header>

	<!--report-wrapper-->
	<section class="report-wrapper">

		<ul class="report-serch clearfix">
			<li class="report-date">
				日報作成
				<span class="button-s sort"></span>
			</li>
			<li class="report-project-no">
				案件No
				<span class="button-s sort"></span>
			</li>
			<li class="report-project-name">
				案件名
				<span class="button-s sort"></span>
			</li>
			<li class="report-author">
				作成者
				<span class="button-s sort"></span>
			</li>
			<li class="report-work-content">
				作業内容
				<span class="button-s sort"></span>
			</li>
			<li class="report-work-place">
				作業場所
				<span class="button-s sort"></span>
			</li>
			<li class="report-note">
				コメント
				<span class="button-s sort"></span>
			</li>
		</ul>

		<ul>
            @foreach($models as $model)
			    <li class="clearfix">
                    <a href="{{ route('report.show',['id'=>$model->id]) }}">
					    <span class="report-date">{{ $model->log_date }}</span>
					    <span class="report-project-no">{{ sprintf('%08d', $model->id) }}</span>
					    <span class="report-project-name">{{ $model->title }}</span>
					    <span class="report-author">{{ $model->user->name }}</span>
					    <span class="report-work-content">{{ $model->work_type }}</span>
					    <span class="report-work-place">{{ $model->location }}</span>
					    <span class="report-note">{{ $model->note }}</span>
				    </a>
			    </li>
            @endforeach
		</ul>

	</section>

@endsection
