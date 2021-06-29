<!doctype html>
<html>
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
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
             height: 100%;
             margin: 0;
             padding: 0;
         }
        </style>
    </head>
    <body>

        {{ Breadcrumbs::render('gantt') }}

        <h2>
            工程管理
        </h2>

        <button onClick="drawByDay()">日</button>
        <button onClick="drawByWeek()">週</button>
        <button onClick="drawByMonth()">月</button>
        <button onClick="drawByYear()">年</button>
        <button onClick="drawByQuarter()">四半期</button>
        <button onClick="outputPdf()">PDF</button>
        <button onClick="outputExcel()">Excel</button>
        <button id="enable-editing">編集モード</button>

        {!! Form::open(['url'=>route('gantt/pdf'),'method'=>'post','id'=>'form-generate-pdf','target'=>'_blank']) !!}
        {!! Form::hidden('base64', null, ['id'=>'input-hidden-base64']) !!}
        {!! Form::close() !!}

        {!! Form::open(['url'=>route('gantt/excel'),'method'=>'post','id'=>'form-generate-excel','target'=>'_blank']) !!}
        {!! Form::hidden('task_id', null, ['id'=>'input-hidden-task_id']) !!}
        {!! Form::close() !!}

        <div>
        {!! Form::open() !!}

        {!! Form::label('st_date', "開始日") !!}
        {!! Form::text('st_date', $model->st_date, ['id'=>'actualStart']) !!} 

        {!! Form::label('ed_date', "終了日") !!}
        {!! Form::text('ed_date', $model->ed_date, ['id'=>'actualEnd']) !!} 

        {!! Form::submit("更新") !!}

        {!! Form::close() !!}
        </div>

        <div id="anygantt"></div>

        <a href="{{ route('gantt/create') }}">新しいタスク</a>

        <script type="text/javascript">

         var treeData;
         var ganttChart;
         var stage;
         var editedTasks;
         var editedConnects;

         function updateAll()
         {
             // TODO
         }

         function drawByDay()
         {
             var scale = window.ganttChart.xScale().zoomLevels([
                 [
                     {unit: 'day',   count: 1},
                     {unit: 'month', count: 1},
                     {unit: 'year',  count: 1}
                 ]
             ]);
             window.ganttChart.zoomIn();
             window.ganttChart.zoomOut();
             window.ganttChart.draw();
         }

         function drawByWeek()
         {
             var scale = window.ganttChart.xScale().zoomLevels([
                 [
                     {unit: 'week',  count: 1},
                     {unit: 'month', count: 1},
                     {unit: 'year',  count: 1}
                 ]
             ]);
             window.ganttChart.zoomIn();
             window.ganttChart.zoomOut();
             window.ganttChart.draw();
         }

         function drawByMonth()
         {
             var scale = window.ganttChart.xScale().zoomLevels([
                 [
                     {unit: 'month', count: 1},
                     {unit: 'year',  count: 1}
                 ]
             ]);
             window.ganttChart.zoomIn();
             window.ganttChart.zoomOut();
             window.ganttChart.draw();
         }

         function drawByYear()
         {
             var scale = window.ganttChart.xScale().zoomLevels([
                 [
                     {unit: 'year',  count: 1}
                 ]
             ]);
             window.ganttChart.zoomIn();
             window.ganttChart.zoomOut();
             window.ganttChart.draw();
         }

         function drawByQuarter()
         {
             var scale = window.ganttChart.xScale().zoomLevels([
                 [
                     {unit: 'quarter', count: 1},
                     {unit: 'year',    count: 1}
                 ]
             ]);
             window.ganttChart.zoomIn();
             window.ganttChart.zoomOut();
             window.ganttChart.draw();
         }

         function outputExcel() {
             console.log(window.treeData.getChildren());
             var children = window.treeData.getChildren();
             var ids = [];

             for (var i = 0; i < children.length; i++) {
                 var child = children[i];
                 ids.push(child.get('id'));
                 console.log(ids);
             }

             form = document.getElementById('form-generate-excel');
             elem = document.getElementById('input-hidden-task_id');
             elem.value = ids;
             form.submit();
         }

         function outputPdf() {
             // send script to anychart.com, get PNG as base64 string.
             window.stage.getPngBase64String(function (response) {
                 form = document.getElementById('form-generate-pdf');
                 elem = document.getElementById('input-hidden-base64');
                 elem.value = response;
                 form.submit();
             });
         }

         function configureDefaultGantt(chart)
         {
             chart.data(window.treeData);

             chart.dataGrid().tooltip(true);
             chart.getTimeline().tooltip(false);

             //set DataGrid and TimeLine visual settings
             chart.dataGrid().rowEvenFill('green .1');
             chart.getTimeline().rowEvenFill('green .1');

             var timeline = chart.getTimeline();
             timeline.editStartConnectorMarkerType("diamond");
             timeline.editFinishConnectorMarkerType("diamond");
//             timeline.editStartConnectorMarkerSize(0);
//             timeline.editFinishConnectorMarkerSize(0);
             timeline.editStartConnectorMarkerHorizontalOffset(15);
             timeline.editFinishConnectorMarkerHorizontalOffset(-15);

             // get chart data grid link to set column settings
             var dataGrid = chart.dataGrid();

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
                     .format('{%actualStart}{dateTimeFormat:yyyy-MM-dd HH:mm}')
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

             var scale = chart.xScale();
             scale.minimum({{ (strtotime($model->st_date) + 3600 * 9) * 1000 /*+900 JST*/ }});
             scale.maximum({{ (strtotime($model->ed_date) + 3600 * 9) * 1000 /*+900 JST*/ }});

             scale.zoomLevels([
                 [
                     {unit: 'day',   count: 1},
                     {unit: 'month', count: 1},
                     {unit: 'year',  count: 1}
                 ]
             ]);

             //chart.container('anygantt');
             chart.container(stage);
             return chart;
         }

         

         anychart.onDocumentReady(function() {

             $.ajaxSetup({
                 headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
             });

             // The data used in this sample can be obtained from the CDN
             // https://cdn.anychart.com/samples/gantt-charts/activity-oriented-chart/data.js
             anychart.data.loadJsonFile('{{ route('gantt/json-by-date',['start'=>$model->st_date,'end'=>$model->ed_date]) }}', function(data) {
                 // create data tree
                 stage    = anychart.graphics.create("anygantt");
                 treeData = anychart.data.tree(data, 'as-table');

                 var chart = anychart.ganttProject();

                 chart = configureDefaultGantt(chart);

                 // initiate chart drawing
                 chart.draw();
                 chart.collapseAll();
                 chart.fitAll();

                 var jump_to_edit_page = chart.listen("rowDblClick", function(e) {
                     var id = e.item.get('id');
                     window.location.replace("{{ url('/gantt/edit') }}/" + id );
                     return;
                 });

                 $('#enable-editing').on('click',function(e){
                     chart.unlistenByKey(jump_to_edit_page);
                     chart.editing(true);
                     e.target.style.color="red";
                 });

                 chart.listen("beforeCreateConnector", function(e) {
                     var src_id = e.source.get('id');
                     var dst_id = e.target.get('id');
                     console.log(e.field);

                     $.post('{{ route("task-depend/create-by-ajax") }}', { 'src_id': src_id, 'dst_id': dst_id })
                      .done(function(msg){
                          console.log("success");
                          console.log(msg);
                      })
                      .fail(function(xhr, status, error) {
                          console.log(status);
                          console.log(error);
                      });
                 });

                 treeData.listen("treeItemRemove", function(e){
	                 console.log("The " + e.itemIndex +  " item was removed");
                     
                 });

                 chart.listen("connectordblclick", function(e){
                     if(! confirm("コネクタを削除しますか")){
                         return;
                     }
                     var src_id = e.fromItem.get('id');
                     var dst_id = e.toItem.get('id');

                     $.post('{{ route("task-depend/delete-by-ajax") }}', { 'src_id': src_id, 'dst_id': dst_id })
                      .done(function(msg){
                          console.log("success");
                          console.log(msg);
                      });
                 });

                 treeData.listen("treeItemUpdate", function(e){
                     return;
                     console.log('t: '+e.type);
                     console.log('f: '+e.field);
                     console.log('p: '+e.path);
                     console.log('v: '+e.value);

                     console.log(e.item);
                     console.log(e.path);
                     if('connector' == e.field){
                     }

                     var id = e.item.get('id');
                     var value;


                     if(('actualStart' == e.field) || ('actualEnd' == e.field))
                     {
                             var date = new Date(e.value);
                             var y = date.getFullYear();
                             var m = date.getMonth() + 1;
                             var d = date.getDate();

                             value = y + '-' + m + '-' + d;
                     }
                     else
                     {
                             value = e.value;
                     }

                     $.post('{{ route("gantt/update-by-ajax") }}', { 'id': id, 'field': e.field, 'value': value })
                      .done(function(msg){
                          console.log("success");
                          console.log(msg);
                      })
                      .fail(function(xhr, status, error) {
                          console.log(status);
                          console.log(error);
                          e.item.set('rowFill','red .1');
                      });

                 });

                 window.ganttChart = chart;
             });
         });
        </script>
    </body>
</html>
