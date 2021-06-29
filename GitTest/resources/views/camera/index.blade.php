@php
$bodyStyle = 'low camera';
@endphp
@extends('layouts.app')

@section('header')
<title>webカメラ</title>
  <link rel="stylesheet" href="{{ asset('/css/camera.css') }}">
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.webrtc.ecl.ntt.com/skyway-latest.js"></script>
  <script type="text/javascript" src="{{ asset('/js/script.js') }}"></script>
@endsection('header')

@section('content')

<?php /*
<div class="wrapper low camera" id="top">

    <!--nav-wrapper-->
    <div class="nav-wrapper">
        <div class="logo"><a href="{{ url('/') }}">SHOKU-nin</a></div>

        <nav>
            <ul>
                <li><a href="index.html">ダッシュボード</a></li>
                <li><a href="{{ url('/schedule/ref/0') }}">スケジュール</a></li>
                <li><a href="project.html">案件</a></li>
                <li><a href="friend.html">仲間</a></li>
                <li class="current"><a href="{{ url('/chat') }}">チャット</a></li>
                <li><a href="{{ url('/step') }}">工程</a></li>
                <li><a href="report.html">日報</a></li>
                <li><a href="{{ url('/document') }}">書類</a></li>
                <li><a href="{{ url('/search') }}">求人</a></li>
                <li><a href="{{ url('/camera') }}">カメラ</a></li>
                <li><a href="{{ url('/faq') }}">教えて</a></li>
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
    */ ?>
        <header>
            <h1><a href="{{ url('/chat') }}">カメラ</a></h1>

            <div class="title-wrap">

            </div>
        </header>

        <!--project-wrapper-->
        <section class="camera-wrapper">

    <div class="pure-g">

<?php if (isset($_SERVER['HTTP_USER_AGENT']) && ((strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ||
        (strstr($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false && strstr($_SERVER['HTTP_USER_AGENT'], 'rv:11.0') !== false))) { ?>
        ※ IE（Internet Explore）では動きません。
<?php } else { ?>

        <!-- Video area -->
        <div class="pure-u-1" id="video-container">
            <div id="their-videos"></div>
            <div>
                <label id="my-label"></label>
                <video id="my-video" muted="true" autoplay></video>
            </div>
        </div>

        <!-- Steps -->
        <div class="inputBox">

            <!-- Get local audio/video stream -->
            <div id="step1">
                <p><small>画面の上部にある「許可」をクリックすると、ウェブカメラとマイクからアクセスできるようになります。
<?php if (isset($_SERVER['HTTP_USER_AGENT']) && ((strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ||
        (strstr($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false && strstr($_SERVER['HTTP_USER_AGENT'], 'rv:11.0') !== false))) { ?>
                （IEでは動きません）
<?php } ?>
                </small></p>
                <div id="step1-error">
                    <p>ウェブカメラとマイクにアクセスできませんでした。</p>
                    <a href="#" class="pure-button pure-button-error" id="step1-retry">再試行</a>
                </div>
            </div>

            <!-- Make calls to others -->
            <div id="step2">
                <form id="make-call" class="pure-form">

            @if ($group_id == '')
                    <label for="join-room">ルーム名:</label>
                    <!--
                        <input type="text" placeholder="Join room..." id="join-room">
                    -->
                    <select id="join-room">
                        <option value=""></option>
                        @if (isset($room) && is_array($room) && !empty($room))
                            @foreach ($room as $row)
                        <option value="{{ $row['room_name'] }}">{{ $row['name'] }}</option>
                            @endforeach
                        @endif
                    </select>
            @else
                    <select id="join-room" style="visibility : hidden;">
                        <option value="group_{{ $group_id }}"></option>
                    </select>
            @endif

                    <button class="pure-button pure-button-success" type="submit">接続</button>

                </form>
<?php /*
                <p><small><strong>注意：</strong>同じルーム名を使用した場合、知らない人と接続する可能性があります。</small></p>
*/ ?>
            </div>
            <p style="visibility : hidden;">Your id: <span id="my-id">...</span></p>

            <!-- Call in progress -->
            <div id="step3">
            <?php /*
                <p>接続しているルーム名：<span id="room-id">...</span></p>
            */ ?>
                <p><a href="#" class="pure-button pure-button-error" id="end-call">終了</a></p>
            </div>
        </div>

        <div style="visibility : hidden;">
          <label for="audioSource">Audio: </label><select id="audioSource"></select>
          <label for="videoSource">Video: </label><select id="videoSource"></select>
        </div>

<?php } ?>
    </div>

        </section>
        <!--/project-wrapper-->

@endsection('content')
<?php /*
    </div>
    <!--/container-->

    <!--footer-->
    <footer>
        &copy; 2017 SHOKU-nin Inc.
    </footer>
    <!--/footer-->

    <div class="top"><a href="#top"></a></div>
</div>

<!--/container-->
*/ ?>
<?php /*
<script>
@if ($group_id != '')
    e.preventDefault();
    // Initiate a call!
    const roomName = 'group_{{ $group_id }}';
    room = peer.joinRoom('mesh_video_' + roomName, {mode: 'mesh', stream: localStream});

    var obj = document.getElementById('join-room');
    var idx = obj.selectedIndex;       //インデックス番号を取得
    var val = obj.options[idx].value;  //value値を取得
    var txt  = obj.options[idx].text;  //ラベルを取得

    $('#room-id').text(txt);
    step3(room);
@endif
</script>
*/ ?>

