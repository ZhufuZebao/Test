@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('header')
    <title>{{ $model->id ? "編集" : "新規作成" }} | 資格 | {{ config('app.name') }}</title>
@endsection

@section('content')

		<header>
			<h1><a href="{{ route('qualification.index') }}">資格</a></h1>

			<ul class="header-nav friend">
				<li><a href="{{ route('qualification.index') }}">一覧</a></li>
				<li><a href="{{ route('qualification.create') }}">新規</a></li>
				<li class="current"><a href="{{ route('qualification.show',['id'=>$model->id]) }}">詳細</a></li>
				<li><a href="{{ route('qualification.edit',['id'=>$model->id]) }}">編集</a></li>
			</ul>

		    <div class="title-wrap">

                {!! Form::open(['method'=>'post','files'=>false,'route'=>'qualification.search']) !!}

			    <h2>技能一覧</h2>

                <div><button class="button-s" type="submit">検索</button></div>

			    <dl class="header-friend-serch clearfix">
				    <dt>ID</dt>
				    <dd>
                        {!! Form::text('id', null, ['placeholder'=>""]) !!}
                    </dd>
				    <dt>名前</dt>
				    <dd>
                        {!! Form::text('name', null, ['placeholder'=>"左官 内装 など"]) !!}
                    </dd>
			    </dl>

                {!! Form::close() !!}

		    </div>

		</header>

	    <section class="project-wrapper">
            {!! Form::open(['method'=>'post','files'=>false]) !!}
		    <div class="project-deteil-wrap clearfix">
			    <dl class="clearfix">

				    <dt class="name icon-s">名前</dt>
				    <dd>
                        {!! Form::text('name', $model->name) !!}
                        @if ($errors->has('name'))
                            <strong>{{ $errors->first('name') }}</strong>
                        @endif
                    </dd>

				    <dt class="name icon-s">技能分野</dt>
				    <dd>
                        <?php
                        $skills = [];
                        foreach(\App\Skill::all() as $skill)
                        {
                        $skills[$skill->id] = $skill->name;
                        }
                        ?>

                        {!! Form::select('skill_id', $skills, $model->skill_id) !!}
                        @if ($errors->has('skill_id'))
                            <strong>{{ $errors->first('skill_id') }}</strong>
                        @endif
                    </dd>

				    <dt></dt>
				    <dd>
                        <div><button class="button-s" type="submit">保存</button></div>
                    </dd>

			    </dl>
		    </div>


            <div>
                &nbsp;
            </div>

            {!! Form::close() !!}
	    </section>


@endsection
