@extends('layouts.app')

@section('header')
<title>スケジュール</title>
@endsection

@section('content')
<!--container-->
<div class="container low schedule">
    <h1>スケジュール</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/registschedule') }}">
    {{ csrf_field() }}
    <input type="hidden" name="id" id="id" value="{{ $id }}" />
    <input type="hidden" name="relation_id" id="relation_id" value="{{ isset($relation_id) ? $relation_id : '' }}" />
    <input type="hidden" name="ref" id="ref" value="{{ $ref }}" />
    <input type="hidden" name="back" id="ref" value="{{ $back }}" />

    <dl>
        <dt>日付</dt>
        <dd>{{ $date }}</dd>

        <dt>開始時間</dt>
        <dd>{{ $st_time_h }}:{{ sprintf('%02d', $st_time_m) }}</dd>

        <dt>終了時間</dt>
        <dd>{{ $ed_time_h }}:{{ sprintf('%02d', $ed_time_m) }}</dd>

        @if (isset($repeat))
        <dt>繰り返し</dt>
        <dd>
            @if ($repeat == 1) 毎日
            @elseif($repeat == 2) 毎週
            @elseif($repeat == 3) 毎月
            @elseif ($repeat == 4) 曜日指定 (
                @if (isset($week1) && $week1 == 1) 日 @endif
                @if (isset($week2) && $week2 == 1) 月 @endif
                @if (isset($week3) && $week3 == 1) 火 @endif
                @if (isset($week4) && $week4 == 1) 水 @endif
                @if (isset($week5) && $week5 == 1) 木 @endif
                @if (isset($week6) && $week6 == 1) 金 @endif
                @if (isset($week7) && $week7 == 1) 土 @endif
            )
            @endif

            （{{ sprintf('%04d/%02d/%02d', $st_y, $st_m, $st_d) }} ～ {{ sprintf('%04d/%02d/%02d', $ed_y, $ed_m, $ed_d) }}）
        </dd>
        @endif

        <dt>見出し</dt>
        <dd>{{ $subject }}</dd>

        <dt>内容</dt>
        <dd>{{ $comment }}</dd>
    </dl>

    </form>

    <div class="button green"><a href="javascript:document.input_form.submit();">登録</a></div>
    <div class="button gray pencil"><a href="{{ url('/newschedule/ref/'. $ref. '/y/'. $target_y. '/m/'. $target_m. '/d/' . $target_d. '/back/'. $back) }}">変更</a></div>

</div>
@endsection
