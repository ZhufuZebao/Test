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

function nextPage()
{
    if (document.getElementById('repeat4').checked == true) {
        var chk = 0;
        for (i = 1; i <= 7; i++) {
            if (document.getElementById('week' + i).checked == true) {
                chk++;
            }
        }
        if (chk == 0) {
            alert('曜日を指定してください。');
            return;
        }
    }
    document.input_form.submit();
}
</script>
@endsection

@section('content')
<!--container-->
<div class="container low schedule">
    <h1>スケジュール</h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/newschedule') }}">
    {{ csrf_field() }}
    <input type="hidden" name="id" id="id" value="" />
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
                <option value="{{ $i }}"@if ($i == old('st_time_h')) selected @endif>{{ $i }}</option>
            @endfor
            </select>
            ：
            <select name="st_time_m" id="st_time_m" style="width:70px;">
                <option value=""></option>
            @for ($i = 0; $i <= 59; $i++)
                <option value="{{ $i }}"@if ($i == old('st_time_m')) selected @endif>{{ sprintf('%02d', $i) }}</option>
            @endfor
            </select>
        </dd>

        <dt>終了時間</dt>
        <dd>
            <select name="ed_time_h" id="ed_time_h" style="width:70px;">
                <option value=""></option>
            @for ($i = 0; $i <= 23; $i++)
                <option value="{{ $i }}"@if ($i == old('ed_time_h')) selected @endif>{{ $i }}</option>
            @endfor
            </select>
            ：
            <select name="ed_time_m" id="ed_time_m" style="width:70px;">
                <option value=""></option>
            @for ($i = 0; $i <= 59; $i++)
                <option value="{{ $i }}"@if ($i == old('ed_time_m')) selected @endif>{{ sprintf('%02d', $i) }}</option>
            @endfor
            </select>
        </dd>

        <dt>繰り返し</dt>
        <dd>
            <div class="check-repeat">
                <label for="repeat0" class="repeat"><input type="radio" name="repeat" id="repeat0" value="0" onClick="setRepeat(0)" onChange="setRepeat(0)"{{ (old('repeat') == 0) ? ' checked': '' }} />1日</label>
                <label for="repeat1" class="repeat"><input type="radio" name="repeat" id="repeat1" value="1" onClick="setRepeat(1)" onChange="setRepeat(1)"{{ (old('repeat') == 1) ? ' checked': '' }} />毎日</label>
                <label for="repeat2" class="repeat"><input type="radio" name="repeat" id="repeat2" value="2" onClick="setRepeat(2)" onChange="setRepeat(2)"{{ (old('repeat') == 2) ? ' checked': '' }} />毎週</label>
                <label for="repeat3" class="repeat"><input type="radio" name="repeat" id="repeat3" value="3" onClick="setRepeat(3)" onChange="setRepeat(3)"{{ (old('repeat') == 3) ? ' checked': '' }} />毎月</label>
                <label for="repeat4" class="repeat"><input type="radio" name="repeat" id="repeat4" value="4" onClick="setRepeat(4)" onChange="setRepeat(4)"{{ (old('repeat') == 4) ? ' checked': '' }} />曜日指定</label>
            </div>

            <div id="week" style="display:none;">
                <label for="week1" class="week"><input type="checkbox" name="week1" id="week1" value="1"{{ (old('week1') == 1) ? ' checked': '' }} />日</label>
                <label for="week2" class="week"><input type="checkbox" name="week2" id="week2" value="1"{{ (old('week2') == 1) ? ' checked': '' }} />月</label>
                <label for="week3" class="week"><input type="checkbox" name="week3" id="week3" value="1"{{ (old('week3') == 1) ? ' checked': '' }} />火</label>
                <label for="week4" class="week"><input type="checkbox" name="week4" id="week4" value="1"{{ (old('week4') == 1) ? ' checked': '' }} />水</label>
                <label for="week5" class="week"><input type="checkbox" name="week5" id="week5" value="1"{{ (old('week5') == 1) ? ' checked': '' }} />木</label>
                <label for="week6" class="week"><input type="checkbox" name="week6" id="week6" value="1"{{ (old('week6') == 1) ? ' checked': '' }} />金</label>
                <label for="week7" class="week"><input type="checkbox" name="week7" id="week7" value="1"{{ (old('week7') == 1) ? ' checked': '' }} />土</label>
            </div>
            <br clear="all" />

            <div id="period" style="display:none;">
                <br clear="all" />
                期間<br />
                <select name="st_y">
                @for ($y = (int)date('Y') - 20; $y <= (int)date('Y') + 5; $y++)
                    <option value="{{ $y }}"{{ old('st_y') ? (($y == old('st_y')) ? ' selected' : '') : (($y == (int)$target_y) ? ' selected' : '') }}>{{ $y }}年</option>
                @endfor
                </select>
                <select name="st_m">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}"{{ old('st_m') ? (($m == old('st_m')) ? ' selected' : '') : (($m == (int)$target_m) ? ' selected' : '') }}>{{ $m }}月</option>
                @endfor
                </select>
                <select name="st_d">
                @for ($d = 1; $d <= 31; $d++)
                    <option value="{{ $d }}"{{ old('st_m') ? (($d == old('st_m')) ? ' selected' : '') : (($d == (int)$target_d) ? ' selected' : '') }}>{{ $d }}日</option>
                @endfor
                </select>
                <div class="kara">～</div>
                <br clear="all" />
                <select name="ed_y">
                @for ($y = (int)date('Y') - 20; $y <= (int)date('Y') + 5; $y++)
                    <option value="{{ $y }}"{{ old('ed_y') ? (($y == old('ed_y')) ? ' selected' : '') : (($y == (int)$target_y) ? ' selected' : '') }}>{{ $y }}年</option>
                @endfor
                </select>
                <select name="ed_m">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}"{{ old('ed_m') ? (($m == old('ed_m')) ? ' selected' : '') : (($m == (int)$target_m) ? ' selected' : '') }}>{{ $m }}月</option>
                @endfor
                </select>
                <select name="ed_d">
                @for ($d = 1; $d <= 31; $d++)
                    <option value="{{ $d }}"{{ old('ed_d') ? (($d == old('ed_d')) ? ' selected' : '') : (($d == (int)$target_d) ? ' selected' : '') }}>{{ $d }}日</option>
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

        <dt>内容</dt>
        <dd>
            <textarea name="comment" id="comment" value="{{ old('comment') }}" row="10"></textarea>

            @if ($errors->has('comment'))
                <span class="help-block">
                    <strong>{{ $errors->first('comment') }}</strong>
                </span>
            @endif
        </dd>
    </dl>

    </form>

    <div class="button green"><a href="javascript:nextPage()">入力内容を確認</a></div>

</div>
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
