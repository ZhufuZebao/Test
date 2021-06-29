@extends('layouts.app')

@section('header')
<title>スケジュール</title>

@endsection

@section('content')

<!--container-->
<div class="container low schedule">
    <h1>スケジュール</h1>

    <ul class="schedule-nav clearfix">
        <li><a href="{{ url('/schedule/ref/0') }}"></a></li>
        <li><a href="{{ url('/scheduleweek/ref/0') }}"></a></li>
        <li><a href="{{ url('/scheduleday/y/'. date('Y'). '/m/'. (int)date('m'). '/d/'. (int)date('d')) }}/"></a></li>
        <li class="current"><a href="{{ url('/schedulesetting') }}"></a></li>
    </ul>

    <p>設定</p>

    <form class="form-horizontal" name="setting_form" role="form" method="POST" action="{{ url('/schedulesetting') }}">
        {{ csrf_field() }}

    <dl>
        <dt>表示する時間帯</dt>
        <dd>
            <select name="st_hour" class="show_time">
            @for ($i = 0; $i <= 23; $i++)
                <option value="{{ $i }}"{{ ($st_hour == $i) ? ' selected' : '' }}>{{ $i }}時</option>
            @endfor
            </select>
            ～
            <select name="ed_hour" class="show_time">
            @for ($i = 0; $i <= 23; $i++)
                <option value="{{ $i }}"{{ ($ed_hour == $i) ? ' selected' : '' }}>{{ $i }}時</option>
            @endfor
            </select>
        </dd>
    </dl>

    <br clear="all" />
    <div class="button green" id="save" style="display:block;"><a href="javascript:document.setting_form.submit();">設定</a></div>
    <br />

    </form>

</div>
<!--/container-->

@endsection
