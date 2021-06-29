@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->name }} | 技能 | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('skill.index') }}">技能</a></h1>

			<ul class="header-nav friend">
				<li><a href="{{ route('skill.index') }}">一覧</a></li>
				<li><a href="{{ route('skill.create') }}">新規</a></li>
				<li class="current"><a href="{{ route('skill.show',['id'=>$model->id]) }}">詳細</a></li>
				<li><a href="{{ route('skill.edit',['id'=>$model->id]) }}">編集</a></li>
			</ul>

		    <div class="title-wrap">

                {!! Form::open(['method'=>'post','files'=>false,'route'=>'skill.search']) !!}

			    <h2>技能一覧</h2>

                <div><button class="button-s" type="submit">検索</button></div>

			    <dl class="header-friend-serch clearfix">
				    <dt>ID</dt>
				    <dd>
                        {!! Form::text('id', null, ['placeholder'=>""]) !!}
                    </dd>
				    <dt>名前</dt>
				    <dd>
                        {!! Form::text('name', null, ['placeholder'=>"左官 内装 など"]) !!}
                    </dd>
			    </dl>

                {!! Form::close() !!}

		    </div>

		</header>

	    <section class="project-wrapper">
		    <div class="project-deteil-wrap clearfix">
			    <dl class="clearfix">

				    <dt class="no icon-s">ID</dt>
				    <dd>{{ $model->id }}</dd>

				    <dt class="name icon-s">名前</dt>
				    <dd>{{ $model->name }}</dd>

			    </dl>
		    </div>

            <div>
                &nbsp;
            </div>

	    </section>


@endsection
