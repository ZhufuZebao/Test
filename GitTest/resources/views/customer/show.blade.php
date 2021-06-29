@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->id ? "編集" : "新規作成" }} | 日報 | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('customer.index') }}">日報</a></h1>

			<ul class="header-nav friend">
				<li><a href="{{ route('customer.index') }}">一覧</a></li>
				<li><a href="{{ route('customer.create') }}">新規</a></li>
				<li class="current"><a href="{{ route('customer.show',['id'=>$model->id]) }}">詳細</a></li>
				<li><a href="{{ route('customer.edit',['id'=>$model->id]) }}">編集</a></li>
			</ul>

		    <div class="title-wrap">

                {!! Form::open(['method'=>'post','files'=>false,'route'=>'customer.search']) !!}

			    <h2>顧客詳細</h2>


		    </div>

		</header>

	    <section class="project-wrapper">
		    <div class="project-deteil-wrap clearfix">
			    <dl class="clearfix">

				    <dt class="no icon-s">NO</dt>
				    <dd>{{ sprintf('%08d', $model->id) }}</dd>

				    <dt class="name icon-s">顧客名</dt>
				    <dd>{{ $model->name }}</dd>

			    </dl>
		    </div>

            <div>
                <h3>事業所</h3>
                @foreach($model->offices as $office)
				    <p>
                        <a href="{{ route('customer-office.show',['cid'=>$model->id,'oid'=>$office->id]) }}">
                            {{ $office->name }}
                            〒{{ $office->zip }} {{ array_get($office,'pref.name') }}
                            {{ $office->town }}{{ $office->street }}{{ $office->house }}
                            {{ $office->tel }} / {{ $office->fax }}
                        </a>
                    </p>
                @endforeach
                <p>
                    <a href="{{ route('customer-office.create',['id'=>$model->id]) }}">＋追加</a>
                </p>
            </div>

            <div>
                &nbsp;
            </div>

	    </section>


@endsection
