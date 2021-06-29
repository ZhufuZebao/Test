@extends('layouts.app')

@section('header')
<title>スケジュール</title>
<script>
<?php /*
function showSubMenu(d) {
    var show = false;
    if (document.getElementById('subMenu' + d).style.display == 'none') {
        show = true;
    }
    for (i = 1; i <= 31; i++) {
        if (document.getElementById('subMenu' + i)) {
            document.getElementById('subMenu' + i).style.display = 'none';
        }
    }
    if (show == true) {
        document.getElementById('subMenu' + d).style.display = 'inline';
    }
}
*/ ?>
function newSchedule(d) {
    window.location.href = '{{ url('/newschedule/ref/'. $ref. '/y/'. $display_y. '/m/'. $display_m. '/d') }}/' + d + '/back/schedule';
}
</script>
@endsection

@section('content')

<!--container-->
<div class="container low schedule">
    <h1>スケジュール</h1>

    <ul class="schedule-nav clearfix">
        <li class="current"><a href="{{ url('/schedule/ref/0') }}"></a></li>
        <li><a href="{{ url('/scheduleweek/ref/0') }}"></a></li>
        <li><a href="{{ url('/scheduleday/y/'. date('Y'). '/m/'. (int)date('m'). '/d/'. (int)date('d')) }}/"></a></li>
        <li><a href="{{ url('/schedulesetting') }}"></a></li>
    </ul>

    <table>
    <caption><span class="left_area" onClick="window.location.href='{{ url('/schedule/ref/'. ($ref - 1)) }}'"></span>{{ $display_y }}年{{ $display_m }}月<span class="right_area" onClick="window.location.href='{{ url('/schedule/ref/'. ($ref + 1)) }}'"></span></caption>
    <thead>
    <tr>
        <th>日</th>
        <th>月</th>
        <th>火</th>
        <th>水</th>
        <th>木</th>
        <th>金</th>
        <th>土</th>
    </tr>
    </thead>
    <tbody>
        <tr>
@if (is_array($calendar))
    @foreach ($calendar as $key => $item)

        @if ($key > 0 && $key % 7 == 0)
            </tr>
            <tr>
        @endif
        @if ($item != '')
            <?php $style= ($display_y == $today_y && $display_m == $today_m && $item== $today_d) ? ' class=today' : ''; ?>
            <td{{ $style }}>

                {{ $item }}<br />
                <a href="javascript:newSchedule({{ $item }})"><img src="{{ asset('/images/ico_plus6_3.gif') }}" alt="スケジュール追加" title="スケジュール追加" /></a><br />

<?php /*
                <a href="#open09" class="link"><img src="/images/ico_plus6_3_2.gif" alt="スケジュール簡易登録" title="スケジュール簡易登録" width="15" /></a><br />
                <a href="javascript:easyClick({{ $item }})" class="link"><img src="/images/ico_plus6_3_2.gif" alt="スケジュール簡易登録" title="スケジュール簡易登録" width="15" /></a><br />
                <a href="javascript:void(0);" class="link" id="easy_{{ $item }}"><img src="/images/ico_plus6_3_2.gif" alt="スケジュール簡易登録" title="スケジュール簡易登録" width="15" /></a><br />
*/ ?>

                @if (isset($schedule[$item]) && is_array($schedule[$item]))
                    @foreach ($schedule[$item] as $key => $value)
<?php /*
                    <span><a href="/editschedule/id/{{ ($value['relation_id'] != '') ? $value['relation_id'] : $value['id'] }}/sub_id/{{ ($value['relation_id'] != '') ? $value['id'] : '0' }}/ref/{{ $ref }}">{{ $value['subject'] }}</a></span><br />
*/ ?>
                    <span class="text"><a href="{{ url('/editschedule/id/'. (($value['relation_id'] != '') ? $value['relation_id'] : $value['id'])) }}/sub_id/{{ ($value['relation_id'] != '') ? $value['id'] : '0' }}/ref/{{ $ref }}/back/schedule">{{ $value['type_name'] }}@if($value['st_time'] != '')<br /><span class="time_area">{{ (int)substr($value['st_time'], 0, 2). ':'. substr($value['st_time'], -2). '-'. $value['ed_time'] }}</span>@endif</a></span><br />
                    @endforeach
                @endif
            </td>
        @else
            <td>&nbsp;</td>
        @endif
    @endforeach
@endif
    </tr>
    </tbody>
    </table>

</div>

<?php /*
    <div class="balloon1-top" id="balloon1-top" style="display:none">
*/ ?>
<div id="modal">
    <div id="open09">

        <a href="#send-message-area" class="close_overlay">close</a>

        <div class="modal_window">

            <div class="close"><a href="#send-message-area">×</a></div>

            <div class="modal-content clearfix">

                <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/newschedule') }}">
                {{ csrf_field() }}
                <input type="hidden" name="id" id="id" value="" />
                <input type="hidden" name="ref" id="ref" value="{{ $ref }}" />

                <dl>
                    <dt>{{ $display_y }}年{{ $display_m }}月<span id="disp_day"></span>日</dt>
                    <dd>
                        時刻
                        <select name="st_time_h" id="st_time_h">
                            <option value=""></option>
                        @for ($i = 0; $i <= 23; $i++)
                            <option value="{{ $i }}"@if ($i == old('st_time_h')) selected @endif>{{ $i }}</option>
                        @endfor
                        </select>
                        :
                        <select name="st_time_m" id="st_time_m">
                            <option value=""></option>
                        @for ($i = 0; $i <= 59; $i++)
                            <option value="{{ $i }}"@if ($i == old('st_time_m')) selected @endif>{{ sprintf('%02d', $i) }}</option>
                        @endfor
                        </select>
                        ～
                        <select name="ed_time_h" id="ed_time_h">
                            <option value=""></option>
                        @for ($i = 0; $i <= 23; $i++)
                            <option value="{{ $i }}"@if ($i == old('ed_time_h')) selected @endif>{{ $i }}</option>
                        @endfor
                        </select>
                        :
                        <select name="ed_time_m" id="ed_time_m">
                            <option value=""></option>
                        @for ($i = 0; $i <= 59; $i++)
                            <option value="{{ $i }}"@if ($i == old('ed_time_m')) selected @endif>{{ sprintf('%02d', $i) }}</option>
                        @endfor
                        </select>
                    </dd>
                    <dd>
                        予定
                        <select name="type" class="type">
                            @foreach ($types as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        　<input type="text" name="subject" id="subject" value="{{ old('subject') }}" />

                        @if ($errors->has('subject'))
                            <span class="help-block">
                                <strong>{{ $errors->first('subject') }}</strong>
                            </span>
                        @endif
                    </dd>
                    <dd>
                        内容
                        <textarea name="comment" id="comment" value="{{ old('comment') }}" row="10"></textarea>

                        @if ($errors->has('comment'))
                            <span class="help-block">
                                <strong>{{ $errors->first('comment') }}</strong>
                            </span>
                        @endif
                    </dd>
                </dl>

                </form>

                <div class="button green"><a href="#">登録</a></div>

            </div>
        </div><!--/.modal_window-->
    </div><!--/#open09-->

</div><!--/#modal-->

<!--/container-->

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
function easyClick(day) {

    if (document.getElementById('balloon1-top').style.display == 'block') {
        document.getElementById('balloon1-top').style.display = 'none';
    } else {
        document.getElementById('disp_day').innerHTML = day;
        document.getElementById('balloon1-top').style.display = 'block';
//        $('.balloon1-top').css('top', '-500px');
    }
}
/*
$(function(){
    $(".link").on('click',function(event){

        if (document.getElementById('balloon1-top').style.display == 'block') {
            document.getElementById('balloon1-top').style.display = 'none';

        } else {

            var eventID = event.target.id;
            console.log(eventID);

            var tmp = eventID.split('_');
            console.log(tmp);

            var day = tmp[1];

            var x = event.pageX ;
            var y = event.pageY ;

            x = x - 180;
            if (x < 0) {
                x = 0;
//                $('.balloon1-top:before').css('left', '10%');
//                $(this).toggleClass('balloon1-top2');
//                $('balloon1-top').append('left: 10%;');
             // head内末尾に追加
                $('head').append('<style>.balloon1-top:before { left: 10%; }</style>');

            } else {
//                $('.balloon1-top:before').css('left', '50%');
                $('head').append('<style>.balloon1-top:before { left: 50%; }</style>');
            }

//alert(x + ':' + y);

            $('.balloon1-top').css('top', y);
            $('.balloon1-top').css('left', x);


            document.getElementById('disp_day').innerHTML = day;
            document.getElementById('balloon1-top').style.display = 'block';

        }
    });
});
*/
</script>
@endsection
