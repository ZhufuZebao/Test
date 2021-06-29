@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/') }}/css/bootstrap.min.css" type="text/css">
@endsection

@section('content')

        {!! Form::open() !!}

        <p>新着メッセージ</p>

        <div class="row">

        <div class="col-md-3">
            <?php ?>
            <div class="list-group">
                <a href="{{ route('job-message.inbox') }}" class="list-group-item list-group-item-action active">
                    受信箱
                </a>
                <a href="{{ route('job-message.inbox') }}" class="list-group-item list-group-item-action">
                    すべて
                </a>
                <a href="{{ route('job-message.unread') }}" class="list-group-item list-group-item-action">
                    未読
                    @if(0 < $unread)
                        <span class="badge badge-pill badge-primary">{{ $unread }}</span>
                    @else
                        <span class="badge badge-pill badge-secondary"> 0 </span>
                    @endif
                </a>
                <a href="{{ route('job-message.outbox') }}" class="list-group-item list-group-item-action active">
                    送信箱
                </a>
                <a href="{{ route('job-message.outbox') }}" class="list-group-item list-group-item-action">
                    すべて
                </a>
            </div>
        </div>

        <div class="col-md-9">
            @if(0 == $query->count())
                <p class="alert alert-light">
                    まだメッセージがありません
                </p>
            @else
                <div class="alert alert-dark">
                    {!! Form::text('keyword', null, ['size'=>32,'max'=>72]) !!}
                    <button type="submit" class="btn-primary">検索</button>
                </div>
            @foreach($models as $model)
	    @include('/job-message/item', ['model' => $model])
	    @endforeach
        @endif
        
        <?php $models->appends(['pagesize'=>$models->perPage()]) ?>
        {{ $models->links() }}

        {{ Form::select('pagesize', [10 => 10, 20 => 20, 50 => 50, 100 => 100], $models->perPage(), ['onchange'=>'this.form.submit()']) }}件ずつ表示

        {!! Form::close() !!}

        </div>
        </div>

@endsection

