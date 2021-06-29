<!doctype html>

<html lang="ja">

<head>
    <title>SHOKU-nin</title>

    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="shortcut icon" href="favicon.ico" />

    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">

    @yield('header')

</head>

<body>

    @yield('content')

</body>

@yield('footer')

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
<?php /*
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('header')
</head>

<body id="top">

<header>
    <div class="title"><a href="{{ url('/') }}">〇〇〇〇〇</a></div>

    <!-- top-menu -->
    <div id="top-menu"><div class="menu"><img src="{{ asset('images/menu-bar.png') }}" alt=""></div></div>

    <div class="login_logout">
      @if (Auth::check())
        <form action="{{ url('/logout') }}" method="post">
          {{ csrf_field() }}
          <button type="submit" class="btn btn-primary">ログアウト</button>
        </form>
      @else
        <form action="{{ url('/login') }}" method="get">
          <button type="submit" class="btn btn-primary">ログイン</button>
        </form>
      @endif
    </div>

    <div class="js-menu sb-right">
        <ul>
            <li><a href="{{ url('/user') }}">登録画面</a></li>
            <li><a href="{{ url('/schedule/ref/0') }}">スケジュール</a></li>
            <li><a href="{{ url('/evaluation') }}">評価</a></li>
            <li><a href="{{ url('/chat') }}">チャット</a></li>
            <li><a href="{{ url('/search') }}">求人検索</a></li>
            <li><a href="{{ url('/document') }}">提出書類管理</a></li>
            <li><a href="{{ url('/step') }}">工程管理</a></li>
            <li><a href="{{ url('/faq') }}">教えてサイト</a></li>
            <li><a href="{{ url('/camera') }}">webカメラ</a></li>
            <li><a href="{{ url('/history') }}">受注履歴</a></li>
            <li><a href="{{ url('/worker') }}">職人検索</a></li>
        </ul>
    </div>
    <!-- top-menu -->

</header>

@yield('content')

@yield('footer')

</body>

<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script type="text/javascript">
$(function() {
  var menu = $("div.js-menu");
  var search = $("div.js-search");
  menu.hide();
  search.hide();
  $("div.menu").on("click", {a: menu, b: search}, slide);
  $("div.search").on("click", {a: search, b: menu}, slide);
  function slide(event) {
    if (event.data.a.css("display") === "none") {
      event.data.a.slideDown(250);
      event.data.b.slideUp(250);
    } else {
      event.data.a.slideUp(250);
    }
  }
});
</script>

@yield('footer2')

</html>
*/ ?>