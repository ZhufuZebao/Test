@extends('layouts.app')

@section('header')
<title>登録画面</title>
<link rel="stylesheet" href="{{ url('/') }}/css/bootstrap.min.css" />
@endsection

@section('content')

<!--container-->
<div class="container low user">

    <h1>請負企業</h1>

    <div class="container">
        <div class="alert alert-warning">
        {!! Form::open(['files'=>true]) !!}
        <div class="col-md-12">
            名前：{!! Form::text('name',$model->name) !!}
            @if ($errors->has('name'))
                <div class="error">{{ $errors->first('name') }}</div>
            @endif
        </div>

        <div class="col-md-4">
            〒{!! Form::text('zip',$model->zip) !!}
            都道府県 {!! Form::select('pref', $prefs, $model->pref) !!}
            住所 {!! Form::text('addr', $model->addr) !!}
            @if ($errors->has('zip'))
                <div class="error">{{ $errors->first('name') }}</div>
            @endif
            @if ($errors->has('pref'))
                <div class="error">{{ $errors->first('pref') }}</div>
            @endif
            @if ($errors->has('addr'))
                <div class="error">{{ $errors->first('addr') }}</div>
            @endif
        </div>

        <div class="col-md-4">
            代表：{!! Form::text('representative', $model->representative) !!}
            @if ($errors->has('representative'))
                <div class="error">{{ $errors->first('representative') }}</div>
            @endif
        </div>


        <div class="col-md-4">
            設立：{!! Form::text('establishment', $model->establishment) !!}
            @if ($errors->has('establishment'))
                <div class="error">{{ $errors->first('establishment') }}</div>
            @endif
        </div>

        <div class="col-md-4">
            説明：{!! Form::text('contents', $model->contents) !!}
            @if ($errors->has('contents'))
                <div class="error">{{ $errors->first('contents') }}</div>
            @endif
        </div>

        </div>

        <button type="submit" class="btn btn-success">保存</button>

        {!! Form::close() !!}
    </div>

</div>
<!--/container-->

@endsection
