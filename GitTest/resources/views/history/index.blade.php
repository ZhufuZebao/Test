@extends('layouts.app')

@section('header')
<title>受注履歴</title>
@endsection

@section('content')

<!--container-->
<div class="container low history">
    <h1>受注履歴</h1>

    <ul>
    @foreach ($historys as $key => $items)
        <li class="clearfix">
            <div class="title"><a href="{{ url('/project/id/'. $items->project_id) }}">{{ $items->project_name }}</a></div>
            <div class="photo"><a href="{{ url('/contractor/id/'. $items->contractor_id) }}"><img src="{{ asset('/photo/contractors/'. sprintf('%010d', $items->contractor_id)) }}.jpg" alt="{{ $items->contractor_name }}"></a></div>
            <div class="content clearfix">
                <a href="{{ url('/contractor/id/'. $items->contractor_id) }}">{{ $items->contractor_name }}</a>
                <div class="day">{{ $items->st_disp_m }}/{{ $items->st_disp_d }}({{ $items->st_disp_w }}) ～ @if ($items->st_disp_m != $items->ed_disp_m) {{ $items->ed_disp_m }}/{{ $items->ed_disp_d }}({{ $items->ed_disp_w }}) @else {{ $items->ed_disp_d }}({{ $items->ed_disp_w }})@endif</div>
            </div>
            <div class="post button green"><a href="{{ url('/project/id/'. $items->project_id) }}">詳細を確認する</a></div>
        </li>
    @endforeach
    </ul>
</div>
<!--/container-->

@endsection
