@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/') }}/css/bootstrap.min.css" type="text/css">
@endsection

@section('content')

	<section class="dashubord-schedule-wrapper">
        <p>プレビュー</p>
        <div class="alert alert-dark">
            <p>
                {{ $model->user->name }}さん
            </p>
            <p>
                {{ $model->title }}
            </p>

            <p>
                @if($model->photo)
                    <img src="{{ route('profile.photo') }}" width="80">
                @else
                    <img src="{{ asset('/images/user.png') }}" width="80">
                @endif
            </p>

            <p>
                {{ $model->content }}
            </p>
        </div>

        <a href="{{ route('profile.edit') }}">編集する</a>

	</section>

@endsection
