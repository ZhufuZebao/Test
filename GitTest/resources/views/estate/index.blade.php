@php
$bodyStyle = 'low project';
@endphp

@extends('layouts.app')

@section('header')
    <title>案件一覧 | {{ config('app.name') }}</title>
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

		<ul class="project-list clearfix">
            @foreach ($models as $estate)
                <li>
			        <div class="project-list-item clearfix">
                        <a class="clearfix" href="{{ route('estate.show', ['id' => $estate->id]) }}">
				            <dl class="clearfix">
					            <dt class="no icon-s">案件番号</dt>
					            <dd>{{ sprintf('%08d', $estate->id) }}</dd>

					            <dt class="name icon-s">案件名</dt>
					            <dd>{{ $estate->project_name }}</dd>

					            <dt class="place icon-s">場所</dt>
					            <dd>
					                <dd>{{ $estate->location_name }}</dd>
                                </dd>
                            </dl>
				            <img src="{{ url('/') }}/images/project-000001.png">
			            </a>
                    </div>
                </li>
            @endforeach

            <li>
			    <div class="project-list-item clearfix">
                    <a class="clearfix" href="{{ route('estate.create') }}">
				        <dl class="clearfix">
					        <dt class="no icon-s">案件番号</dt>
					        <dd>新規作成</dd>

					        <dt class="name icon-s">案件名</dt>
                            <dd>&nbsp;</dd>

					        <dt class="place icon-s">場所</dt>
					        <dd>&nbsp;</dd>
                        </dl>
			        </a>
                </div>
            </li>
		</ul>
	</section>
	<!--/project-wrapper-->

    {{ $models->links() }}

        </div>
        <!--/container-->

@endsection
