@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->id ? "編集" : "新規作成" }} | 顧客 | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('customer.index') }}">顧客事業所</a></h1>

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

	    <!--project-wrapper-->
        {!! Form::open(['method'=>'post']) !!}
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
                        {!! Form::text('name', $model->name, ['placeholder'=>"事業所名"]) !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::text('zip', $model->zip, ['placeholder'=>"郵便番号"]) !!}
                        {!! Form::submit("住所検索",['name'=>'zip2addr','value'=>1]) !!}
                    </dd>

				    <dt></dt>
				    <dd>
<?php
$prefs = [];
foreach(\App\Pref::all() as $pref)
{
  $prefs[$pref->id] = $pref->name;
}
?>
                        {!! Form::select('pref_id', $prefs, $model->pref_id, ['class'=>'foobar']) !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::text('town', $model->town, ['placeholder'=>"市町村"]) !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::text('street', $model->street, ['placeholder'=>"番地"]) !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::text('house', $model->house,['placeholder'=>"ビル名"]) !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::text('tel', $model->tel,['placeholder'=>"電話番号"]) !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::text('fax', $model->fax,['placeholder'=>"FAX番号"]) !!}
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
