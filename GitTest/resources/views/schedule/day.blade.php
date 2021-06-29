@extends('layouts.app')

@section('header')
<title>スケジュール</title>
<script>
function newSchedule(y, m, d) {
    window.location.href = '{{ url('/newschedule/ref/0/y/') }} + y + '/m/' + m + '/d/' + d + '/back/scheduleday';
}
</script>
@endsection

@section('content')

<!--container-->
<div class="container low schedule">
    <h1>スケジュール</h1>

    <ul class="schedule-nav clearfix">
        <li><a href="{{ url('/schedule/ref/0') }}"></a></li>
        <li><a href="{{ url('/scheduleweek/ref/0') }}"></a></li>
        <li class="current"><a href="{{ url('/scheduleday/y/'. date('Y'). '/m/'. (int)date('m'). '/d/'. (int)date('d')) }}/"></a></li>
        <li><a href="{{ url('/schedulesetting') }}"></a></li>
    </ul>

    <table>
    <caption><span class="left_area" onClick="window.location.href='{{ url('/scheduleday/y/'. $prev_y. '/m/'. $prev_m. '/d/'. $prev_d) }}'"></span>{{ $display_y }}年{{ $display_m }}月{{ $display_d }}日<span class="right_area" onClick="window.location.href='{{ url('/scheduleday/y/'. $next_y. '/m/'. $next_m. '/d/'. $next_d) }}'"></span></caption>
    <tbody>
    <tr class="ws">
        <th style="width:2%">&nbsp;</th>
        <td class="ws">
            <a href="javascript:newSchedule({{ $display_y }}, {{ $display_m }}, {{ $display_d }})"><img src="{{ asset('/images/ico_plus6_3.gif') }}" alt="スケジュール追加" title="スケジュール追加" /></a><br />
        </td>
    </tr>
    @for ($t = $st_hour; $t <= $ed_hour; $t++)
    <tr>
        <th style="width:2%">{{ $t }}</th>
        <?php
        $str  = '';
        $str2 = '';
        $style="ws1";
        $rowspan = 1;
        ?>
        @foreach ($schedule as $value)
        <?php
        if ($value['st_time'] <= sprintf('%02d:59', $t) && $value['ed_time'] > sprintf('%02d:00', $t)) {
            $style="ws2";
            if ((int)substr($value['st_time'], 0, 2) == $t) {

                if ($str != '') $str .= '<br /><br />';

                $style="ws2 ws2_2";
                $str .= '<span><a href="'. url('/editschedule/id/')
                    . (($value['relation_id'] != '') ? $value['relation_id'] : $value['id'])
                    . '/sub_id/'. (($value['relation_id'] != '') ? $value['id'] : '0')
                    . '/ref/0/back/scheduleday"><span class="subject">'. $value['subject']. '</span>';
                if ($value['st_time'] != '') {
                    $str .= '<br /><span class="time_area">'
                        . (int)substr($value['st_time'], 0, 2). ':'. substr($value['st_time'], -2)
                        . '-'. $value['ed_time']. '</span>';
                }
                $str .= '</a></span><br />'. $value['comment'];

                $rowspan = (int)substr($value['ed_time'], 0, 2) - (int)substr($value['st_time'], 0, 2);
//                $t += $rowspan;
            }
        }
        ?>
        @endforeach
        <td class="{{ $style }}" rowspan="{{ $rowspan }}">
            {!! $str !!}<br />
        </td>
        @if ($rowspan > 1)
            @for ($i = $t + 1; $i < $t + $rowspan; $i++)
    </tr>
    <tr>
        <th style="width:2%">{{ $i }}</th>
            @endfor
            <?php $t += $rowspan - 1; ?>
        @endif
    </tr>
    @endfor
    </tbody>

    </table>
</div>
<!--/container-->

@endsection
