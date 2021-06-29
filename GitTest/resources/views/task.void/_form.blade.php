@extends('layouts.debug')

@section('header')
<title>工程管理</title>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css">
<link href="{{ url('/') }}/jsgantt.css" rel="stylesheet" type="text/css"/>
<link href="{{ url('/') }}/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
<script src="{{ url('/') }}/js/jsgantt.js" type="text/javascript"></script>
<script src="{{ url('/') }}/js/jquery-3.3.1.min.js" type="text/javascript"></script>
<script src="{{ url('/') }}/js/jquery-ui.min.js" type="text/javascript"></script>
@endsection

@section('content')

<!--container-->
<div class="container low step new">
    <h1><a href="{{ route('task') }}">工程管理</a></h1>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url()->current() }}">
    {{ csrf_field() }}

    @if(0 < $model->id)
        <h2>id:{{ $model->id }}</h2>
    @else
        <h2>(新規)</h2>
    @endif

    @if($model->id || $model->parent_id)

        <div style="" class="gantt" id="GanttChartTable"></div>

        @if($model->id)
        <a href="{{ route('task/append', [ 'id' => $model->id ]) }}">子を追加</a>
        @endif

        <script type="text/javascript">

         var g = new JSGantt.GanttChart(document.getElementById('GanttChartTable'), 'day');
         g.setCaptionType('Complete');
         g.setQuarterColWidth(36);
         g.setDateTaskDisplayFormat('day dd month yyyy');
         g.setDayMajorDateDisplayFormat('mon yyyy - Week ww');
         g.setWeekMinorDateDisplayFormat('dd mon');
         g.setShowTaskInfoLink(1);
         g.setShowEndWeekDate(0);
         g.setUseToolTip(0);
         g.setUseSingleCell(10000);
         g.setFormatArr('Day', 'Week', 'Month', 'Quarter');
         var xml = "<project>";
         @if($parent = $model->parent)
         xml += "<task><pID>{{ $parent->id }}</pID><pName>{{ $parent->name }}</pName><pStart>{{ $parent->st_date }}</pStart><pEnd>{{ $parent->ed_date }}</pEnd><pClass>gtaskblue</pClass><pLink></pLink><pMile>0</pMile><pRes></pRes><pComp>0</pComp><pGroup>1</pGroup><pParent>{{ $parent->parent_id }}</pParent><pOpen>1</pOpen><pDepend>{{ $parent->depends->implode('src_id') }}</pDepend></task>";
         @endif
         xml += "<task><pID>{{ $model->id }}</pID><pName>{{ $model->name }}</pName><pStart>{{ $model->st_date }}</pStart><pEnd>{{ $model->ed_date }}</pEnd><pClass>gtaskred</pClass><pLink></pLink><pMile>0</pMile><pRes></pRes><pComp>0</pComp><pGroup>1</pGroup><pParent>{{ $model->parent_id }}</pParent><pOpen>1</pOpen><pDepend>{{ $model->depends->implode('src_id') }}</pDepend></task>";
         @if($children = $model->children)
           @foreach($children as $child)
         xml += "<task><pID>{{ $child->id }}</pID><pName>{{ $child->name }}</pName><pStart>{{ $child->st_date }}</pStart><pEnd>{{ $child->ed_date }}</pEnd><pClass>gtaskblue</pClass><pLink></pLink><pMile>0</pMile><pRes></pRes><pComp>0</pComp><pGroup>1</pGroup><pParent>{{ $child->parent_id }}</pParent><pOpen>1</pOpen><pDepend>{{ $child->depends->implode('src_id') }}</pDepend></task>";
           @endforeach
         @endif

         xml += "</project>";
         JSGantt.parseXMLString(xml, g);
         g.Draw();
        </script>

        <input type="hidden" name="parent_id" value="{{ $model->parent_id }}">

    @endif


    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <dl class="step-top">
        <dt>
            名称

            @if ($errors->has('name'))
                <span class="help-block">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif
        </dt>
        <dd>
            <input type="text" name="name" id="task_name" value="{{ old('name') ?? $model->name }}">
        </dd>

        <dt>
            開始日

            @if ($errors->has('st_date'))
                <span class="help-block">
                    <strong>{{ $errors->first('st_date') }}</strong>
                </span>
            @endif
        </dt>
        <dd class="calendar">
            <input type="text" name="st_date" id="datepicker-01" value="{{ old('st_date') ?? $model->st_date }}">
        </dd>

        <dt>
            完了予定日

            @if ($errors->has('ed_date'))
                <span class="help-block">
                    <strong>{{ $errors->first('ed_date') }}</strong>
                </span>
            @endif
        </dt>
        <dd class="calendar">
            <input type="text" name="ed_date" id="datepicker-02" value="{{ old('ed_date') ?? $model->ed_date }}">
        </dd>

        <dt>
            備考

            @if ($errors->has('note'))
                <span class="help-block">
                    <strong>{{ $errors->first('note') }}</strong>
                </span>
            @endif
        </dt>
        <dd>
            <input name="note" id="note" value="{{ old('note') ?? $model->note }}">
        </dd>

    </dl>
    </form>

    <div class="button green"><a href="javascript:document.input_form.submit();">保存</a></div>

</div>
<!--/container-->

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
@endsection
