@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->name }} | 資格 | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('qualification.index') }}">資格</a></h1>

			<ul class="header-nav friend">
				<li><a href="{{ route('qualification.index') }}">一覧</a></li>
				<li><a href="{{ route('qualification.create') }}">新規</a></li>
				<li class="current"><a href="{{ route('qualification.show',['id'=>$model->id]) }}">詳細</a></li>
				<li><a href="{{ route('qualification.edit',['id'=>$model->id]) }}">編集</a></li>
			</ul>

		    <div class="title-wrap">

                {!! Form::open(['method'=>'post','files'=>false,'route'=>'qualification.search']) !!}

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

				    <dt class="name icon-s">技能分野</dt>
				    <dd>{{ $model->skill->name }}</dd>

			    </dl>
		    </div>

            <div>
                &nbsp;
            </div>

	    </section>


@endsection
