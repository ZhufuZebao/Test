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
				<li><a href="{{ route('customer-office-person.edit',['oid'=>$model->customer_office_id,'pid'=>$model->id]) }}">編集</a></li>
				<li class="current"><a href="{{ route('customer-office-person.show',['oid'=>$model->customer_office_id,'pid'=>$model->id]) }}">詳細</a></li>
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

				    <dt class="name icon-s">顧客事業所名</dt>
				    <dd>
                        <a href="{{ route('customer.show',['id'=>array_get($model,'office.customer.id')]) }}">
                            {{ array_get($model,'office.customer.name') }} &gt;
                        </a>
                        <a href="{{ route('customer-office.show',[
                                    'cid'=>array_get($model,'office.customer.id'),
                                    'oid'=>$model->id,
                                    ]) }}">
                            {{ array_get($model,'office.name') }}
                        </a>
                    </dd>
				    <dt class="name icon-s">顧客事業所住所</dt>
				    <dd>
                        {{ array_get($model,'office.zip') }}
                        {{ array_get($model,'office.pref.name') }}
                        {{ array_get($model,'office.town') }}
                        {{ array_get($model,'office.street') }}
                        {{ array_get($model,'office.house') }}
                        {{ array_get($model,'office.tel') }}
                        {{ array_get($model,'office.fax') }}
                    </dd>

				    <dt></dt>
				    <dd>
                        担当者氏名
                        {{ $model->name }}
                    </dd>

				    <dt></dt>
				    <dd>
                        役職
                        {{ $model->position }}
                    </dd>

				    <dt></dt>
				    <dd>
                        部署
                        {{ $model->dept }}
                    </dd>

				    <dt></dt>
				    <dd>
                        担当区分（シェフ、店長、厨房担当、など　）
                        {{ ($r = $model->role) ? $r->name : null }}
                    </dd>
                        
				    <dt></dt>
				    <dd>
                        担当者メール：
                        {{ $model->email }}
                    </dd>

				    <dt></dt>
				    <dd>
                        請求先 - 
                        @if($model->bill_here)
                            担当者と同じ
                        @else
                            <br>
                            名称：{{ $model->billing_name }} <br>
                            部署：{{ $model->billing_dept }} <br>
                            担当者：{{ $model->billing_person }} <br>
                            住所：{{ $model->billing_zip }} 
                            {{ $model->billing_pref }} 
                            {{ $model->billing_town }} 
                            {{ $model->billing_street }} 
                            {{ $model->billing_house }} <br>
                            TEL: {{ $model->billing_tel }} <br>
                            FAX: {{ $model->billing_fax }} <br>
                        @endif
                    </dd>

				    <dt></dt>
				    <dd>
                        {{ $model->zip }} {{ array_get($model,'pref.name') }} {{ $model->town }} {{ $model->house }}
                    </dd>

				    <dt></dt>
				    <dd>
                        {{ $model->fax }}
                    </dd>

			    </dl>
		    </div>

            </div>
	    </section>

@endsection
