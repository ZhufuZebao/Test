@extends('layouts.debug')

@section('header')
<title>工程管理</title>
<script src="https://cdn.anychart.com/releases/8.2.1/js/anychart-base.min.js"></script>
<script src="https://cdn.anychart.com/releases/8.2.1/js/anychart-ui.min.js"></script>
<script src="https://cdn.anychart.com/releases/8.2.1/js/anychart-exports.min.js"></script>
<script src="https://cdn.anychart.com/releases/8.2.1/js/anychart-gantt.min.js"></script>
<script src="https://cdn.anychart.com/releases/8.2.1/js/anychart-data-adapter.min.js"></script>
<script src="{{ url('/') }}/js/anychart-locale-ja.js"></script>
<link rel="stylesheet" href="https://cdn.anychart.com/releases/8.2.1/css/anychart-ui.min.css" />
<link rel="stylesheet" href="https://cdn.anychart.com/releases/8.2.1/fonts/css/anychart-font.min.css" />
<style>
 html, body, #anygantt {
     width: 100%;
     height: 70%;
     margin: 0;
     padding: 0;
 }
</style>
@endsection

@section('content')

<!--container-->

    {{ Breadcrumbs::render($model->id ? 'gantt/edit' : 'gantt/create' ) }}
    <h2>
        編集：{{ $model->name }}
    </h2>

    @if($model->id)
        <a href="{{ route('gantt/create-child', [ 'id' => $model->id ]) }}">子タスクを追加</a>
    @endif
    <div id="anygantt"></div>

    {!! Form::open() !!}

    {!! Form::label('name', "名称") !!}
    {!! Form::text('name', $model->name) !!}

    {!! Form::label('st_date', "開始日") !!}
    <span style="color:gray">{{ $model->st_date }}</span> {!! Form::text('st_date', $model->st_date, ['id'=>'actualStart']) !!} 

    {!! Form::label('st_date', "終了日") !!}
    <span style="color:gray">{{ $model->ed_date }}</span> {!! Form::text('ed_date', $model->ed_date, ['id'=>'actualEnd']) !!} 

    {!! Form::label('progress', "進捗") !!}
    <span style="color:gray"> 0 %</span> {!! Form::text('progress', null, ['id'=>'progressValue']) !!} 

    {!! Form::submit($model->id ? "更新" : "登録") !!}

    <div>
    {!! Form::label('note', "備考") !!}
    {!! Form::textarea('note', $model->note) !!} 
    </div>

    {!! Form::close() !!}

    @if ( $errors->count() > 0 )
        <div style="color:red">
            <p>入力エラーが発生しました:</p>
            <ul>
                @foreach( $errors->all() as $message )
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <script type="text/javascript">
     anychart.onDocumentReady(function() {

         // The data used in this sample can be obtained from the CDN
         // https://cdn.anychart.com/samples/gantt-charts/activity-oriented-chart/data.js
         anychart.data.loadJsonFile("{{ route('gantt/json-by-id',['id'=> $model->id/* edit */ ?? ($model->parent_id/* create-child */ ?? 0 /* create */)]) }}", function(data) {
             // create data tree
             var treeData = anychart.data.tree(data, 'as-table');

             treeData.listen("treeItemUpdate", function(e){

                if(('actualStart' == e.path) || ('actualEnd' == e.path))
                {
                  var date = new Date(e.value);
                  var y = date.getFullYear();
                  var m = date.getMonth() + 1;
                  var d = date.getDate();
                  document.getElementById(e.path).value = y + '-' + m + '-' + d;
                }
                else
                {
                  document.getElementById(e.path).value = e.value;
                }
             });

             // create project gantt chart
             var chart = anychart.ganttProject();

             chart.dataGrid().tooltip(false);
             chart.getTimeline().tooltip(false);

             // set data for the chart
             chart.data(treeData);

             // enable editing
             chart.editing(true);

             //set DataGrid and TimeLine visual settings
             chart.dataGrid().rowEvenFill('green .1');
             chart.getTimeline().rowEvenFill('green .1');

             // get chart data grid link to set column settings
             var dataGrid = chart.dataGrid();

             dataGrid.onEditStart(function () {
               // forbid editing
               return {cancelEdit: true};
             });

             // set first column settings
             dataGrid.column(0)
                     .title('#')
                     .width(30)
                     .labels({
                         hAlign: 'center'
                     });

             // set column settings
             dataGrid.column(0)
                     .title('#')
                     .collapseExpandButtons(true)
                     .depthPaddingMultiplier(15)
                     .width(40)
                     .format(function(item){ return ''; });

             dataGrid.column(1)
                     .title("名称")
                     .collapseExpandButtons(false)
                     .labels()
                     .format(function(){ return this.get('name'); })
                     .hAlign('left')
                     .width(180);

             dataGrid.column(2)
                     .title("開始日")
                     .width(80)
                     .labels()
                     .format('{%actualStart}{dateTimeFormat:yyyy-MM-dd}')
                     .hAlign('left');

             dataGrid.column(3)
                     .title("終了日")
                     .width(80)
                     .labels()
                     .format('{%actualEnd}{dateTimeFormat:yyyy-MM-dd}')
                     .hAlign('left');

             // set start splitter position settings
             chart.splitterPosition(370);

             // set locales
             anychart.format.inputLocale('ja-jp');
             anychart.format.inputDateTimeFormat('yyyy-MM-dd'); //Like '2015-03-12'
             anychart.format.outputLocale('ja-jp');

             chart.container('anygantt');

             var scale = chart.xScale();

             scale.zoomLevels([
                 [
                     {unit: 'day',   count: 1},
                     {unit: 'month', count: 1},
                     {unit: 'year',  count: 1}
                 ]
             ]);

             // initiate chart drawing
             chart.draw();
             chart.fitAll();
             //chart.fitToTask({{ $model->id }});
         });
     });
    </script>
@endsection
