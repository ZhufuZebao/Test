@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/') }}/css/bootstrap.min.css" type="text/css">
@endsection

@section('content')

	<section class="dashubord-schedule-wrapper">
        <p>{{ ($u = $model->user) ? $u->name : null }} さんのプロファイル</p>

        見出し
        <div class="alert alert-dark">
            {{ $model->title ?? "(まだありません)" }}
        </div>

        写真
        <div class="alert alert-dark">
            @if($model->photo)
                <img src="{{ route('worker.photo',['id'=>$model->id]) }}" width="80">
            @else
                <img src="{{ asset('/images/user.png') }}" width="80">
            @endif
        </div>

        紹介文
        <div class="alert alert-dark">
            {{ $model->content ?? "(まだありません)" }}
        </div>

        <a href="{{ route('job.recruit',['worker_id'=>$model->id]) }}">この人に指名する</a>

	</section>

@endsection
