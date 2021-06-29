
<!doctype html>

<html lang="ja">

<head>
	<title>SHOKU-nin</title>

	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<link rel="shortcut icon" href="favicon.ico" />

	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/style-s.css" type="text/css" media="only screen and (max-width:480px)">
	<link rel="stylesheet" href="css/style-m.css" type="text/css" media="only screen and (min-width:481px) and (max-width:800px)">
</head>

<body>
<div class="wrapper low report" id="top">

	<!--nav-wrapper-->
	<div class="nav-wrapper">
		<div class="logo"><a href="index.html">SHOKU-nin</a></div>

		<nav>
			<ul>
				<li><a href="index.html">ダッシュボード</a></li>
				<li><a href="schedule.html">スケジュール</a></li>
				<li><a href="project.html">案件</a></li>
				<li><a href="friend.html">仲間</a></li>
				<li><a href="chat.html">チャット</a></li>
				<li><a href="process.html">工程</a></li>
				<li class="current"><a href="report.html">日報</a></li>
				<li><a href="document.html">書類</a></li>
				<li><a href="job-offer.html">求人</a></li>
				<li><a href="camera.html">カメラ</a></li>
				<li><a href="question.html">教えて</a></li>
				<li><a href="customer.html">顧客</a></li>
				<li><a href="time-card.html">タイムカード</a></li>
				<li><a href="management.html">その他の管理</a></li>
				<li><a href="setup.html">設定</a></li>
			</ul>
		</nav>
	</div>
	<!--/nav-wrapper-->

	<!--container-->
	<div class="container clearfix">
		<header>
			<h1><a href="report.html">日報</a></h1>

			<ul class="header-nav friend">
				<li class="current"><a href="report.html">一覧</a></li>
				<li><a href="report-detail.html">詳細</a></li>
				<li><a href="report-new.html">新規</a></li>
			</ul>

			<div class="title-wrap">

				<h2>日報一覧</h2>

				<div class="button-s">検索</div>

				<dl class="header-friend-serch clearfix">
					<dt>フリーワード</dt>
					<dd><input tupe="text"></dd>
				</dl>					
			</div>
		</header>

		<!--report-wrapper-->
		<section class="report-wrapper">
			<ul class="report-serch clearfix">
				<li class="report-date">
					日報作成
					<span class="button-s sort"></span>
				</li>
				<li class="report-project-no">
					案件No
					<span class="button-s sort"></span>
				</li>
				<li class="report-project-name">
					案件名
					<span class="button-s sort"></span>
				</li>
				<li class="report-author">
					作成者
					<span class="button-s sort"></span>
				</li>
				<li class="report-work-content">
					作業内容
					<span class="button-s sort"></span>
				</li>
				<li class="report-work-place">
					作業場所
					<span class="button-s sort"></span>
				</li>
				<li class="report-note">
					備考
					<span class="button-s sort"></span>
				</li>
			</ul>

			<ul>
				<li class="clearfix">
					<a href="report-detail.html" class="clearfix">
						<span class="report-date">2018-10-01 10:31:54</span>
						<span class="report-project-no">123456789</span>
						<span class="report-project-name">〇〇邸</span>
						<span class="report-author">ＸＸ設備</span>
						<span class="report-work-content">基礎施工</span>
						<span class="report-work-place">東京都千代田区神田三崎町</span>
						<span class="report-note">ああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああああ</span>
					</a>
				</li>
			</ul>
		</section>
		<!--/report-wrapper-->
	</div>
	<!--/container-->

	<!--footer-->
	<footer>
		&copy; 2017 SHOKU-nin Inc.
	</footer>
	<!--/footer-->

	<div class="top"><a href="#top"></a></div>
</div>

</body>


<!-- IDにゆっくり移動 -->
<script type="text/javascript">
$(function(){
	$('a[href^=#]').click(function(){
		var speed = 1000;
		var href= $(this).attr("href");
		var target = $(href == "#" || href == "" ? 'html' : href);
		var position = target.offset().top;
		$("html, body").animate({scrollTop:position}, speed, "swing");
		return false;
	});
});
</script>

<!-- モーダル -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script type="text/javascript">
$(function(){
	$('.btns').click(function(){
		wn = '.' + $(this).attr('date-tgt');
		var mW = $(wn).find('.modalBody').innerWidth() / 2;
		var mH = $(wn).find('.modalBody').innerHeight() / 2;
		$(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
		$(wn).fadeIn(100);
	});
	$('.modal-close,.modalBK').click(function(){
		$(wn).fadeOut(100);
	});
});
</script>

</html>
