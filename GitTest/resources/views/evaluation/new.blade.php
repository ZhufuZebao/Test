@extends('layouts.app')

@section('header')
<title>評価</title>
@endsection

@section('content')

<!--container-->
<div class="container low evaluation client post">
    <h1>評価</h1>

    <ul>
        <li class="clearfix">
            <div class="title"><a href="{{ url('/project/id/' .$historys->project_id) }}">{{ $historys->project_name }}</a></div>
            <div class="photo"><a href="{{ url('/contractor/id/'. $historys->contractor_id) }}"><img src="/photo/contractors/{{ sprintf('%010d', $historys->contractor_id) }}.jpg" alt="{{ $historys->contractor_name }}"></a></div>
            <div class="name">
                <div class="cliant"><a href="{{ url('/contractor/id/'. $historys->contractor_id) }}">{{ $historys->contractor_name }}</a></div>
                <div class="day">{{ $historys->st_disp_m }}/{{ $historys->st_disp_d }}({{ $historys->st_disp_w }}) ～ @if ($historys->st_disp_m != $historys->ed_disp_m) {{ $historys->ed_disp_m }}/{{ $historys->ed_disp_d }}({{ $historys->ed_disp_w }}) @else {{ $historys->ed_disp_d }}({{ $historys->ed_disp_w }})@endif</div>
            </div>
        </li>
    </ul>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/registpost') }}">
    {{ csrf_field() }}
    <input type="hidden" name="project_id" id="project_id" value="{{ $historys->project_id }}">

    <dl>
        <dt>満足度</dt>
        <dd>
            満足度を、1～5星で評価してください。

            <select name="satisfaction" id="satisfaction">
                <option value=""></option>
                <option value="1">★</option>
                <option value="2">★★</option>
                <option value="3">★★★</option>
                <option value="4">★★★★</option>
                <option value="5">★★★★★</option>
            </select>

            @if ($errors->has('satisfaction'))
                <span class="help-block">
                    <strong>{{ $errors->first('satisfaction') }}</strong>
                </span>
            @endif
        </dd>

        <dt>本文</dt>
        <dd>
            <textarea name="comment">{{ old('comment') }}</textarea>

            @if ($errors->has('comment'))
                <span class="help-block">
                    <strong>{{ $errors->first('comment') }}</strong>
                </span>
            @endif
        </dd>
    </dl>

    <div class="button green pencil"><a href="javascript:document.input_form.submit();">投稿する</a></div>
    <div class="back"><a href="{{ url('/listevaluation') }}">前の画面へ戻る</a></div>

    </form>

</div>
<!--/container-->

@endsection
