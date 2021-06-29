@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.layout')

@section('header')
    <title> 顧客一覧 | {{ config('app.name') }}</title>
@endsection

@section('content')
	<header>
		<h1><a href="{{ route('customer.index') }}">顧客</a></h1>

		<ul class="header-nav customer">
			<li class="current"><a href="{{ route('customer.index') }}">一覧</a></li>
			<li><a href="{{ route('customer.show',['id'=>1]) }}">詳細</a></li>
			<li><a href="{{ route('customer.create') }}">新規登録</a></li>
		</ul>

		<div class="title-wrap">

            {!! Form::open(['method'=>'post','files'=>false]) !!}

			<h2>顧客一覧</h2>

            <div><button class="button-s" type="submit">検索</button></div>

			<dl class="header-friend-serch clearfix">
				<dt>フリーワード</dt>
				<dd>
                </dd>
			</dl>				

            {!! Form::close() !!}

		</div>
	</header>
	<div id="app">
		<App/>
	</div>

@endsection
