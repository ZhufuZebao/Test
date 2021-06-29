@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->id ? "編集" : "新規作成" }} | 担当者 | {{ array_get($model,'office.customer.name') }} | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('customer.index') }}">日報</a></h1>

			<ul class="header-nav friend">
                @if($model->id)
				    <li class="current"><a href="{{ route('customer-office-person.edit',['oid'=>$model->customer_office_id,'pid'=>$model->id]) }}">編集</a></li>
                @else
				    <li class="current"><a href="{{ route('customer-office-person.create',['oid'=>$model->customer_office_id]) }}">新規</a></li>
                @endif
				<li><a href="{{ route('customer-office-person.show',['oid'=>$model->customer_office_id,'pid'=>$model->id]) }}">詳細</a></li>
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
                        {!! Form::text('name', $model->name, ['placeholder'=>"担当者氏名"]) !!}
                        @if ($errors->has('name'))
                            <div class="error">{{ $errors->first('name') }}</div>
                        @endif
                    </dd>

				    <dt></dt>
				    <dd>
                        役職
                        {!! Form::text('position', $model->position, ['placeholder'=>"役職"]) !!}
                        @if ($errors->has('position'))
                            <div class="error">{{ $errors->first('position') }}</div>
                        @endif
                    </dd>

				    <dt></dt>
				    <dd>
                        部署
                        {!! Form::text('dept', $model->dept, ['placeholder'=>"部署"]) !!}
                        @if ($errors->has('dept'))
                            <div class="error">{{ $errors->first('dept') }}</div>
                        @endif
                    </dd>

				    <dt></dt>
				    <dd>
                        <?php
                        $roles = [];
                        foreach(\App\CustomerRole::all() as $r)
                        {
                        $roles[$r->id] = $r->name;
                        }
                        ?>
                        担当区分（シェフ、店長、厨房担当、など　）
                        {!! Form::select('customer_role_id', $roles, $model->customer_role_id) !!}
                        @if ($errors->has('customer_role_id'))
                            <div class="error">{{ $errors->first('customer_role_id') }}</div>
                        @endif
                    </dd>
                    
				    <dt></dt>
				    <dd>
                        メアド
                        {!! Form::text('email', $model->email, ['placeholder'=>"foo@example.com"]) !!}
                        @if ($errors->has('email'))
                            <div class="error">{{ $errors->first('email') }}</div>
                        @endif
                    </dd>

				    <dt></dt>
				    <dd>
                        請求先
                        {!! Form::checkbox('bill_here', $model->bill_here, ['id'=>'bill_here']) !!}
                        {!! Form::label('bill_here', "上記顧客情報と同じ") !!}
                        @if ($errors->has('bill_here'))
                            <div class="error">{{ $errors->first('bill_here') }}</div>
                        @endif
                    </dd>

                    <dt></dt>
                    <dd>
                        請求先名称
                        {!! Form::text('billing_name',$model->billing_name,['class'=>'billing']) !!}
                        @if ($errors->has('billing_name'))
                            <div class="error">{{ $errors->first('billing_name') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
                        郵便番号
                        {!! Form::text('billing_zip',$model->billing_zip,['placeholder'=>'0000000']) !!}
                        <button type="submit" name="zip2addr" value="billing_">住所検索</button>
                        @if ($errors->has('billing_zip'))
                            <div class="error">{{ $errors->first('billing_zip') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
                        <?php
                        $prefs = [];
                        foreach(\App\Pref::all() as $model)
                        {
                        $prefs[$model->id] = $model->name;
                        }
                        ?>
	                    都道府県
                        {!! Form::select('billing_pref_id',$prefs,$model->billing_pref_id,['class'=>'billing']) !!}
                        @if ($errors->has('billing_pref_id'))
                            <div class="error">{{ $errors->first('billing_pref_id') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
	                    市区町村
                        {!! Form::text('billing_town',$model->billing_town,['class'=>'billing']) !!}
                        @if ($errors->has('billing_town'))
                            <div class="error">{{ $errors->first('billing_town') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
	                    番地
                        {!! Form::text('billing_street',$model->billing_street,['class'=>'billing']) !!}
                        @if ($errors->has('billing_street'))
                            <div class="error">{{ $errors->first('billing_street') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
	                    建物名
                        {!! Form::text('billing_house',$model->billing_house,['class'=>'billing']) !!}
                        @if ($errors->has('billing_house'))
                            <div class="error">{{ $errors->first('billing_house') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
                        電話番号
                        {!! Form::text('billing_tel',$model->billing_tel,['class'=>'billing']) !!}
                        @if ($errors->has('billing_tel'))
                            <div class="error">{{ $errors->first('billing_tel') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
                        FAX
                        {!! Form::text('billing_fax',$model->billing_fax,['class'=>'billing']) !!}
                        @if ($errors->has('billing_fax'))
                            <div class="error">{{ $errors->first('billing_fax') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
	                    部署
                        {!! Form::text('billing_dept',$model->billing_dept,['class'=>'billing']) !!}
                        @if ($errors->has('billing_dept'))
                            <div class="error">{{ $errors->first('billing_dept') }}</div>
                        @endif
                    </dd>
                    <dt></dt>
                    <dd>
                        担当者氏名
                        {!! Form::text('billing_person',$model->billing_person,['class'=>'billing']) !!}
                        @if ($errors->has('billing_person'))
                            <div class="error">{{ $errors->first('billing_person') }}</div>
                        @endif
                    </dd>

				    <dt></dt>
				    <dd>
                        {!! Form::submit("保存") !!}
                    </dd>

				    <dt></dt>
				    <dd>
                        @if ($errors->any())
                            {{ implode('', $errors->all('<div>:message</div>')) }}
                        @endif
                    </dd>

			    </dl>
		    </div>

	    </section>


        {!! Form::close() !!}

		<!--/customer-wrapper-->

@endsection
