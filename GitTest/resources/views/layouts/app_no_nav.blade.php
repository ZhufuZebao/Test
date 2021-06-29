<!doctype html>

<html lang="ja">

    <head>
        <title>{{ config('app.name') }}</title>

        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link rel="shortcut icon" href="{{ url('/') }}/favicon.ico" type="image/x-icon" />

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

        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="{{ asset('/css/style.css') }}?id={{str_random(10)}}">
        <link rel="stylesheet" href="{{ asset('/css/style_add.css') }}?id={{str_random(10)}}">

        @yield('header')

    </head>

    <body>
        <div class="wrapper" id="top">

            @yield('content')

        </div>
    </body>

</html>
