/*
@license

dhtmlxGantt v.5.2.0 Professional Evaluation
This software is covered by DHTMLX Evaluation License. Contact sales@dhtmlx.com to get Commercial or Enterprise license. Usage without proper license is prohibited.

(c) Dinamenta, UAB.

*/
Gantt.plugin(function(e){!function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/codebase/",n(n.s=170)}({170:function(t,n){e.locale={date:{month_full:["1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月"],month_short:["1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月"],day_full:["日曜日","月曜日","火曜日","水曜日","木曜日","金曜日","土曜日"],day_short:["日","月","火","水","木","金","土"]},labels:{new_task:"新しい仕事",dhx_cal_today_button:"今日",day_tab:"日",week_tab:"週",month_tab:"月",new_event:"新しいイベント",icon_save:"保存",icon_cancel:"キャンセル",icon_details:"詳細",icon_edit:"編集",icon_delete:"削除",confirm_closing:"",confirm_deleting:"イベントは完全に削除されます。よろしいですか？",section_description:"説明",section_time:"期間",section_type:"種類",column_wbs:"WBS",column_text:"タスク名",column_start_date:"開始日",column_end_date:"終了日",column_duration:"日数",column_add:"",link:"リンク",confirm_link_deleting:"削除されます",link_start:" (開始)",link_end:" (終了)",type_task:"タスク",type_project:"プロジェクト",type_milestone:"マイルストーン",minutes:"分",hours:"時間",days:"日",weeks:"週",months:"か月",years:"年",message_ok:"OK",message_cancel:"キャンセル"}}}})});

	gantt.config.work_time = true;
	gantt.setWorkTime({hours: [8, 12, 13, 17]});//global working hours. 8:00-12:00, 13:00-17:00

	gantt.config.scale_unit = "month";
	gantt.config.date_scale = "%F";
	gantt.config.min_column_width = 20;
	gantt.config.duration_unit = "day";
	gantt.config.scale_height = 20 * 3;
    gantt.config.fit_tasks = true; // re-render the scale each time a task doesn't fit into the existing scale interval
    gantt.config.work_time = false;  // removes non-working time from calculations

var dayStyleTemplate = function(date){
    var wday = date.getDay();
    if(0 == wday)
        return "holiday sunday";

    if(6 == wday)
        return "holiday saturday";

    return "";
};

function quarterLabel(date) {
	var month = date.getMonth();
	var q_num;

	if (month >= 9) {
		q_num = 4;
	} else if (month >= 6) {
		q_num = 3;
	} else if (month >= 3) {
		q_num = 2;
	} else {
		q_num = 1;
	}

	return "Q" + q_num;
}

    gantt.config.columns=[
         {name:"text",       align: "left", width: '*', tree: true },
         {name:"start_date", align: "left", width: 80 },
         {name:"end_date",   align: "left", width: 72, label: "終了日" },
         {name:"duration",   align: "right", width: 32 }
     ];

	gantt.config.subscales = [
		{unit: "day",  step: 1, date: "%d", css:dayStyleTemplate},
		{unit: "day",  step: 1, date: "%D", css:dayStyleTemplate},
	];

 gantt.config.xml_date  = "%Y-%m-%d";
 gantt.config.date_grid = "%Y-%m-%d";

	gantt.templates.task_cell_class = function (task, date) {
        return dayStyleTemplate(date);
	};

	// adding baseline display
	gantt.config.task_height = 16;
	gantt.config.row_height  = 36;
	gantt.addTaskLayer(function draw_planned(task) {
		if (task.planned_start && task.planned_end) {
			var sizes = gantt.getTaskPosition(task, task.planned_start, task.planned_end);
			var el = document.createElement('div');
			el.className   = 'baseline';
			el.style.left  = sizes.left  + 'px';
			el.style.width = sizes.width + 'px';
			el.style.height = '2px';
			el.style.top = sizes.top + gantt.config.task_height + 12 + 'px';
            el.style.position = 'absolute';
			return el;
		}
		return false;
	});

	gantt.attachEvent("onTaskLoading", function (task) {
		task.planned_start = gantt.date.parseDate(task.planned_start, "xml_date");
		task.planned_end = gantt.date.parseDate(task.planned_end, "xml_date");
		return true;
	});

     // scale by (Day | Week | Month | Year)
	 function setScaleConfig(value) {
		 gantt.config.subscales = [];

		 switch (value) {
			 case "1":
                 // one cell = one hour
	             gantt.config.scale_unit = "day";
	             gantt.config.date_scale = "%Y/%m/%d(%D)";
	             gantt.config.min_column_width = 20;
	             gantt.config.duration_unit = "day";
	             gantt.config.scale_height = 20 * 3;
				 gantt.config.subscales = [
	                 {unit: "hour", step: 2, date: "%G",css:dayStyleTemplate},
				 ];
				 break;
			 case "2":
                 // one cell = one day
	             gantt.config.scale_unit = "month";
	             gantt.config.date_scale = "%F";
	             gantt.config.min_column_width = 20;
	             gantt.config.duration_unit = "day";
	             gantt.config.scale_height = 20 * 3;
				 gantt.config.subscales = [
		             {unit: "day",  step: 1, date: "%d", css:dayStyleTemplate},
		             {unit: "day",  step: 1, date: "%D", css:dayStyleTemplate},
				 ];
				 break;
			 case "3":
                 // one cell = one week
				 var weekScaleTemplate = function (date) {
					 var dateToStr = gantt.date.date_to_str("%m/%d");
					 var startDate = gantt.date.week_start(new Date(date));
					 var endDate = gantt.date.add(gantt.date.add(startDate, 1, "week"), -1, "day");
					 return dateToStr(startDate) + " - " + dateToStr(endDate);
				 };

				 gantt.config.scale_unit = "week";
				 gantt.config.step = 1;
				 gantt.templates.date_scale = weekScaleTemplate;
				 gantt.config.subscales = [
					 {unit: "year", step: 1, date: "%Y"}
				 ];
				 gantt.config.scale_height = 50;
				 break;
			 case "4":
                 // one cell = one month
				 gantt.config.scale_unit = "year";
	             gantt.config.date_scale = "%Y";
				 //gantt.config.step = 1;
				 gantt.config.min_column_width = 25;
				 gantt.config.subscales = [
					 {unit: "month", step: 1, date: "%M"},
                 ];
				 gantt.config.scale_height = 90;
				 gantt.templates.date_scale = null;
				 break;
			 case "5":
                 // one cell = one quarter
				 gantt.config.scale_unit = "year";
				 gantt.config.step = 1;
				 gantt.config.date_scale = "%Y";
				 gantt.config.min_column_width = 25;
				 gantt.config.subscales = [
                     {unit: "quarter", step: 1, template: quarterLabel},
                 ];
				 gantt.config.scale_height = 90;
				 gantt.templates.date_scale = null;
				 break;
			 case "6":
                 // one cell = one year
				 gantt.config.scale_unit = "year";
				 gantt.config.step = 1;
				 gantt.config.date_scale = "%Y";
				 gantt.config.min_column_width = 25;
				 gantt.config.subscales = [
                 ];
				 gantt.config.scale_height = 45;
				 gantt.templates.date_scale = null;
				 break;
		 }
         return gantt.config;
	 }

// SAMPLE //
//makes a specific date a day-off
gantt.setWorkTime({date:new Date(2013,0,1), hours:false})
