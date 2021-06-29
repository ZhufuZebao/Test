<!DOCTYPE html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">

    <link href="{{ url('/') }}/jsgantt.css" rel="stylesheet" type="text/css"/>
    <link href="{{ url('/') }}/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
    <script src="{{ url('/') }}/js/jsgantt.js" type="text/javascript"></script>
    <script src="{{ url('/') }}/js/jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="{{ url('/') }}/js/jquery-ui.min.js" type="text/javascript"></script>

    <script type="text/javascript">
     $(document).ready(function(){
         $(".gtaskbarcontainer").draggable({
         });
         
         $(".gtaskbarcontainer").droppable({
             drop: function (e, ui) {
                 // get id of current row
                 var src   = $(this)[0];
                 var num   = src.id.search(/_[0-9]+$/);
                 var srcId = src.id.substring(num + 1);

                 // get id of the other row
                 var dst   = ui.helper[0];
                 num       = dst.id.search(/_[0-9]+$/);
                 var dstId = dst.id.substring(num + 1);

                 window.location.replace("{{ url('/task/depend') }}/" + srcId + "/" + dstId);
             }
         });

         $("tr").dblclick(function(e){
             var num = this.id.search(/_[0-9]+$/);
             var vId = this.id.substring(num + 1)

             console.log(this);//DEBUG

             window.location.replace("{{ url('/task/edit') }}/" + vId);
         });

     });
    </script>
</head>

<body>
  <h1>工程管理</h1>

  <p>
      tasks = {{ count($tasks) }} 件
  </p>
  <p>

  <div style="" class="gantt" id="GanttChartTable"></div>
  <script type="text/javascript">

   var g = new JSGantt.GanttChart(document.getElementById('GanttChartTable'), 'day');
   g.setCaptionType('Complete');
   g.setQuarterColWidth(36);
   g.setDateTaskDisplayFormat('day dd month yyyy');
   g.setDayMajorDateDisplayFormat('yyyy/mon - ww Week');
   g.setWeekMinorDateDisplayFormat('dd mon');
   g.setShowTaskInfoLink(1);
   g.setShowEndWeekDate(0);
   g.setUseToolTip(0);
   g.setUseSingleCell(10000);
   g.setFormatArr('Day', 'Week', 'Month', 'Quarter');
   var xml = "<project><?php foreach ($tasks as $t): ?>@include('task/_jsgantt', ['item' => $t, 'color'=>'ggroupblack'])<?php endforeach ?></project>";
   JSGantt.parseXMLString(xml, g);
   g.Draw();
  </script>

    <h2> 使い方 </h2>

    <ul>
    <li>新規: <a href="{{ url('/task/create') }}">最上位に新しいタスクを追加</a></li>
    <li>編集: テーブル列をダブルクリックすると編集できます</li>
    <li>追加: 編集画面で子タスクを追加できます</li>
    <li>依存関係の追加・削除: 対象タスクをドラッグし、依存するタスクの上にドロップします</li>
    <li>タスクの色: 最上位は黒、それ以下は青色です</li>
    </ul>

    <p>
        by Reiko Mori
    </p>
</body>
