@extends('layouts.app')

@section('header')
<title>スケジュール</title>
<script>
function setRepeat(no)
{
    if (no == 4) {
        document.getElementById('week').style.display='inline'
    } else {
        document.getElementById('week').style.display='none'
    }

    if (no == 0) {
        document.getElementById('period').style.display='none'
    } else {
        document.getElementById('period').style.display='inline'
    }

}

function deleteSubmit()
{
    if (confirm("削除します。よろしいですか？") == false) {
        return;
    }
    document.delete_form.action = "{{ url('/deleteschedule') }}";
    document.delete_form.submit();
}

function delete1Submit()
{
    if (confirm("削除します。よろしいですか？") == false) {
        return;
    }
    document.delete_form.action = "{{ url('/deleteschedule') }}";
    document.getElementById('delete_kbn3').checked = true;
    document.delete_form.submit();
}
</script>
@endsection

@section('content')
<!--container-->
<div class="container low schedule">
    <h1>スケジュール</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/editschedule') }}">
    {{ csrf_field() }}
    <input type="hidden" name="id" id="id" value="{{ $id }}" />
    <input type="hidden" name="sub_id" id="sub_id" value="{{ $sub_id }}" />
    <input type="hidden" name="relation_id" id="relation_id" value="{{ $data->relation_id }}" />
    <input type="hidden" name="ref" id="ref" value="{{ $ref }}" />

    <dl>
        <dt>日付</dt>
        <dd>
            {{ sprintf('%04d/%02d/%02d', $target_y, $target_m, $target_d) }}
            <input type="hidden" name="date" id="date" value="{{ sprintf('%04d/%02d/%02d', $target_y, $target_m, $target_d) }}" />
            <input type="hidden" name="target_y" id="target_y" value="{{ $target_y }}" />
            <input type="hidden" name="target_m" id="target_m" value="{{ $target_m }}" />
            <input type="hidden" name="target_d" id="target_d" value="{{ $target_d }}" />
            <input type="hidden" name="back" id="back" value="{{ $back }}" />
        </dd>

        <dt>開始時間</dt>
        <dd>
            <select name="st_time_h" id="st_time_h" style="width:70px;">
                <option value=""></option>
            @for ($i = 0; $i <= 23; $i++)
                <option value="{{ $i }}"@if ($i == old('st_time_h', (int)substr($data->st_time, 0, 2))) selected @endif>{{ $i }}</option>
            @endfor
            </select>
            ：
            <select name="st_time_m" id="st_time_m" style="width:70px;">
                <option value=""></option>
            @for ($i = 0; $i <= 59; $i++)
                <option value="{{ $i }}"@if ($i == old('st_time_m', (int)substr($data->st_time, 3, 2))) selected @endif>{{ sprintf('%02d', $i) }}</option>
            @endfor
            </select>
        </dd>

        <dt>終了時間</dt>
        <dd>
            <select name="ed_time_h" id="ed_time_h" style="width:70px;">
                <option value=""></option>
            @for ($i = 0; $i <= 23; $i++)
                <option value="{{ $i }}"@if ($i == old('ed_time_h', (int)substr($data->ed_time, 0, 2))) selected @endif>{{ $i }}</option>
            @endfor
            </select>
            ：
            <select name="ed_time_m" id="ed_time_m" style="width:70px;">
                <option value=""></option>
            @for ($i = 0; $i <= 59; $i++)
                <option value="{{ $i }}"@if ($i == old('ed_time_m', (int)substr($data->ed_time, 3, 2))) selected @endif>{{ sprintf('%02d', $i) }}</option>
            @endfor
            </select>
        </dd>

        <dt>繰り返し</dt>
        <dd>
            <div class="check-repeat">
                <label for="repeat0" class="repeat"><input type="radio" name="repeat" id="repeat0" value="0" onClick="setRepeat(0)" onChange="setRepeat(0)"{{ (old('repeat') == 0 || $data->repeat_kbn == 0) ? ' checked': '' }} />1日</label>
                <label for="repeat1" class="repeat"><input type="radio" name="repeat" id="repeat1" value="1" onClick="setRepeat(1)" onChange="setRepeat(1)"{{ (old('repeat') == 1 || $data->repeat_kbn == 1) ? ' checked': '' }} />毎日</label>
                <label for="repeat2" class="repeat"><input type="radio" name="repeat" id="repeat2" value="2" onClick="setRepeat(2)" onChange="setRepeat(2)"{{ (old('repeat') == 2 || $data->repeat_kbn == 2) ? ' checked': '' }} />毎週</label>
                <label for="repeat3" class="repeat"><input type="radio" name="repeat" id="repeat3" value="3" onClick="setRepeat(3)" onChange="setRepeat(3)"{{ (old('repeat') == 3 || $data->repeat_kbn == 3) ? ' checked': '' }} />毎月</label>
                <label for="repeat4" class="repeat"><input type="radio" name="repeat" id="repeat4" value="4" onClick="setRepeat(4)" onChange="setRepeat(4)"{{ (old('repeat') == 4 || $data->repeat_kbn == 4) ? ' checked': '' }} />曜日指定</label>
            </div>

            <div id="week" style="display:none;">
                <label for="week1" class="week"><input type="checkbox" name="week1" id="week1" value="1"{{ (old('week1') == 1 || $data->week1 == 1) ? ' checked': '' }} />日</label>
                <label for="week2" class="week"><input type="checkbox" name="week2" id="week2" value="1"{{ (old('week2') == 1 || $data->week2 == 1) ? ' checked': '' }} />月</label>
                <label for="week3" class="week"><input type="checkbox" name="week3" id="week3" value="1"{{ (old('week3') == 1 || $data->week3 == 1) ? ' checked': '' }} />火</label>
                <label for="week4" class="week"><input type="checkbox" name="week4" id="week4" value="1"{{ (old('week4') == 1 || $data->week4 == 1) ? ' checked': '' }} />水</label>
                <label for="week5" class="week"><input type="checkbox" name="week5" id="week5" value="1"{{ (old('week5') == 1 || $data->week5 == 1) ? ' checked': '' }} />木</label>
                <label for="week6" class="week"><input type="checkbox" name="week6" id="week6" value="1"{{ (old('week6') == 1 || $data->week6 == 1) ? ' checked': '' }} />金</label>
                <label for="week7" class="week"><input type="checkbox" name="week7" id="week7" value="1"{{ (old('week7') == 1 || $data->week7 == 1) ? ' checked': '' }} />土</label>
            </div>
            <br clear="all" />

            <div id="period" style="display:none;">
                期間<br />
                <select name="st_y">
                @for ($y = (int)date('Y') - 20; $y <= (int)date('Y') + 5; $y++)
                    <option value="{{ $y }}"{{ old('st_y') ? (($y == old('st_y')) ? ' selected' : '') : (($y == (int)$data->st_year) ? ' selected' : '') }}>{{ $y }}年</option>
                @endfor
                </select>
                <select name="st_m">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}"{{ old('st_m') ? (($m == old('st_m')) ? ' selected' : '') : (($m == (int)$data->st_month) ? ' selected' : '') }}>{{ $m }}月</option>
                @endfor
                </select>
                <select name="st_d">
                @for ($d = 1; $d <= 31; $d++)
                    <option value="{{ $d }}"{{ old('st_m') ? (($d == old('st_m')) ? ' selected' : '') : (($d == (int)$data->st_day) ? ' selected' : '') }}>{{ $d }}日</option>
                @endfor
                </select>
                <div class="kara">～</div>
                <br clear="all" />
                <select name="ed_y">
                @for ($y = (int)date('Y') - 20; $y <= (int)date('Y') + 5; $y++)
                    <option value="{{ $y }}"{{ old('ed_y') ? (($y == old('ed_y')) ? ' selected' : '') : (($y == (int)$data->ed_year) ? ' selected' : '') }}>{{ $y }}年</option>
                @endfor
                </select>
                <select name="ed_m">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}"{{ old('ed_m') ? (($m == old('ed_m')) ? ' selected' : '') : (($m == (int)$data->ed_month) ? ' selected' : '') }}>{{ $m }}月</option>
                @endfor
                </select>
                <select name="ed_d">
                @for ($d = 1; $d <= 31; $d++)
                    <option value="{{ $d }}"{{ old('ed_d') ? (($d == old('ed_d')) ? ' selected' : '') : (($d == (int)$data->ed_day) ? ' selected' : '') }}>{{ $d }}日</option>
                @endfor
                </select>
                <br clear="all" />
            </div>

            @if ($errors->has('st_span'))
                <span class="help-block">
                    <strong>{{ $errors->first('st_span') }}</strong>
                </span>
            @endif

            @if ($errors->has('ed_span'))
                <span class="help-block">
                    <strong>{{ $errors->first('ed_span') }}</strong>
                </span>
            @endif

        </dd>

        <dt>見出し</dt>
        <dd>
            <select name="type" class="type">
                @foreach ($types as $item)
                <option value="{{ $item->id }}"{{ ($item->id == $data->type_id) ? ' selected' : '' }}>{{ $item->name }}</option>
                @endforeach
            </select>
            <input type="text" name="subject" id="subject" value="{{ old('subject', $data->subject) }}" />

            @if ($errors->has('subject'))
                <span class="help-block">
                    <strong>{{ $errors->first('subject') }}</strong>
                </span>
            @endif
        </dd>

        <dt>内容</dt>
        <dd>
            <textarea name="comment" id="comment" value="{{ old('comment', $data->comment) }}" row="10"></textarea>

            @if ($errors->has('comment'))
                <span class="help-block">
                    <strong>{{ $errors->first('comment') }}</strong>
                </span>
            @endif
        </dd>

    </dl>

    </form>

    <div class="button green"><a href="javascript:document.input_form.submit();">入力内容を確認</a></div>
@if ($data->relation_id != '')
    <div class="button gray"><a href="#open02">削　除</a></div>
@else
    <div class="button gray"><a href="javascript:delete1Submit();">削　除</a></div>
@endif

</div>

<div id="modal">
    <div id="open02">
        <form class="form-horizontal" name="delete_form" role="form" method="POST" action="{{ url('/deleteschedule') }}">
        {{ csrf_field() }}
        <input type="hidden" name="relation_id" id="relation_id" value="{{ $data->relation_id }}" />
        <input type="hidden" name="id" id="id" value="{{ $id }}" />
        <input type="hidden" name="sub_id" id="sub_id" value="{{ $sub_id }}" />
        <input type="hidden" name="ref" id="ref" value="{{ $ref }}" />
        <input type="hidden" name="date" id="date" value="{{ sprintf('%04d/%02d/%02d', $target_y, $target_m, $target_d) }}" />
            <input type="hidden" name="back" id="back" value="{{ $back }}" />

        <a href="#" class="close_overlay">close</a>

        <div class="modal_window">
            <div class="close"><a href="#">×</a></div>

            <div class="modal-content clearfix">

                <ul>
                    <li><label for="delete_kbn1"><input type="radio" name="delete_kbn" id="delete_kbn1" value="1" />{{ sprintf('%04d/%02d/%02d', $target_y, $target_m, $target_d) }} １日だけ</label></li>
                    <li><label for="delete_kbn2"><input type="radio" name="delete_kbn" id="delete_kbn2" value="2" />{{ sprintf('%04d/%02d/%02d', $target_y, $target_m, $target_d) }} 以降すべて</label></li>
                    <li><label for="delete_kbn3"><input type="radio" name="delete_kbn" id="delete_kbn3" value="3" />期間内すべて<br />　　（{{ sprintf('%04d/%02d/%02d', $data->st_year, $data->st_month, $data->st_day). ' - '. sprintf('%04d/%02d/%02d', $data->ed_year, $data->ed_month, $data->ed_day) }}）</label></li>
                </ul>

                <div class="clearfix"></div>
                <div class="button green" id="del"><a href="javascript:deleteSubmit();">削除</a></div>
            </div>
        </div><!--/.modal_window-->

        </form>
    </div><!--/#open02-->

</div><!--/#modal-->

<script>
window.onload = function() {
    if (document.getElementById('repeat1').checked == true ||
        document.getElementById('repeat2').checked == true ||
        document.getElementById('repeat3').checked == true ||
        document.getElementById('repeat4').checked == true) {
        document.getElementById('period').style.display='inline'
    } else {
        document.getElementById('period').style.display='none'
    }
    if (document.getElementById('repeat4').checked == true) {
        document.getElementById('week').style.display='inline'
    } else {
        document.getElementById('week').style.display='none'
    }
}
</script>

@endsection
