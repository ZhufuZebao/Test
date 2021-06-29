@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/') }}/css/bootstrap.min.css" type="text/css">
@endsection

@section('content')

    @if($msg = Session::get('msg-success'))
        <p class="alert alert-success">
            {{ $msg }}
        </p>
        {{ Session::forget('msg-success') }}
    @endif

    <p>
        仕事
    </p>

    <div class="col-md-12 alert-dark">
        メッセージ
            <div class="row">
                <div class="col-md-1">
                    <a href="{{ route('worker.show',['id'=>$model->sender_id]) }}">
                        <img src="{{ route('worker.photo',['id'=>$model->sender_id]) }}" width="80">
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('worker.show',['id'=>$model->sender_id]) }}">{{ $model->sender->name }}</a>
                </div>
                <div class="col-md-4">
                    {{ $model->content }}
                </div>
                <div class="col-md-4">
                    {{ $model->created_at }}
                </div>
            </div>
    </div>

    {{-- 職人への返信、または応募済みなら単純にメッセージ送信 --}}        
    @if(\App\JobOffer::where('user_id',Auth::id())->where('id',$model->id)->exists())
        <div class="alert alert-dark">
            {!! Form::open(['route'=>['job-message.store','id'=>$model->id]]) !!}
            {!! Form::textarea('content',null,['maxlength'=>2048]) !!}
            <button type="submit">
                送信
            </button>
            {!! Form::close() !!}
        </div>
    @endif

@endsection

