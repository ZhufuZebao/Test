<!doctype html>

<html lang="ja">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link rel="shortcut icon" href="{{ url('/') }}/favicon.ico" type="image/x-icon" />

        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes"/>

        <meta name="application-name" content="SITE">
        <link rel="apple-touch-icon-precomposed" href="{{ url('/') }}/touch-icon-iphone-retina.png" >
        <link rel="apple-touch-icon" href="{{ url('/') }}/touch-icon-iphone-retina.png" />
        <link rel="apple-touch-icon" sizes="152x152" href="{{ url('/') }}/touch-icon-ipad.png" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/') }}/touch-icon-iphone-retina.png" />
        <link rel="apple-touch-icon" sizes="167x167" href="{{ url('/') }}/touch-icon-ipad-retina.png" />
        <link rel="apple-touch-icon-precomposed" sizes="152x152" href="{{ url('/') }}/touch-icon-ipad.png" />
        <link rel="apple-touch-icon-precomposed" sizes="180x180" href="{{ url('/') }}/touch-icon-iphone-retina.png" />
        <link rel="apple-touch-icon-precomposed" sizes="167x167" href="{{ url('/') }}/touch-icon-ipad-retina.png" />

        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">

        <link rel="stylesheet" href="{{ asset('/css/style.css') }}?id={{str_random(10)}}">
        <link rel="stylesheet" href="{{ asset('/css/style_sp.css') }}?id={{str_random(10)}}" type="text/css" media="only screen and (max-width:480px)">
        <link rel="stylesheet" href="{{ asset('/css/style_sp.css') }}?id={{str_random(10)}}" type="text/css" media="only screen and (min-width:481px) and (max-width:1024px)">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.0/lib/theme-chalk/index.css">
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/firebase@7.24.0/firebase-app.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/firebase@7.24.0/firebase-messaging.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios@0.21.1/dist/axios.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.20/lodash.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue-router@3.5.1/dist/vue-router.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.0/lib/index.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.0/lib/umd/locale/ja.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue-resource@1.5.1/dist/vue-resource.min.js"></script>
        <script src="{{ asset(mix('js/app.js')) }}" defer></script>
        {{-- disable go back --}}
        <script type="text/javascript">
          ELEMENT.locale(ELEMENT.lang.ja);
          window.addEventListener('popstate', function () {
            history.pushState(null, null, document.URL);
          });
        </script>

        @yield('header')

    </head>

    <body>
        <!--container-->
        <div class="wrapper low" id="top">
            @yield('content')
            <footer>
                &copy; {{ date('Y') }} CONIT Inc.
            </footer>
        </div>

        @yield('footer')
        @yield('footer2')
    </body>

</html>
