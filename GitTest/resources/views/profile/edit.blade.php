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

    {!! Form::open(['method'=>'post','files'=>true]) !!}

	<section class="dashubord-schedule-wrapper">
        <p>プロファイルの編集</p>
        <div class="alert alert-dark">
            見出し
            <span class="badge badge-warning">必須</span>
            {!! Form::text('title', $model->title,['size'=>72]) !!}
            @if($errors->has('title'))
                <span class="text-danger">
                    {{ $errors->first('title') }}
                </span>
            @endif
        </div>

        <div class="alert alert-dark">
            写真
            <span class="badge badge-secondary">任意</span>
            @if($model->photo)
                <img src="{{ route('profile.photo') }}" width="80">
            @else
                <img src="{{ asset('/images/user.png') }}" width="80">
            @endif
            {!! Form::file('image') !!}
            @if($errors->has('image'))
                <span class="text-danger">
                    {{ $errors->first('image') }}
                </span>
            @endif
        </div>

        紹介文
        <span class="badge badge-warning">必須</span>

        <div class="alert alert-dark">
            {!! Form::textarea('content', $model->content) !!}
            @if($errors->has('content'))
                <span class="text-danger">
                    {{ $errors->first('content') }}
                </span>
            @endif
            (2048字)
        </div>

        <button type="submit">保存</button>

        @foreach($errors->all() as $error)
            {{ $error }}
        @endforeach

	</section>

    {!! Form::close() !!}

@endsection
