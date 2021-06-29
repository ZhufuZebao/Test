@php
$bodyStyle = 'low process';
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
				<li><a href="{{ route('customer-office.edit',['cid'=>$model->customer_id,'oid'=>$model->id]) }}">編集</a></li>
				<li class="current"><a href="{{ route('customer-office.show',['cid'=>$model->customer_id,'oid'=>$model->id]) }}">詳細</a></li>
			</ul>

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

	    <section class="project-wrapper">
		    <div class="project-deteil-wrap clearfix">
			    <dl class="clearfix">

				    <dt class="name icon-s">顧客名</dt>
				    <dd>
                        <a href="{{ route('customer.show',['id'=>$model->customer_id]) }}">
                        {{ ($c = $model->customer) ? $c->name : null }}
                        </a>
                    </dd>

				    <dt></dt>
				    <dd>
                        {{ $model->name }}
                    </dd>

				    <dt></dt>
				    <dd>
                        {{ $model->zip }} {{ array_get($model,'pref.name') }} {{ $model->town }} {{ $model->house }}
                    </dd>

				    <dt></dt>
				    <dd>
                        {{ $model->tel }}
                    </dd>

				    <dt></dt>
				    <dd>
                        {{ $model->fax }}
                    </dd>

			    </dl>
		    </div>

            <div>
                <h3>
                    担当者
                </h3>
                @foreach($model->persons as $person)
                    <a href="{{ route('customer-office-person.show',['oid'=>$person->customer_office_id,'pid'=>$person->id]) }}">
                    <p>
                        n: {{ $person->dept }} {{ $person->position}} {{ $person->name }}
                    </p>
                    <p>
                        e: {{ $person->email }}
                    </p>
                    <p>
                        請求先：
                        @if($person->bill_here)
                            同じ
                        @else
                            {{ $person->billing_dept }} {{ $person->billing_person }} {{ $person->billing_tel }}
                        @endif
                    </p>
                    </a>
                @endforeach
            <p>
                <a href="{{ route('customer-office-person.create',['oid'=>$model->id]) }}">＋追加</a>
            </p>

            </div>
	    </section>

@endsection
