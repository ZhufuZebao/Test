@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title> 技能一覧 | {{ config('app.name') }}</title>
@endsection

@section('content')
	<header>
		<h1><a href="{{ route('skill.index') }}">技能</a></h1>

		<ul class="header-nav customer">
			<li class="current"><a href="{{ route('skill.index') }}">一覧</a></li>
			<li><a href="{{ route('skill.show',['id'=>1]) }}">詳細</a></li>
			<li><a href="{{ route('skill.create') }}">新規登録</a></li>
		</ul>

		<div class="title-wrap">

            {!! Form::open(['method'=>'post','files'=>false]) !!}

			<h2>技能一覧</h2>

            <div><button class="button-s" type="submit">検索</button></div>

			<dl class="header-friend-serch clearfix">
				<dt>ID</dt>
				<dd>
                    {!! Form::text('id', $query->id, ['placeholder'=>""]) !!}
                </dd>
				<dt>名前</dt>
				<dd>
                    {!! Form::text('name', $query->name, ['placeholder'=>"左官 内装 など"]) !!}
                </dd>
			</dl>

            {!! Form::close() !!}

		</div>
	</header>

	<!--customer-wrapper-->
	<section class="customer-wrapper">
		<ul class="customer-serch clearfix">
			<li class="customer-name">
				ID
				<span class="button-s sort"></span>
			</li>
			<li class="customer-name">
				名前
				<span class="button-s sort"></span>
			</li>
		</ul>

		<ul>
            @foreach ($models as $model)
			<li class="clearfix">
				<a href="{{ route('skill.show',['id'=>$model->id]) }}">
					<span class="customer-name">{{ $model->id }}</span>
					<span class="customer-name">{{ $model->name }}</span>
                </a>
			</li>
            @endforeach
		</ul>

        {{ $models->links() }}

	</section>
	<!--/customer-wrapper-->
@endsection
