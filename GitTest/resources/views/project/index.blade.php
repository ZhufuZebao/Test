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

            {!! Form::open(['method'=>'post','files'=>false]) !!}

			<dl class="project-serch clearfix">
				<dt>案件番号</dt>
				<dd>
                    {!! Form::text('id', $query->id, ['placeholder'=>""]) !!}
                </dd>

				<dt>施工期間</dt>
				<dd>
                    {!! Form::text('date', $query->date, ['placeholder'=>""]) !!}
                </dd>

				<dt>場所</dt>
				<dd>
                    {!! Form::text('location', $query->location, ['placeholder'=>""]) !!}
                </dd>

				<dt>フリーワード</dt>
				<dd>
                    {!! Form::text('keyword', $query->keyword, ['placeholder'=>""]) !!}
                </dd>
			</dl>

			<div><button class="button-s" type="submit">検索</button></div>

            {!! Form::close() !!}

		</div>
	</header>

	<!--project-wrapper-->
	<section class="project-wrapper">

		<ul class="project-list clearfix">
            @foreach ($models as $project)
                <li>
			        <div class="project-list-item clearfix">
                        <a class="clearfix" href="{{ route('project.show', ['id' => $project->id]) }}">
				            <dl class="clearfix">
					            <dt class="no icon-s">案件番号</dt>
					            <dd>{{ sprintf('%08d', $project->id) }}</dd>

					            <dt class="name icon-s">案件名</dt>
					            <dd>{{ $project->name }}</dd>

					            <dt class="period icon-s">施工期間</dt>
					            <dd>{{ \Carbon\Carbon::parse($project->st_date)->format('Y/m/d')}}～{{ \Carbon\Carbon::parse($project->ed_date)->format('Y/m/d')}}</dd>

					            <dt class="place icon-s">場所</dt>
					            <dd>
東京都千代田区神田三崎町<!-- TBD --></dd>
                            </dl>
                            @if($project->photo)
				            <img src="{{ route('project.photo',['id'=>$project->id]) }}">
                            @else
				            <img src="{{ url('/') }}/images/project-000001.png">
                            @endif
			            </a>
                    </div>
                </li>
            @endforeach
		</ul>
	</section>
	<!--/project-wrapper-->


    {{ $models->links() }}

        </div>
        <!--/container-->

@endsection
