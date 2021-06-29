@extends('layouts.app')

@section('header')
<title>評価</title>
@endsection

@section('content')

<!--container-->
<div class="container low evaluation client">
    <h1>評価</h1>

    <div class="number">受注履歴（{{ count($historys) }}件）@if ($unrated > 0) <span class="highlight small-text">未評価が{{ $unrated }}件あります</span>@endif</div>

    <ul>
    @foreach ($historys as $items)
        <li class="clearfix">
            <div class="title"><a href="{{ url('/project/id/'. $items->project_id) }}">{{ $items->project_name }}</a></div>
            <div class="name clearfix">
                <a href="{{ url('/contractor/id/'. $items->contractor_id) }}"><img src="/photo/contractors/{{ sprintf('%010d', $items->contractor_id) }}.jpg" alt="{{ $items->contractor_name }}"></a>
                <a href="{{ url('/contractor/id/'. $items->contractor_id) }}">{{ $items->contractor_name }}</a>
                <div class="day">{{ $items->st_disp_m }}/{{ $items->st_disp_d }}({{ $items->st_disp_w }}) ～ @if ($items->st_disp_m != $items->ed_disp_m) {{ $items->ed_disp_m }}/{{ $items->ed_disp_d }}({{ $items->ed_disp_w }}) @else {{ $items->ed_disp_d }}({{ $items->ed_disp_w }})@endif</div>
            </div>
            @if (isset($evaluations[$items->project_id]))
            <div class="post button white"><a href="{{ url('/editpost/id/'. $items->project_id) }}">投稿済の評価を<br />確認・変更する</a></div>
            @else
            <div class="post button green pencil"><a href="{{ url('/newpost/id/'. $items->project_id) }}">評価を投稿する</a></div>
            @endif
        </li>
    @endforeach
    </ul>
</div>
<!--/container-->

@endsection
