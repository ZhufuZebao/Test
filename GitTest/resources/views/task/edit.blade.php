@php
$bodyStyle = 'low process';
@endphp

@extends('layouts.debug')

@section('header')
<title>{{ config('app.name') }}</title>
<link rel="stylesheet" href="{{ url('/') }}/css/bootstrap.min.css" />
<link rel="stylesheet" href="{{ url('/') }}/css/dhtmlxgantt.css?v=5.2.0">
<script src="{{ url('/') }}/js/dhtmlxgantt.js?v=5.2.0"></script>
<script src="{{ url('/') }}/js/dhtmlxgantt-locale-ja.js?v=5.2.0"></script>
<script src="{{ url('/') }}/js/html2canvas.js"></script>
@endsection

@section('content')

	<header>
		<h1>
            <a href="process.html">工程</a>
        </h1>

		<ul class="header-nav process">
			<li>
                <a href="{{ route('scheme.gantt',['id'=>$model->id]) }}">
                    計画工程表
                </a>
            </li>
			<li class="current">
                <a href="{{ route('task.gantt',['id'=>$model->id]) }}">
                    実施工程表
                </a>
            </li>
			<li>
                <a href="{{ route('project.gantt',['id'=>$model->id]) }}">
                    両方
                </a>
            </li>
		</ul>

		<div class="title-wrap">
			<h2>
                {{ sprintf('%08d', $model->project_id) }}
                {{ $model->name }}
            </h2>
			<div class="button-s">
                <a href="{{ route('project.index') }}">案件一覧へ</a>
            </div>
		</div>
	</header>

	<section class="process-wrapper">
        <div id="gantt_here" style='width:100%; height:80%;'></div>
    </section>
    <form class="gantt_control">
	    <input type="radio" id="scale1" class="gantt_radio" name="gantt_scale" value="1"/><label for="scale1">時</label>
	    <input type="radio" id="scale2" class="gantt_radio" name="gantt_scale" value="2" checked/><label for="scale2">日</label>
	    <input type="radio" id="scale3" class="gantt_radio" name="gantt_scale" value="3"/><label for="scale3">週</label>
	    <input type="radio" id="scale4" class="gantt_radio" name="gantt_scale" value="4"/><label for="scale4"></i>月</label>
	    <input type="radio" id="scale5" class="gantt_radio" name="gantt_scale" value="5"/><label for="scale5">四半期</label>
	    <input type="radio" id="scale6" class="gantt_radio" name="gantt_scale" value="6"/><label for="scale6">年</label>
    </form>

    <!-- 一時保存してから本当に保存するのが理想。でも現状では各タスクは即時に保存される。 -->
    <!-- <button id="batch-update" class="btn btn-warning">保存[TODO]</button> -->
    <!-- <div id="ajax-response"></div> -->

<script>

 var func = function (e) {
	 e = e || window.event;
	 var el = e.target || e.srcElement;
	 var value = el.value;
	 gantt.config = setScaleConfig(value);// see also dhtmlxgantt-locale-ja.js
     gantt.render();
 };
 var els = document.getElementsByName("gantt_scale");
 for (var i = 0; i < els.length; i++) {
	 els[i].onclick = func;
 }

 var dels = []; // tasks to be deleted
 gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";

 gantt.config.order_branch = true;       // allow reordering tasks within the whole gantt
 gantt.config.order_branch_free = false; // allow moving inside other task or not

 gantt.config.readonly = false;
 gantt.config.columns.push({name: 'add', align: 'right'});

 gantt.init("gantt_here");
 gantt.load("{{ route('task.json',['id'=>$model->id]) }}", "json");

 gantt.attachEvent("onAfterTaskDelete", function(id,item){
     targets[item.$index] = item;
 });

 gantt.attachEvent("onAfterTaskAdd", function(id,task){
     if(0 == task.parent)
         gantt.moveTask(id, 0); // move to top
 });

 gantt.attachEvent("onRowDragEnd", function(id,target){
     var task = gantt.getTask(id);
     if (task.$index == 0){
         prevId = 0; // No previous task
     }
     else{
         if (isNaN(target+1)){
             prevId = gantt.getTaskByIndex(task.$index - 1).id;
             //nextId = 0; // No next task
         }
         else {
             prevId = gantt.getTaskByIndex(task.$index - 1).id;
             //nextId = gantt.getTaskByIndex(task.$index + 1).id;
         }
     }
     $.ajax({
         url: "{{ route('task.api.move',['id'=>$model->id]) }}",
         type: 'post',
         headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
         data: {'itemId': task.id, 'prevId': prevId, /*'nextId': nextId*/},
         success: function(response) {
             $('#ajax-response').html(response);
             console.log(response);
         }
     });
 });

 // configure API
 var dp = new gantt.dataProcessor('{{ url('/') }}/task/{{ $model->id }}/api');
 dp.enableDebug(true);
 dp.init(gantt);

 dp.setTransactionMode({
     mode:"REST",
     headers:{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
     payload:{ 'project_id'  : "{{ $model->id }}"   }
 }, false);

</script>

@endsection
