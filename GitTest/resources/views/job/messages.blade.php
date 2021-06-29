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
        <a href="{{ route('job.index') }}">仕事</a> &gt;
        <a href="{{ route('job.show',['id'=>$model->id]) }}">{{ $model->name }}</a> &gt;
        メッセージ （{{ ($w = $offer->worker) ? $w->name : null }} さん）
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
                {{ $offer->vacancy->contractor->name }}
            </p>
        </div>
        <div class="col-md-6">
            発注者
            <p class="alert alert-dark">
                {{ $offer->vacancy->user->name }}
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
        <div class="row">
        <p class="col-md-4 alert alert-dark">
            求人マスターの状態
            @if($model->status->isDraft())
                <span class="badge badge-secondary">一時保存</span>
            @elseif($model->status->isOpen())
                <span class="badge badge-info">公開中</span>
            @elseif($model->status->isClosed())
                <span class="badge badge-danger">停止中</span>
            @else
                <span class="badge badge-warning">エラー</span>
            @endif
        </p>

        <p class="col-md-4 alert alert-dark">
            応募の状態 //TODO
            <span class="badge badge-secondary">保留中</span>
        </p>
        </div>
    </div>

    <div class="col-md-12 alert-dark">
        メッセージ
        @foreach($mails as $mail)
	    @include('/job/_message',['model'=>$mail])
        @endforeach
        <div class="alert-dark">
            {{ $mails->links() }}
        </div>

    </div>

    {{-- もし作成者が自分なら「編集」を表示 --}}
    @if(Auth::id() == $model->user_id)
        <div>
            <a href="{{ route('job.edit',['id'=>$model->id]) }}">編集する</a>
        </div>
	{{-- 返信する --}}
        <div>
            {!! Form::open(['route'=>['job-message.reply','id'=>$offer->id]]) !!}
            {!! Form::textarea('content',null,['maxlength'=>2048]) !!}
            <button type="submit" class="btn btn-info">
                返信する
            </button>

            @if(null === $offer->hired)
                <button type="submit" name="hired" value="1" class="btn btn-outline-success">内定する</button>
                <button type="submit" name="hired" value="0" class="btn btn-outline-secondary">辞退する</button>
            @elseif($offer->hired)
                <div class="alert alert-success">
                    あなたはこの職人の採用を内定しました
                </div>
            @else
                <div class="alert alert-warning">
                    あなたはこの職人の採用を辞退しました
                </div>
            @endif

            @if(0 === $offer->accepted)
                <div class="alert alert-warning">
                    この職人は内定を辞退しました
                </div>
            @else
                <div class="alert alert-success">
                    この職人はは内定を承諾しました
                </div>
            @endif

            {!! Form::close() !!}
        </div>

    {{-- 応募済みなら単純にメッセージ送信 --}}        
    @elseif($model->offers()->where('worker_id','=',Auth::id())->exists())
        <div>
            {!! Form::open(['route'=>['job-message.store','id'=>$model->id]]) !!}
            {!! Form::textarea('content',null,['maxlength'=>2048]) !!}

            <button type="submit" class="btn btn-primary">
                送信する
            </button>
            @if($offer->hired)
                @if(null === $offer->accepted)
                    <button type="submit" name="accepted" value="1" class="btn btn-outline-warning">承諾する</button>
                    <button type="submit" name="accepted" value="0" class="btn btn-outline-secondary">辞退する</button>
                @elseif($offer->accepted)
                    <div class="alert alert-success">
                        あなたは内定を承諾しました
                    </div>
                @else
                    <div class="alert alert-warning">
                        あなたは内定を辞退しました
                    </div>
                @endif

            @endif

            {!! Form::close() !!}
        </div>

    {{-- もしユーザが職人、かつ状態が「公開中」なら「応募」を表示 --}}
    @else
        <div>
            {!! Form::open(['route'=>['job.propose','id'=>$model->id]]) !!}
            {!! Form::textarea('content',null,['maxlength'=>2048]) !!}
            <button type="submit">
                応募する
            </button>
            {!! Form::close() !!}
        </div>

    @endif


@endsection

