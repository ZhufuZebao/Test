@extends('layouts.app')

@section('header')
<title>スケジュール</title>
<script>
function newSchedule(y, m, d) {
    window.location.href = "{{ url('/newschedule/ref/'. $ref. '/y') }}/" + y + '/m/' + m + '/d/' + d + '/back/scheduleweek';
}
</script>
@endsection

@section('content')

<!--container-->
<div class="container low schedule">
    <h1>スケジュール</h1>

    <ul class="schedule-nav clearfix">
        <li><a href="{{ url('/schedule/ref/0') }}"></a></li>
        <li class="current"><a href="{{ url('/scheduleweek/ref/0') }}"></a></li>
        <li><a href="{{ url('/scheduleday/y/'. date('Y'). '/m/'. (int)date('m'). '/d/'. (int)date('d')) }}/"></a></li>
        <li><a href="{{ url('/schedulesetting') }}"></a></li>
    </ul>

    <table>
    <caption><span class="left_area" onClick="window.location.href='{{ url('/scheduleweek/ref/'. ($ref - 1)) }}'"></span>{{ $display_y }}年{{ $display_m }}月<span class="right_area" onClick="window.location.href='{{ url('/scheduleweek/ref/'. ($ref + 1)) }}'"></span></caption>
    <thead>
    <tr>
        <th style="width:2%"> </th>
    <?php $w = ['日', '月', '火', '水', '木', '金', '土']; ?>
    @for ($i = 0; $i < 7; $i++)
        <?php $style = ($week[$i] == sprintf('%04d/%02d/%02d', $today_y, $today_m, $today_d)) ? 'ws_today' : 'ws'; ?>
        <th class="{{ $style }}">
            {{ (int)substr($week[$i], -2) }}<br />
            ({{ $w[$i] }})
        </th>
    @endfor
    </tr>
    </thead>
    <tbody>
    <tr class="ws">
        <th style="width:2%">&nbsp;</th>
    @for ($i = 0; $i < 7; $i++)
        <td class="ws">
            <a href="javascript:newSchedule({{ substr($week[$i], 0, 4) }}, {{ (int)substr($week[$i], 5, 2) }}, {{ (int)substr($week[$i], -2) }})"><img src="{{ asset('/images/ico_plus6_3.gif') }}" alt="スケジュール追加" title="スケジュール追加" /></a><br />
<?php /*
        @if (isset($schedule[(int)substr($week[$i], -2)]))
            @foreach ($schedule[(int)substr($week[$i], -2)] as $value)
                <span><a href="/editschedule/id/{{ ($value['relation_id'] != '') ? $value['relation_id'] : $value['id'] }}/sub_id/{{ ($value['relation_id'] != '') ? $value['id'] : '0' }}/ref/{{ $ref }}">{{ $value['subject'] }}@if($value['st_time'] != '')<br /><span class="time_area">{{ (int)substr($value['st_time'], 0, 2). ':'. substr($value['st_time'], -2). '-'. $value['ed_time'] }}</span>@endif</a></span><br />
            @endforeach
        @endif
*/ ?>
        </td>
    @endfor
    </tr>
    @for ($t = $st_hour; $t <= $ed_hour; $t++)
    <tr>
        <th style="width:2%">{{ $t }}</th>
        @for ($i = 0; $i < 7; $i++)
            <?php
            $style="ws1";
            $str  = '';
            $str2 = '';
            $rowspan = '1';
            $skip = false;
            ?>
            @if (isset($schedule[(int)substr($week[$i], -2)]))

                @foreach ($schedule[(int)substr($week[$i], -2)] as $value)
                <?php
                if ($value['st_time'] <= sprintf('%02d:59', $t) && $value['ed_time'] > sprintf('%02d:00', $t)) {
//                    $style="ws2";
                    if ((int)substr($value['st_time'], 0, 2) == $t) {
                        $style="ws2 ws2_2";
                        $str = '<span><a href="'. url('/editschedule/id/')
                            . (($value['relation_id'] != '') ? $value['relation_id'] : $value['id'])
                            . '/sub_id/'. (($value['relation_id'] != '') ? $value['id'] : '0')
                            . '/ref/'. $ref. '/back/scheduleweek">'. $value['subject'];
                        if ($value['st_time'] != '') {
                            $str .= '<br /><span class="time_area">'
                                . (int)substr($value['st_time'], 0, 2). ':'. substr($value['st_time'], -2)
                                . '-'. $value['ed_time']. '</span>';
                        }
                        $str .= '</a></span><br />';

                        $rowspan = (int)substr($value['ed_time'], 0, 2) - (int)substr($value['st_time'], 0, 2);
                        if ($rowspan < 1) $rowspan = 1;

                    } else {
                        $skip = true;
                    }
                }
                ?>
                @endforeach
            @endif
            @if ($skip == false)
        <td class="{{ $style }}" rowspan="{{ $rowspan }}">
            {!! $str !!}<br />
        </td>
            @endif
        @endfor
    </tr>
    @endfor
    </tbody>

    </table>
</div>
<!--/container-->

@endsection
