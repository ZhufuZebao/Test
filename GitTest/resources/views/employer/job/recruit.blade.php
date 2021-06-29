@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/') }}/css/bootstrap.min.css" type="text/css">
@endsection

@section('content')

    <h2>
        {{ $worker->name }}
        <small>さん に仕事を指名します</small>
    </h2> 

    {!! Form::open(['method'=>'post','files'=>true]) !!}

    <p>募集中の仕事</p>
    <div class="row">
        <div class="col-1">
        </div>
        <div class="col-4">
            TITLE
        </div>
        <div class="col-4">
            説明
        </div>
        <div class="col-3">
            種別
        </div>
    </div>

    @if(0 == $jobs->total())
        <div class="alert alert-warning">
            現在有効な求人がありません
        </div>
    @endif

    @foreach($jobs as $job)
        <div class="row">
            <div class="col-1">
                <input type="radio" name="vacancy_id" value="{{ $job->id }}">
            </div>
            <div class="col-4">
                <a href="{{ route('job.show',['id'=>$job->id]) }}">{{ $job->name }}</a>
            </div>
            <div class="col-4">
                {{ \Illuminate\Support\Str::limit($job->description, 72) }}
            </div>
            <div class="col-3">
                {{ $job->skill->name }}
            </div>
        </div>
    @endforeach

    <hr>

    <div>
        {!! Form::label('content',"メッセージ") !!}
    </div>
    <div>
        {!! Form::textarea('content',null,['maxlength'=>2048]) !!}
    </div>

    <hr>

    {!! Form::submit("指名を実行", ['class'=>'btn btn-info']) !!}

    {!! Form::close() !!}

    @if($errors->any())
        <div class="alert alert-danger">
        {!! implode('', $errors->all('<div>:message</div>')) !!}
        </div>
    @endif

@endsection

