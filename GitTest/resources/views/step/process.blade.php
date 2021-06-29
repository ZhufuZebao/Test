@extends('layouts.app')

@section('header')
<title>工程管理</title>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css">
@endsection

@section('content')

<!--container-->
<div class="container low step table schedule">
    <h1>工程管理</h1>

    <div class="title">{{ $projects->name }}</div>

    <form class="form-horizontal" name="cal_form" role="form" method="POST" action="{{ url('/processsheet') }}">
    {{ csrf_field() }}
    <input type="hidden" name="ref" id="ref" value="{{ $ref }}">
    <input type="hidden" name="project_id" id="project_id" value="@if (is_object($projects)) {{ $projects->id }} @endif">
@if (is_object($projects))
        <input type="hidden" name="project_name" id="project_name" value="{{ $projects->name }}">
        <input type="hidden" name="contractor" id="contractor" value="{{ $projects->contractor_id }}">
        <input type="hidden" name="st_date" id="st_date" value="{{ $projects->st_date }}">
        <input type="hidden" name="ed_date" id="ed_date" value="{{ $projects->ed_date }}">
        <input type="hidden" name="manager" id="manager" value="{{ $projects->manager }}">
        <input type="hidden" name="author" id="author" value="{{ $projects->author }}">
        <input type="hidden" name="create_date" id="create_date" value="{{ $projects->create_date }}">
@else
        <input type="hidden" name="project_name" id="project_name" value="{{ old('project_name') }}">
        <input type="hidden" name="contractor" id="contractor" value="{{ old('contractor') }}">
        <input type="hidden" name="st_date" id="st_date" value="{{ old('st_date') }}">
        <input type="hidden" name="ed_date" id="ed_date" value="{{ old('ed_date') }}">
        <input type="hidden" name="manager" id="manager" value="{{ old('manager') }}">
        <input type="hidden" name="author" id="author" value="{{ old('author') }}">
        <input type="hidden" name="create_date" id="create_date" value="{{ old('create_date') }}">
@endif
    </form>

    <table>
    <caption><span class="left_area" onClick="prevMonth()"></span>{{ $display_y }}年{{ $display_m }}月<span class="right_area" onClick="nextMonth()"></span></caption>
    <thead>
        <tr>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th>土</th>
            <th>日</th>
        </tr>
    </thead>
    <tbody>
        <tr>
<?php
$tmp = 0;
$continue = 0;
?>
@if (is_array($calendar))
    @foreach ($calendar as $key => $item)

        @if ($key > 0 && $key % 7 == 0)
            </tr>
            <tr class="chart">
            @if ($continue > 0)
            <?php
                $colspan = ($continue > 7) ? 7 : $continue;
                $continue = ($continue > 7) ? $continue - 7 : 0;
            ?>
            @else
            <?php $colspan = null; ?>
            @endif
            @for ($i = $tmp; $i < $tmp + 7; $i++)
                @if (is_array($processs))
                    @foreach($processs as $process)
                        @if ($process->st_day == $calendar[$i])
                        <?php
                        $colspan = (($process->diff+ $process->st_week) > 7) ? (7 - $process->st_week) : $process->diff;
                        $continue = $process->diff- $colspan;
                        $color = $process->color;
                        $part_name = $process->part_name;
                        $staff_name = $process->staff_name;

                        $process_id = $process->id;
                        $part_id = $process->part_id;
                        $staff_id = $process->staff_id;
                        $st_expected = $process->st_disp;
                        $ed_expected = $process->ed_disp;
                        ?>
                        @endif
                    @endforeach
                @endif

                @if (!$colspan)
                <td></td>
                @else
                <td colspan="{{ $colspan }}"><div class="{{ $color }}"><a href="#open03" onClick="edit('{{ $process_id }}', '{{ $part_id }}', '{{ $staff_id }}', '{{ $st_expected }}', '{{ $ed_expected }}');">{{ $part_name. '('. $staff_name. ')' }}</a></div></td>
                <?php
                $i += $colspan - 1;
                $colspan = null;
                ?>
                @endif
            @endfor
            </tr>
            <tr>
            <?php $tmp += 7; ?>
        @endif
        @if ($item != '')
            <?php $style= ($display_y == $today_y && $display_m == $today_m && $item== $today_d) ? ' class=today' : ''; ?>
            <td{{ $style }}>
                <a href="#open03" onClick="init()">{{ $item }}</a><br />

                @if (isset($schedule[$item]) && is_array($schedule[$item]))
                    @foreach ($schedule[$item] as $id => $subject)
                    <span><a href="{{ url('/editschedule/id/'. $id. '/ref/'. $ref) }}">{{ $subject }}</a></span><br />
                    @endforeach
                @endif
            </td>
        @else
            <td>&nbsp;</td>
        @endif
    @endforeach
@endif
    </tr>
    <tr class="chart">
    @if ($continue > 0)
    <?php
        $colspan = ($continue > 7) ? 7 : $continue;
        $continue = ($continue > 7) ? $continue - 7 : 0;
    ?>
    @else
    <?php $colspan = null; ?>
    @endif
    @for ($i = $tmp; $i < $tmp + 7; $i++)
        @if (is_array($processs))
            @foreach($processs as $process)
                @if ($process->st_day == $calendar[$i])
                <?php
                $colspan = (($process->diff+ $process->st_week) > 7) ? (7 - $process->st_week) : $process->diff;
                $continue = $process->diff- $colspan;
                $color = $process->color;
                $part_name = $process->part_name;
                $staff_name = $process->staff_name;
                ?>
                @endif
            @endforeach
        @endif

        @if (!$colspan)
        <td></td>
        @else
        <td colspan="{{ $colspan }}"><div class="{{ $color }}"><a href="#open03" onClick="init()">{{ $part_name. '('. $staff_name. ')' }}</a></div></td>
        <?php
        $i += $colspan - 1;
        $colspan = null;
        ?>
        @endif
    @endfor
    </tr>
    </tbody>
    </table>

@if (is_object($projects))
    <div class="button gray"><a href="{{ url('/deleteprocess/ref/'. $ref. '/id/'. $projects->id) }}">すべての工程を削除</a></div>
@endif
    <div class="back"><a href="{{ url('/step') }}">工程表トップ</a></div>

</div>
<!--/container-->

<div id="modal">
    <div id="open03">
        <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/registprocess') }}">
        {{ csrf_field() }}
        <input type="hidden" name="ref" id="ref" value="{{ $ref }}">
        <input type="hidden" name="project_id" id="project_id" value="@if (is_object($projects)) {{ $projects->id }} @endif">
@if (is_object($projects))
        <input type="hidden" name="project_name" id="project_name" value="{{ $projects->name }}">
        <input type="hidden" name="contractor" id="contractor" value="{{ $projects->contractor_id }}">
        <input type="hidden" name="st_date" id="st_date" value="{{ $projects->st_date }}">
        <input type="hidden" name="ed_date" id="ed_date" value="{{ $projects->ed_date }}">
        <input type="hidden" name="manager" id="manager" value="{{ $projects->manager }}">
        <input type="hidden" name="author" id="author" value="{{ $projects->author }}">
        <input type="hidden" name="create_date" id="create_date" value="{{ $projects->create_date }}">
@else
        <input type="hidden" name="project_name" id="project_name" value="{{ old('project_name') }}">
        <input type="hidden" name="contractor" id="contractor" value="{{ old('contractor') }}">
        <input type="hidden" name="st_date" id="st_date" value="{{ old('st_date') }}">
        <input type="hidden" name="ed_date" id="ed_date" value="{{ old('ed_date') }}">
        <input type="hidden" name="manager" id="manager" value="{{ old('manager') }}">
        <input type="hidden" name="author" id="author" value="{{ old('author') }}">
        <input type="hidden" name="create_date" id="create_date" value="{{ old('create_date') }}">
@endif
        <input type="hidden" name="process_id" id="process_id" value="">
        <a href="#" class="close_overlay">close</a>

        <div class="modal_window">
            <div class="close"><a href="#">×</a></div>

            <div class="modal-content clearfix">
                <dl>
                    <dt>
                        内容

                        @if ($errors->has('part'))
                            <span class="help-block2">
                                <strong>{{ $errors->first('part') }}</strong>
                            </span>
                        @endif
                    </dt>
                    <dd>
                        <select name="part" id="part">
                            <option value="">－選択－</option>
                        @foreach ($part as $key => $name)
                            <option value="{{ $key }}"@if ($key == old('part')) selected @endif>{{ $name }}</option>
                        @endforeach
                        </select>
                    </dd>
<?php /*
                    <dd class="add">追加</dd>
*/ ?>
                    <dt>
                        担当

                        @if ($errors->has('staff'))
                            <span class="help-block2">
                                <strong>{{ $errors->first('staff') }}</strong>
                            </span>
                        @endif
                    </dt>
                    <dd>
                        <select id="staff" name="staff">
                            <option value="">－選択－</option>
                        @foreach ($staff as $key => $name)
                            <option value="{{ $key }}"@if ($key == old('staff')) selected @endif>{{ $name }}</option>
                        @endforeach
                        </select>
                    </dd>
<?php /*
                    <dd class="add">追加</dd>
*/ ?>
                    <dt>
                        予定日

                        @if ($errors->has('st_expected'))
                            <br />
                            <span class="help-block2">
                                <strong>{{ $errors->first('st_expected') }}</strong>
                            </span>
                        @endif

                        @if ($errors->has('ed_expected'))
                            <br />
                            <span class="help-block2">
                                <strong>{{ $errors->first('ed_expected') }}</strong>
                            </span>
                        @endif
                    </dt>
                    <dd class="calendar">
                        <input type="text" name="st_expected" id="datepicker-01" value="{{ old('st_expected') }}" placeholder="開始">
                    </dd>
                    <dd class="calendar">
                        <input type="text" name="ed_expected" id="datepicker-02" value="{{ old('ed_expected') }}" placeholder="終了">
                    </dd>

                    <dt>実績日</dt>
                    <dd class="calendar"><input type="text" name="st_result" id="datepicker-03" value="" placeholder="開始"></dd>
                    <dd class="calendar"><input type="text" name="ed_result" id="datepicker-04" value="" placeholder="終了"></dd>
                </dl>

                <div class="clearfix"></div>
@if (is_object($projects))
                <div class="button green" id="del" style="display:none;"><a href="javascript:oneDelete();">削除</a></div>
@endif
                <div class="button green" id="save" style="display:block;"><a href="javascript:document.input_form.submit();">登録</a></div>
            </div>
        </div><!--/.modal_window-->

        </form>
    </div><!--/#open03-->

</div><!--/#modal-->


@endsection

@section('footer2')

<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>

<script>
$('#datepicker-01').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: '/images/icon-schedule.png'
});
</script>
<script>
$('#datepicker-02').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: '/images/icon-schedule.png'
});
</script>
<script>
$('#datepicker-03').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: '/images/icon-schedule.png'
});
</script>
<script>
$('#datepicker-04').datepicker({
  showOn: 'button',
  buttonImageOnly: true,
  buttonImage: '/images/icon-schedule.png'
});
</script>

<script>
function prevMonth() {
    var form = document.cal_form;
    form.ref.value = parseInt(form.ref.value) - 1;
    form.submit();
}
function nextMonth() {
    var form = document.cal_form;
    form.ref.value = parseInt(form.ref.value) + 1;
    form.submit();
}
function init(){
@if (!($errors->has('part') || $errors->has('staff') || $errors->has('st_expected') || $errors->has('ed_expected')))
    document.getElementById('process_id').value = '';
    document.getElementById('part').value = '';
    document.getElementById('staff').value = '';
    document.getElementById('datepicker-01').value = '';
    document.getElementById('datepicker-02').value = '';
@endif

    document.getElementById('del').style.display = 'none';
    document.getElementById('save').style.display = 'block';
}
function edit(process_id, part, staff, st_expected, ed_expected) {
    document.getElementById('process_id').value = process_id;
    document.getElementById('part').value = part;
    document.getElementById('staff').value = staff;
    document.getElementById('datepicker-01').value = st_expected;
    document.getElementById('datepicker-02').value = ed_expected;

    if (process_id != '') {
        document.getElementById('del').style.display = 'block';
        document.getElementById('save').style.display = 'none';
    } else {
        document.getElementById('del').style.display = 'none';
        document.getElementById('save').style.display = 'block';
    }
}
function oneDelete() {
    var form = document.input_form;
    var ref = form.ref.value;
    var project_id = form.project_id.value;
    var process_id = form.process_id.value;
    window.location.href = "{{ url('/deleteoneprocess/ref') }}/" + ref + '/project_id/' + project_id + '/process_id/' + process_id;
}
function openPage() {
@if ($errors->has('part') || $errors->has('staff') || $errors->has('st_expected') || $errors->has('ed_expected'))

    location.href = "#open03";
@endif
}
window.onload = openPage;
</script>
@endsection
