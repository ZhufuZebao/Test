@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->id ? "編集" : "新規作成" }} | 顧客 | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('customer.index') }}">日報</a></h1>

			<ul class="header-nav friend">
				<li><a href="{{ route('customer.index') }}">一覧</a></li>
                @if($model->id)
				    <li class="current"><a href="{{ route('customer.edit',['id'=>$model->id]) }}">編集</a></li>
                @else
				    <li class="current"><a href="{{ route('customer.create') }}">新規</a></li>
                @endif
				<li><a href="{{ route('customer.show',['id'=>$model->id]) }}">詳細</a></li>
			</ul>

		    <div class="title-wrap">

                {!! Form::open(['method'=>'post','files'=>false,'route'=>'customer.search']) !!}

			    <h2>顧客一覧</h2>

                <div><button class="button-s" type="submit">検索</button></div>

			    <dl class="header-friend-serch clearfix">
				    <dt>フリーワード</dt>
				    <dd>
                        {!! Form::text('keyword', null, ['placeholder'=>""]) !!}
                    </dd>
			    </dl>				

                {!! Form::close() !!}

		    </div>

		</header>

	    <!--project-wrapper-->
        {!! Form::open(['method'=>'post']) !!}
	    <section class="project-wrapper">
		    <div class="project-deteil-wrap clearfix">
			    <dl class="clearfix">
				    <dt class="no icon-s">NO</dt>
				    <dd>{{ $model->id ? sprintf('%08d', $model->id) : null}}</dd>

				    <dt class="name icon-s">顧客名</dt>
				    <dd>
                        {!! Form::text('name', $model->name, ['placeholder'=>'顧客名','size' => '64','maxlength'=>256]); !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::submit("保存") !!}
                    </dd>

			    </dl>
		    </div>
	    </section>

        {!! Form::close() !!}

		<!--/customer-wrapper-->

@endsection
