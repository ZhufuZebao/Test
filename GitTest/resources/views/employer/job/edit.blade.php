@extends('layouts.app')

@section('header')
    <title>{{ config('app.name') }}</title>

    {{-- bootstrap Modal のために、この組み合わせの *.js が必要 --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="{{ asset('/') }}/css/jquery-ui.min.css" type="text/css">
    <script>
     $('#st_date').datepicker({
         showOn: 'button',
         buttonImageOnly: true,
         buttonImage: '/images/icon-schedule.png'
     });
     $('#ed_date').datepicker({
         showOn: 'button',
         buttonImageOnly: true,
         buttonImage: '/images/icon-schedule.png'
     });
    </script>
@endsection

@section('content')

    @if($msg = Session::get('msg-success'))
    <p class="alert alert-success">
        {{ $msg }}
    </p>
    {{ Session::forget('msg-success') }}
    @endif

    <?php $route = $model->id ? ['job.edit', 'id'=>$model->id] : 'job.create'; ?>
    {!! Form::open(['method'=>'post','files'=>true,'route'=> $route]) !!}

	<section class="dashubord-schedule-wrapper">

        <p>
            {{ $model->id ? "仕事を編集する" : "新しい仕事を追加する" }}
        </p>

        <p class="text-right">
            <!-- Button trigger modal -->
            または
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#jobSearchModal">
                以前の仕事内容をコピー
            </button>
        </p>

        <div class="alert alert-dark">
            見出し
            <span class="badge badge-warning">必須</span>
            {!! Form::text('name', $model->name,['size'=>72]) !!}
            @if($errors->has('name'))
                <span class="text-danger">
                    {{ $errors->first('name') }}
                </span>
            @endif
        </div>

        <div class="alert alert-dark">
            請負会社
            <span class="badge badge-warning">必須</span>
            {!! Form::select('contractor_id', $contractors, $model->contractor_id) !!}
            @if($errors->has('contractor_id'))
                <span class="text-danger">
                    {{ $errors->first('contractor_id') }}
                </span>
            @endif
        </div>

        <div class="alert alert-dark">
            職種
            <span class="badge badge-warning">必須</span>
            {!! Form::select('skill_id', $skills, $model->skill_id) !!}
            @if($errors->has('skill_id'))
                <span class="text-danger">
                    {{ $errors->first('skill_id') }}
                </span>
            @endif
        </div>

        紹介文
        <span class="badge badge-warning">必須</span>

        <div class="alert alert-dark">
            {!! Form::textarea('description', $model->description) !!}
            @if($errors->has('description'))
                <span class="text-danger">
                    {{ $errors->first('description') }}
                </span>
            @endif
            (2048字)
        </div>

        募集期間
        <span class="badge badge-warning">必須</span>

        <div class="row">
            <div class="alert alert-dark">
            開始
            {!! Form::text('st_date', $model->st_date, ['id'=>'st_date']) !!}
            @if($errors->has('st_date'))
                <span class="text-danger">
                    {{ $errors->first('st_date') }}
                </span>
            @endif

            終了
            {!! Form::text('ed_date', $model->st_date, ['id'=>'ed_date']) !!}
            @if($errors->has('ed_date'))
                <span class="text-danger">
                    {{ $errors->first('ed_date') }}
                </span>
            @endif
        </div>
        </div>

        <div class="alert alert-dark">
            状態
            {{ ($s = $model->status) ? $s->name : null }}
            <button type="submit" name="status_id" class="btn-secondary" value="{{ \App\JobVacancyStatus::TEMPORAL }}">一時保存</button>
            <button type="submit" name="status_id" class="btn-info" value="{{ \App\JobVacancyStatus::PUBLISH }}">公開</button>

            @if($model->id)
                <p class="text-right">
                    <button type="submit" name="status_id" class="btn-danger" value="{{ \App\JobVacancyStatus::CLOSED }}">停止</button>
                </p>
            @endif
        </div>

	</section>

    {!! Form::close() !!}

    @foreach($errors->all() as $error)
        {{ $error }}
    @endforeach

    <!-- Modal -->
    <div class="modal fade" id="jobSearchModal" tabindex="-1" role="dialog" aria-labelledby="jobSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobSearchModalLabel">
                        コピーする仕事を選んでください
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach($jobs as $id => $name)
                        <div><a href="{{ route('job.copy',['id'=>$id]) }}">{{ $name }} </a></div>
                    @endforeach
                    <small><a href="{{ route('job.history') }}">別の仕事...</a></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                </div>
            </div>
        </div>
    </div>

@endsection

