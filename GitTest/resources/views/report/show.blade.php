@php
$bodyStyle = 'low report';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->id ? "編集" : "新規作成" }} | 日報 | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('report.index') }}">日報</a></h1>

			<ul class="header-nav friend">
				<li><a href="{{ route('report.index') }}">一覧</a></li>
				<li><a href="{{ route('report.create') }}">新規</a></li>
				<li class="current"><a href="{{ route('report.show',['id'=>$model->id]) }}">詳細</a></li>
				<li><a href="{{ route('report.edit',['id'=>$model->id]) }}">編集</a></li>
			</ul>

		    <div class="title-wrap">
				<h2>日報 【{{ $model->log_date }} {{ sprintf('%08d', $model->id) }} {{ $model->title }}】</h2>		

				<div class="button-s"><a href="{{ route('report.index') }}">日報一覧</a></div>
		    </div>

		</header>

	    <!--report-wrapper-->
	    <section class="report-wrapper">
		    <div class="report-deteil-wrap clearfix">
				<img src="{{ asset('/') }}/images/friend-000001.png" alt="{{ $model->user->name }}">

				<dl class="clearfix">
					<dt class="schedule icon-s">作成</dt>
					<dd>{{ $model->log_date }}</dd>

					<dt class="no icon-s">no</dt>
					<dd>{{ sprintf('%08d', $model->id) }}</dd>

					<dt class="name icon-s">案件名</dt>
					<dd>{{ $model->title }}</dd>

					<dt class="user icon-s">作成者氏名</dt>
					<dd>{{ $model->user->name }}</dd>

					<dt class="construction icon-s">工種</dt>
					<dd>{{ $model->work_type }}</dd>

					<dt class="place icon-s">作業場所</dt>
					<dd>{{ $model->location }}</dd>

					<dt class="note icon-s">コメント</dt>
					<dd>{{ $model->note }}</dd>

				    <dt class="period icon-s">明日の目標</dt>
					<dd>{{ $model->goal }}</dd>

				    <dt class="period icon-s">明日気を付けること</dt>
					<dd>{{ $model->tips }}</dd>

				</dl>

				<div class="clearfix"></div>

				<div class="button-wrap clearfix">
					<div class="button-lower"><a href="{{ route('report.edit',['id'=>$model->id]) }}">この日報を編集する</a></div>
					<div class="button-lower"><a href="friend-to_offer.html">この担当者にオファーする</a></div>
					<div class="button-lower"><a href="{{ route('report.create') }}">新しい日報を作成する</a></div>
					<div class="button-lower"><a href="{{ route('report.index') }}">日報一覧をみる</a></div>
				</div>

		    </div>

	    </section>

@endsection
