@extends('layouts.app')
<title>チャット</title>

@section('header')
    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

@endsection

@section('content')
<div class="container low chat news">
    <h1>チャット</h1>

    <ul class="chat-nav clearfix">
        <li><a href="{{ url('/chat') }}"></a></li>
    @if ($lastDirectId != null)
        <li><a href="{{ url('/chatstart/gid/'. $lastDirectId) }}"></a></li>
    @else
        <li><a href="{{ url('/chat') }}"></a></li>
    @endif
    @if ($lastGroupId != null)
        <li><a href="{{ url('/chatstart/gid/'. $lastGroupId) }}"></a></li>
    @else
        <li><a href="{{ url('/chat') }}"></a></li>
    @endif
        <li class="current"><a href="/chatnews"></a></li>
        <li><a href="{{ url('/chatsetting') }}"></a></li>
    </ul>

    <p>お知らせ</p>

@foreach ($news as $item)
    <p class="title">■{{ $item->title }}</p>
    <p class="content">{!! str_replace("\n", "<br>", $item->content) !!}</p>
@endforeach

</div>

@endsection
