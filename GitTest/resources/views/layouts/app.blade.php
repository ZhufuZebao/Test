<!doctype html>

<html lang="ja">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link rel="shortcut icon" href="{{ url('/') }}/favicon.ico" />

        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/style.css') }}" type="text/css" media="only screen and (max-width:480px)">
        <link rel="stylesheet" href="{{ asset('/css/style.css') }}" type="text/css" media="only screen and (min-width:481px) and (max-width:800px)">
        <link rel="stylesheet" href="{{ asset('/css/style_add.css') }}">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

        @yield('header')

    </head>

    <body>
        <div class="wrapper {{ isset($bodyStyle) ? $bodyStyle : 'dashboard' }}" id="top">

            <!--nav-wrapper-->
            <div class="nav-wrapper">
                <div class="logo"><a href="{{ route('home') }}">{{ config('app.name') }}</a></div>

                <nav>
                    <ul>
                        <?php $routeName = Route::currentRouteName() ?>
                        <li class="{{ preg_match('/^home/',     $routeName) ? 'current' : ''}}"><a href="{{ route('home') }}">ダッシュボード</a></li>
                        <li class="{{ preg_match('/^schedule/', $routeName) ? 'current' : ''}}"><a href="{{ route('schedule.index') }}">スケジュール</a></li>
                        <li class="{{ preg_match('/^project/',  $routeName) ? 'current' : ''}}"><a href="{{ route('project.index') }}">案件</a></li>
                        <li class="{{ preg_match('/^friend/',   $routeName) ? 'current' : ''}}"><a href="{{ route('home') }}">仲間</a></li>
                        <li class="{{ preg_match('/^chat/',     $routeName) ? 'current' : ''}}"><a href="{{ route('chat.index') }}">チャット</a></li>
                        <li class="{{ preg_match('/^foobar/',   $routeName) ? 'current' : ''}}"><a href="{{ route('scheme.index') }}">工程</a></li>
                        <li class="{{ preg_match('/^report/',   $routeName) ? 'current' : ''}}"><a href="{{ route('report.index') }}">日報</a></li>
                        <li class="{{ preg_match('/^document/', $routeName) ? 'current' : ''}}"><a href="{{ route('document.index') }}">書類</a></li>
                        <li class="{{ preg_match('/^job/',      $routeName) ? 'current' : ''}}"><a href="{{ route('job.index') }}">求人</a></li>
                        <li class="{{ preg_match('/^camera/',   $routeName) ? 'current' : ''}}"><a href="{{ route('camera.index') }}">カメラ</a></li>
                        <li class="{{ preg_match('/^foobar/',   $routeName) ? 'current' : ''}}"><a href="{{ route('home') }}">教えて</a></li>
                        <li class="{{ preg_match('/^customer/', $routeName) ? 'current' : ''}}"><a href="{{ route('customer.index') }}">顧客</a></li>
                        <li class="{{ preg_match('/^foobar/',   $routeName) ? 'current' : ''}}"><a href="{{ route('home') }}time-card.html">タイムカード</a></li>
                        <li class="{{ preg_match('/^foobar/',   $routeName) ? 'current' : ''}}"><a href="{{ route('home') }}management.html">その他の管理</a></li>
                        <li class="{{ preg_match('/^foobar/',   $routeName) ? 'current' : ''}}"><a href="{{ route('home') }}setup.html">設定</a></li>
                        <li style="height:50px; text-align:center;">
                          @if (Auth::check())
                            <form action="{{ url('/logout') }}" method="post">
                              {{ csrf_field() }}
                              <button type="submit">ログアウト</button>
                            </form>
                          @else
                            <form action="{{ url('/login') }}" method="get">
                              <button type="submit">ログイン</button>
                            </form>
                          @endif
                        </li>
                    </ul>
                </nav>
            </div>
            <!--/nav-wrapper-->

            <!--container-->
            <div class="container clearfix">

                @if (Breadcrumbs::exists(Route::currentRouteName()))
                    {!! Breadcrumbs::render(); !!}
                @endif

                @yield('content')

            </div>

        <!-- IDにゆっくり移動 -->
        <script type="text/javascript">
         $(function(){
            $('a[href^="#"]').click(function(){
                var speed = 1000;
                var href= $(this).attr("href");
                var target = $(href == "#" || href == "" ? 'html' : href);
                var position = target.offset().top;
                $("html, body").animate({scrollTop:position}, speed, "swing");
                return false;
            });
         });
        </script>

        <footer>
            &copy; {{ date('Y') }} CONIT Inc.
        </footer>

        @yield('footer')
        @yield('footer2')
    </div>

    </body>

</html>
