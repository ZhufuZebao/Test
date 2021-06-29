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
    @if($msg = Session::get('msg-error'))
        <p class="alert alert-error">
            {{ $msg }}
        </p>
        {{ Session::forget('msg-error') }}
    @endif

    <p>
        仕事
    </p>

    <div class="row col-md-12">
        <div class="col-md-6">
            名称
            <p class="alert alert-dark">
                {{ $model->name }}
            </p>
        </div>
        <div class="col-md-6">
            技能
            <p class="alert alert-dark">
                {{ $model->skill->name }}
            </p>
        </div>
        <div class="col-md-6">
            請負企業
            <p class="alert alert-dark">
                {{ $model->contractor->name }}
            </p>
        </div>
        <div class="col-md-6">
            発注者
            <p class="alert alert-dark">
                {{ $model->user->name }}
            </p>
        </div>
    </div>

    <div class="col-md-12">
        紹介文
        <p class="alert alert-dark">
            {{ $model->description }}
        </p>
    </div>
    
    <div class="col-md-12">
        状態
        <p class="alert alert-dark">
            @if($model->status->isDraft())
                <span class="badge badge-secondary">一時保存</span>
            @elseif($model->status->isOpen())
                <span class="badge badge-info">公開中</span>
            @elseif($model->status->isClosed())
                <span class="badge badge-danger">停止中</span>
            @else
                <span class="badge badge-warning">エラー</span>
            @endif

	    {{-- もし作成者が自分なら「編集」を表示 --}}
	    @if(Auth::id() == $model->created_by)
		@if($model->status->isDraft())
                    <a href="{{ route('job.open',['id'=>$model->id]) }}">公開する</a>
		@elseif($model->status->isOpen())
                    <a href="{{ route('job.close',['id'=>$model->id]) }}">停止する</a>
		@endif
	    @endif
        </p>
    </div>

    <div class="col-md-12 alert-info">
        応募した人:
        <span class="badge badge-pill badge-secondary"> {{ $model->offers()->count() }} 人 </span>
        @foreach($model->offers as $offer)
        <?php $worker = $offer->worker; ?>
	        <hr>
            <div class="row small">
                <div class="col-md-3">
	                <a href="{{ route('worker.show',['id'=>$offer->worker_id]) }}">
	                    @if($worker->photo)
		                    <img src="{{ route('worker.photo',['id'=>$offer->worker_id]) }}" width="32">
	                    @else
		                    <img src="{{ url('/') }}/images/user.png" width="32">
	                    @endif
	                    <small>{{ $worker->name }}</small>
	                </a>
                </div>
                <div class="col-md-1">
	                @if($offer->hired)
	                    <small class="badge badge-pill badge-success">内定</small>
	                @elseif(false === $offer->hired)
	                    <small class="badge badge-pill badge-secondary">辞退</small>
	                @else
	                    <small>不明</small>
	                @endif
                </div>
                <div class="col-md-1">
	                @if($offer->accepted)
	                    <small class="badge badge-pill badge-success">承諾</small>
	                @elseif(false === $offer->accepted)
	                    <small class="badge badge-pill badge-secondary">辞退</small>
	                @else
	                    <small>不明</small>
	                @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="col-md-12">
        &nbsp;
    </div>

    <div class="col-md-12 alert-dark">
        メッセージ
        @foreach($mails as $mail)
	        @include('/job-message/item',['model'=>$mail])
        @endforeach
        <div class="alert-dark">
            {{ $mails->links() }}
        </div>
    </div>

    {{-- もし作成者が自分なら「編集」を表示 --}}
    @if(Auth::id() == $model->created_by)
        <div>
            <a href="{{ route('job.edit',['id'=>$model->id]) }}">編集する</a>
        </div>
        <div>
            <a href="{{ route('job.copy',['id'=>$model->id]) }}">コピーして新しい仕事を追加</a>
        </div>
    @endif

@endsection

