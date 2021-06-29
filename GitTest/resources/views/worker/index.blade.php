@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title> 職人一覧 | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ url('/') }}/css/bootstrap.min.css" />
@endsection

@section('content')
	<header>
		<h1><a href="{{ route('worker.index') }}">職人</a></h1>

		<ul class="header-nav customer">
			<li class="current"><a href="{{ route('worker.index') }}">一覧</a></li>
		</ul>

		<div class="title-wrap">

            {!! Form::open(['method'=>'get','files'=>false]) !!}

			<h2>職人一覧</h2>

            <div><button class="button-s" type="submit">検索</button></div>

			<dl class="header-friend-serch clearfix">
				<dt>名前</dt>
				<dd>
                    {!! Form::text('name', $query->name, ['placeholder'=>"鈴木"]) !!}
                </dd>
				<dt>キーワード</dt>
				<dd>
                    {!! Form::text('keyword', $query->keyword, ['placeholder'=>""]) !!}
                </dd>
			</dl>

            {!! Form::close() !!}

		</div>
	</header>

	<!--customer-wrapper-->
	<section class="customer-wrapper">
		<ul class="customer-serch clearfix">
			<li class="customer-name">
				名前
				<span class="button-s sort">
                    @if('name' == request()->query('sort','name')){{-- query string `sort` has value `name` --}}
                        <a href="{{ request()->fullUrlWithQuery(['sort'=>'-name']) }}">&nbsp;</a>{{-- sort DESC --}}
                    @else
                        <a href="{{ request()->fullUrlWithQuery(['sort'=>'name']) }}">&nbsp;</a>{{-- sort ASC --}}
                    @endif
                </span>
			</li>
			<li class="customer-name">
                見出し
				<span class="button-s sort">
                @if('title' == request()->query('sort','title'))
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'-title']) }}">&nbsp;</a>{{-- sort DESC --}}
                @else
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'title']) }}">&nbsp;</a>{{-- sort ASC --}}
                @endif
                </span>
			</li>
			<li class="customer-name">
                本文
				<span class="button-s sort">
                @if('content' == request()->query('sort','content'))
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'-content']) }}">&nbsp;</a>{{-- sort DESC --}}
                @else
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'content']) }}">&nbsp;</a>{{-- sort ASC --}}
                @endif
                </span>
			</li>
		</ul>

		<ul>
            @foreach ($models as $model)
			<li class="clearfix">
				<a href="{{ route('worker.show',['id'=>$model->id]) }}">
					<span class="customer-name">{{ ($u = $model->user) ? $u->name : 'no such user' }}</span>
					<span class="customer-name">{{ $model->title }}</span>
					<span class="customer-name">
                        {{ \Illuminate\Support\Str::limit($model->content, 64) }}
                    </span>
                </a>
			</li>
            @endforeach
		</ul>

        {{ $models->links() }}

        全 {{ $models->total() }} 件中 {{ count($models->items()) }} 件を表示しています
	</section>
	<!--/customer-wrapper-->
@endsection
