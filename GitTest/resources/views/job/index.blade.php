@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>
    {{-- bootstrap Modal のために、この組み合わせの *.js が必要 --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
@endsection

@section('content')

	<section class="dashubord-schedule-wrapper">

        {!! Form::open(['url'=> route(Route::currentRouteName(), app('request')->all())]) !!}

        @include('job.search-header')

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{ app('request')->input('hired') ? 'active' : null }}" href="{{ route('job.search',['hired'=>1]) }}">契約済</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ app('request')->input('posted') ? 'active' : null }}" href="{{ route('job.search',['posted'=>1]) }}">登録中</a>
            </li>
            <li class="nav-item">
                <a class="nav-link  {{ (! app('request')->input('hired') && ! app('request')->input('posted'))? 'active' : null }}" href="{{ route('job.search') }}">公開中</a>
            </li>
        </ul>

        <div class="row">
            <div class="col-4">
                TITLE
            </div>
            <div class="col-4">
                説明
            </div>
            <div class="col-4">
                種別
            </div>
        </div>

        @if(0 == $models->total())
            <p class="alert alert-light">
                まだ対象の仕事はありません
            </p>
        @endif

        @foreach($models as $job)
            <div class="row">
                <div class="col-4">
                    <a href="{{ route('job.show',['id'=>$job->id]) }}">{{ $job->name }}</a>
                </div>
                <div class="col-4">
                    {{ $job->description }}
                </div>
                <div class="col-4">
                    {{ ($s = $job->skill) ? $s->name : null }}
                </div>
            </div>
        @endforeach

        <?php $models->appends(['pagesize'=>$models->perPage()]) ?>
        {{ $models->links() }}

        <div class="row">
            <div class="col-md-6">
                @if($models->total())
                    全 {{ $models->total() }} 件中、{{ count($models->all()) }} 件を表示
                @endif
            </div>

            <div class="col-md-6 text-right">
                {{ Form::select('pagesize', [10 => 10, 20 => 20, 50 => 50, 100 => 100], $models->perPage(), ['onchange'=>'this.form.submit()']) }}件ずつ表示
            </div>
        </div>

        {!! Form::close() !!}

	</section>


@endsection

