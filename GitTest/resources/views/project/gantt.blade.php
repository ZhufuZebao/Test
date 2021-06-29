@php
$bodyStyle = 'low process';
@endphp

@extends('layouts.app')

@section('header')

    <title>{{ $model->name }}</title>
    <link rel="stylesheet" href="{{ url('/')}}/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ url('/') }}/css/dhtmlxgantt.css?v=5.2.0">
    <script src="{{ url('/') }}/js/dhtmlxgantt.js?v=5.2.0"></script>
    <script src="{{ url('/') }}/js/dhtmlxgantt-locale-ja.js?v=5.2.0"></script>
    <script src="{{ url('/') }}/js/html2canvas.js"></script>
    <style type="text/css">
     .baseline {
         display:none;
     }
    </style>

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
			<li>
                <a href="{{ route('task.gantt',['id'=>$model->id]) }}">
                    実施工程表
                </a>
            </li>
			<li class="current">
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

	<section class="process-wrapper one-year">
        <div id="gantt_scheme" style='width:100%; height:80%;'></div>

        <!-- 画面の下か上に配置したかったけど、どうしても画面からはみ出てしまうので、ここに仮置きしてます by mori -->
        <form class="gantt_control">
	        <input type="radio" id="scale1" class="gantt_radio" name="gantt_scale" value="1"/><label for="scale1">時</label>
	        <input type="radio" id="scale2" class="gantt_radio" name="gantt_scale" value="2" checked/><label for="scale2">日</label>
	        <input type="radio" id="scale3" class="gantt_radio" name="gantt_scale" value="3"/><label for="scale3">週</label>
	        <input type="radio" id="scale4" class="gantt_radio" name="gantt_scale" value="4"/><label for="scale4">月</label>
	        <input type="radio" id="scale5" class="gantt_radio" name="gantt_scale" value="5"/><label for="scale5">四半期</label>
	        <input type="radio" id="scale6" class="gantt_radio" name="gantt_scale" value="6"/><label for="scale6">年</label>
        </form>

    </section>


	<section class="process-wrapper three-week">
        <div id="gantt_task" style='width:100%; height:80%;'></div>
    </section>

@endsection

@section('footer')

    <script>
	 var func = function (e) {
		 e = e || window.event;
		 var el = e.target || e.srcElement;
		 var value = el.value;
		 config = setScaleConfig(value);// see also dhtmlxgantt-locale-ja.js
         g1.config = config;
         g2.config = config;
         g1.render();
		 g2.render();
	 };
	 var els = document.getElementsByName("gantt_scale");
	 for (var i = 0; i < els.length; i++) {
		 els[i].onclick = func;
	 }

     var g1 = Gantt.getGanttInstance();
     g1.config = gantt.config;
     g1.config.readonly = true;
     g1.init("gantt_scheme");
     g1.load("{{ route('scheme.json',['id'=>$model->id]) }}", "json");

     @if( 0 == $model->schemes()->exists() )
     g1.message({
         type:'warning',
         text:"計画工程表はまだありません",
         expire:30000 // 30 sec
     });
     @endif

     var g2 = gantt;
     g2.config = gantt.config;
     g2.config.readonly = true;
     g2.init("gantt_task");
     g2.load("{{ route('task.json',['id'=>$model->id]) }}", "json");

     @if( 0 == $model->tasks()->exists() )
     g2.message({
         type:'warning',
         text:"実施工程表はまだありません",
         expire:30000 // 30 sec
     });
     @endif

     function outputPdf()
     {
         var div = document.getElementById('gantt_scheme');

         html2canvas(div).then(canvas => {
             document.body.appendChild(canvas);
             var base64 = canvas.toDataURL("image/png").replace("data:image/png;base64,", "");

             form = document.getElementById('form-generate-pdf');
             elem = document.getElementById('input-hidden-base64');
             elem.value = base64;
             form.submit();
         });
     }
    </script>

@endsection
