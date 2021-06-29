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
				<li class="{{ $model->id ? null : "current" }}"><a href="{{ route('report.create') }}">新規</a></li>
				<li><a href="{{ route('report.show',['id'=>$model->id]) }}">詳細</a></li>
			</ul>

		    <div class="title-wrap">
				<h2>日報 【{{ $model->log_date }} {{ sprintf('%08d', $model->id) }} {{ $model->title }}】</h2>		

				<div class="button-s"><a href="{{ route('report.index') }}">日報一覧</a></div>
		    </div>

		</header>

	    <!--project-wrapper-->
        {!! Form::open(['method'=>'post']) !!}
	    <section class="report-wrapper">
		    <div class="report-deteil-wrap clearfix">

				<img src="{{ asset('/') }}/images/friend-000001.png" alt="{{ $model->user->name }}">

				<dl class="clearfix">
					<dt class="schedule icon-s">作成日時</dt>
				    <dd>{{ $model->log_time ?? null}}</dd>

					<dt class="no icon-s">no</dt>
				    <dd>{{ $model->id ? sprintf('%08d', $model->id) : null}}</dd>

				    <dt class="name icon-s">現場名</dt>
				    <dd>
                        {!! Form::text('location', $model->location, ['placeholder'=>'現場名','size' => '64','maxlength'=>256]); !!}
                    </dd>

				    <dt class="user icon-s">作成者氏名</dt>
				    <dd>{{ ($u = $model->user) ? $u->name : null }}</dd>

					<dt class="construction icon-s">工種</dt>
					<dd>
                        {!! Form::select('work_type', [0 => null, 1 => '床', 2 => '壁', 3 => "屋根"]); !!}
                    </dd>

					<dt class="place icon-s">作業場所</dt>
					<dd><input type="text" value="{{ $model->location }}"></dd>

				    <dt class="period icon-s">作業担当者（複数入力可）</dt>
				    <dd>
                        {!! Form::text('worker', $model->worker, ['placeholder'=>'作業担当者','size' => '64','maxlength'=>256]); !!}
                    </dd>

				    <dt class="period icon-s">コメント</dt>
				    <dd>
                        {!! Form::text('note', $model->note, ['placeholder'=>'コメント','size' => '64','maxlength'=>256]); !!}
                    </dd>

				    <dt class="period icon-s">明日の目標</dt>
				    <dd>
                        {!! Form::text('goal', $model->goal, ['placeholder'=>'明日の目標','size' => '64','maxlength'=>256]); !!}
                    </dd>

				    <dt class="period icon-s">明日気を付けること</dt>
				    <dd>
                        {!! Form::text('tips', $model->tips, ['placeholder'=>"明日気を付けること",'size' => '64','maxlength'=>256]); !!}
                    </dd>

				</dl>

				<div class="clearfix"></div>

				<div class="button-wrap clearfix">
					<div class="button-lower remark">
                        <button type="submit">
                            保存
                        </button>
                    </div>
					<div class="button-lower"><a href="friend-to_offer.html">この担当者にオファーする</a></div>
					<div class="button-lower"><a href="{{ route('report.create') }}">新しい日報を作成する</a></div>
					<div class="button-lower"><a href="{{ route('report.index') }}">日報一覧をみる</a></div>
				</div>

            </div>

	    </section>

        {!! Form::close() !!}

		<!--/report-wrapper-->

@endsection
