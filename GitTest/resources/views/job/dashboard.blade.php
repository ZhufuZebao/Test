@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/') }}/css/bootstrap.min.css" type="text/css">
@endsection

@section('content')
	<section class="dashubord-schedule-wrapper">
        <p>登録中の仕事</p>
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
        @foreach($models as $job)
            <div class="row">
                <div class="col-4">
                    <a href="{{ route('job.show',['id'=>$job->id]) }}">{{ $job->name }}</a>
                </div>
                <div class="col-4">
                    {{ \Illuminate\Support\Str::limit($job->description, 72) }}
                </div>
                <div class="col-4">
                    {{ $job->skill->name }}
                </div>
            </div>
        @endforeach

        <hr>
        <div class="text-right">
            <a href="{{ route('job.search',['posted'=>1]) }}">もっと見る</a>
        </div>
	</section>

	<section class="dashubord-friend-wrapper">
        <div class="alert alert-dark">
            {!! Form::open(['route'=>'job.search']) !!}
            <p>仕事を検索する（受注者メニュー：ユーザが職人の場合）</p>
            <span>キーワード {{ Form::text('keyword',$query->keyword) }}</span>
            <button type="submit">検索</button>
            {!! Form::close() !!}
        </div>

        <hr>

        <p>プロフィール</p>
        <div class="row">
            <p class="col-md-4">
                @if($profile->photo)
                    <img src="{{ route('profile.photo',['id'=>$profile->user_id]) }}" width="80">
                @else
                    <img src="{{ asset('/') }}/images/user.png" width="80">
                @endif
            </p>
            <p class="col-md-8">
                @if($profile->content)
                    {{ \Illuminate\Support\Str::limit($profile->content, 128) }}
                @else
                    <span class="alert alert-danger">プロフィールが未入力です</span>
                @endif
            </p>
        </div>
        <div class="text-right">
            <a href="{{ route('profile.index') }}">プレビュー</a>
        </div>
        
	</section>

	<!--dashubord-project-wrapper-->
	<section class="dashubord-project-wrapper">

        <span>新着メッセージ</span>
        @foreach($job_mesgs as $model)
	    @include('/job-message/item', ['model' => $model])
        @endforeach

        <hr>
        <div class="text-right">
            <a href="{{ route('job-message.index') }}">もっと見る</a>
        </div>
	</section>
	<!--/dashubord-project-wrapper-->

	<!--dashubord-report-wrapper-->
	<section class="dashubord-report-wrapper">

        <div class="alert alert-dark">
            {!! Form::open(['route'=>'worker.search']) !!}
            <p>職人を検索する(発注者メニュー：ユーザが企業の場合)</p>
            <span>キーワード {{ Form::text('keyword',$query->keyword) }}</span>
            <button type="submit">検索</button>
            {!! Form::close() !!}
        </div>

        <hr>

        <p>新しい仕事を追加する</p>
        <a href="{{ route('job.create') }}">追加</a>

	</section>
	<!--/dashubord-report-wrapper-->

@endsection

