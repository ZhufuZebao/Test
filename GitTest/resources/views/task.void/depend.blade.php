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


        <div style="" class="gantt" id="GanttChartTable"></div>

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
         xml += "<task><pID>{{ $src->id }}</pID><pName>{{ $src->name }}</pName><pStart>{{ $src->st_date }}</pStart><pEnd>{{ $src->ed_date }}</pEnd><pClass>ggroupblack</pClass><pLink></pLink><pMile>0</pMile><pRes></pRes><pComp>0</pComp><pGroup>1</pGroup><pParent>{{ $src->parent_id }}</pParent><pOpen>1</pOpen><pDepend /></task>";

         xml += "<task><pID>{{ $dst->id }}</pID><pName>{{ $dst->name }}</pName><pStart>{{ $dst->st_date }}</pStart><pEnd>{{ $dst->ed_date }}</pEnd><pClass>ggroupblack</pClass><pLink></pLink><pMile>0</pMile><pRes></pRes><pComp>0</pComp><pGroup>1</pGroup><pParent>{{ $dst->parent_id }}</pParent><pOpen>1</pOpen><pDepend>{{ $src->id }}</pDepend></task>";

         xml += "</project>";
         JSGantt.parseXMLString(xml, g);
         g.Draw();
        </script>

        <input type="hidden" name="parent_id" value="{{ $dst->parent_id }}">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($model)
        依存関係は登録されています<br>
        依存関係を削除しますか？
        <a href="{{ route('task/undepend',['id'=>$model->id]) }}">はい</a>
    @else
        依存関係を登録しますか？
        <div class="button green"><a href="javascript:document.input_form.submit();">はい</a></div>
    @endif

    </form>

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
