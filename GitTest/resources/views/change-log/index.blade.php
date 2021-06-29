@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title> 変更履歴一覧 | {{ config('app.name') }}</title>
@endsection

@section('content')
	<header>
		<h1><a href="{{ route('change-log.index') }}">変更履歴</a></h1>

		<ul class="header-nav customer">
			<li class="current"><a href="{{ route('change-log.index') }}">一覧</a></li>
			<li><a href="{{ route('customer.show',['id'=>1]) }}">詳細</a></li>
		</ul>

		<div class="title-wrap">

			<h2>変更履歴一覧</h2>

			<div class="button-s">検索</div>

			<dl class="header-friend-serch clearfix">
				<dt>フリーワード</dt>
				<dd><input tupe="text"></dd>
			</dl>				
		</div>
	</header>

	<!--customer-wrapper-->
	<section class="customer-wrapper">
		<ul class="customer-serch clearfix">
			<li class="customer-mail">
				変更日時
				<span class="button-s sort"></span>
			</li>
			<li class="customer-name">
				ユーザ名
				<span class="button-s sort"></span>
			</li>
			<li class="customer-address">
                IP
				<span class="button-s sort"></span>
			</li>
			<li class="customer-tel">
				テーブル
				<span class="button-s sort"></span>
			</li>
			<li class="customer-note">
				内容
				<span class="button-s sort"></span>
			</li>
		</ul>

		<ul>
            @foreach ($models as $model)
			    <li class="clearfix">
				    <a href="{{ route('change-log.show',['id'=>$model->id]) }}">
				        <span class="customer-mail">{{ $model->created_at }}</span>
					    <span class="customer-name">{{ $model->user_id }}</span>
				        <span class="customer-address">{{ $model->ip }}</span>
				        <span class="customer-tel">{{ $model->tbl_name }}</span>
				        <span class="customer-note">{{ implode(',', array_keys($model->getAfter())) }}</span>
                    </a>
			    </li>
            @endforeach
		</ul>
	</section>
	<!--/customer-wrapper-->
@endsection
