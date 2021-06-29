<!doctype html>

<html lang="ja">

<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<link rel="shortcut icon" href="http://localhost/shokunin/favicon.ico" />

	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta content="black" name="apple-mobile-web-app-status-bar-style" />

	<meta content="telephone=no" name="format-detection" />


	{{--<link rel="stylesheet" href="{{ asset('/css/style.css') }}">--}}
	<link rel="stylesheet" href="{{ asset('/css/adminstyle.css') }}?id={{str_random(10)}}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.0/lib/theme-chalk/index.css">
	<!-- Scripts -->
	<script src="https://cdn.jsdelivr.net/npm/axios@0.21.1/dist/axios.min.js"></script>

	<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.20/lodash.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/vue-router@3.5.1/dist/vue-router.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.0/lib/index.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.0/lib/umd/locale/ja.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/vue-resource@1.5.1/dist/vue-resource.min.js"></script>

	<title>{{ config('app.name') }}</title>

</head>

<body>
<!--container-->
<div class="wrapper low" id="top">

	<div id="admin">
		<App/>
	</div>

	<footer>
		&copy; 2020 CONIT Inc.
	</footer>
</div>

<script src="{{ asset('js/jquery-3.3.1.min.js')}}"></script>
<script src="{{ asset(mix('js/admin.js')) }}"></script>

<script type="text/javascript">
	ELEMENT.locale(ELEMENT.lang.ja);
	window.addEventListener('popstate', function () {
		history.pushState(null, null, document.URL);
	});
	var phoneWidth =  parseInt(window.screen.width);
	var phoneScale = phoneWidth/640;
	var ua = navigator.userAgent;

	if (/Android (\d+\.\d+)/.test(ua)) {
		var version = parseFloat(RegExp.$1);
		// andriod 2.3
		if (version > 2.3) {
			document.write('<meta name="viewport" content="width=device-width,initial-scale=0.3,minimum-scale=0.3,maximum-scale=2.0,user-scalable=yes">');
			// andriod 2.3以上
		} else {
			document.write('<meta name="viewport" content="width=device-width,user-scalable=yes">');
		}
		// その他のシステム
	} else {
		document.write('<meta name="viewport" content="width=device-width, initial-scale=0.3,minimum-scale=0.3,maximum-scale =2.0,user-scalable=yes">');
	}

</script>
</body>

</html>
