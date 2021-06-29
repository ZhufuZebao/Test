@php
$bodyStyle = 'low camera';
@endphp
@extends('layouts.app')

@section('header')
<title>webカメラ</title>
<link href="{{ asset('/skyway/bootstrap.css') }}" rel="stylesheet">
<link href="{{ asset('/skyway/bootstrap-basic.css') }}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
.container {
    width: calc(100vw - 85px);
    height:100%;
    position: absolute;
    left: 85px;
    top: 0;
}
</style>

@endsection('header')
<?php /*
<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <title>ビデオチャット</title>
  <link href="{{ asset('/css/style.css') }}" rel="stylesheet">
  <link href="{{ asset('/skyway/bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('/skyway/bootstrap-basic.css') }}" rel="stylesheet">
</head>
<body>
*/ ?>
@section('content')

        <header>
            <h1><a href="{{ url('/chat') }}">カメラ</a></h1>

            <div class="title-wrap">

            </div>
        </header>

<?php /*
        <!--project-wrapper-->
        <section class="camera-wrapper">
*/ ?>
  <div class="connection-alert-container"></div>

  <div class="wrapper">
    <div id="main">
      <div class="container-fluid video-wrapper">
        <ul class="video-list">
<?php /*
            <li data-peer-id="" data-id="" data-peer-share="false">
                <div class="tile">
                    <video class="movie" autoplay="autoplay" id=""></video>
                    <div class="tl-share">
                        <img src="{{ asset('/skyway/icon-share.png') }}">
                    </div>
                    <div class="tl-label">
                        <div class="tl-label-inner" style="">
                            <span class="user-name"></span>
                            <div class="tl-zoom">
                                <span><img src="{{ asset('/skyway/icon-zoomin.png') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
*/ ?>
        </ul>
        <div class="sub-area">
          <div class="btn btn-down"><img src="{{ asset('/skyway/icon-down.png') }}"></div>
          <div class="btn btn-zoomout tl-zoom" data-peer-id=""><img src="{{ asset('/skyway/icon-zoomout.png') }}"></div>
        </div>
      </div>
    </div>

    <div class="footer">
      <div class="container-fluid">
        <div class="row">
          <div id="user-information" class="col-xs-12 col-sm-6 pull-left">
            <div class="row information-inner">
              <div class="col-xs-4 col-sm-5 col-md-6 inner-left">
                <div class="pull-left user-photo">
                  <div class="icon-share"><img src="{{ asset('/skyway/icon-share.png') }}" width="54" height="54"></div>
                  <div class="user-video">
                    <video class="movie" src="" loop="" poster="" autoplay="autoplay" style="width: 100%;" muted="true"></video>
                  </div>
                </div>
              </div>
              <div class="col-xs-8 col-sm-7 col-md-6">
                <div class="user-detail row">
                  <div class="user-name col-sm-12">
                    <form name="changename" id="input-name" class="input-group">
                      <span class="glyphicon glyphicon-pencil input-group-addon" id="user-name-addon"></span>
                      <input name="userName" value="{{ $user->name }}" placeholder="Write Your Name" autocomplete="off" aria-describedby="user-name-addon" type="text">
                    </form>
                  </div>
                  <div class="video-controller col-sm-12">
                    <div class="row">
                      <div id="app-mic-control" class="btn-ctrl col-xs-3"><img type="button" class="btn" src="{{ asset('/skyway/icon-mic.png') }}"></div>
                      <div id="app-video-control" class="btn-ctrl col-xs-3"><img type="button" class="btn" src="{{ asset('/skyway/icon-video.png') }}"></div>
<?php /*
                      <div id="app-share-control" class="btn-ctrl col-xs-3"><img type="button" class="btn" src="{{ asset('/skyway/icon-share.png') }}"></div>
                      <div id="app-invite" class="btn-ctrl col-xs-3"><img type="button" class="btn" src="{{ asset('/skyway/icon-invite.png') }}"></div>
*/ ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xs-12 col-sm-6 pull-right" id="chat">

          <div id="selectors">
            <div style="visibility : hidden;">
              <label for="audioSource">Audio: </label><select id="audioSource"></select>
              <label for="videoSource">Video: </label><select id="videoSource"></select>
            </div>
            <form name="room_form" id="room_form">
                {{ csrf_field() }}
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
            </form>
          </div>

            <div class="chat-area col-xs-12">
              <div class="row chat-list-wrapper">
                <div class="chat-list col-xs-12"></div>
              </div>
              <div class="chat-message">
                <div class="row">
                  <div class="col-xs-12 input-message">
                    <form name="message" id="message">
                      <textarea id="send-message" name="message" placeholder="メッセージを入力するとチャットが出来ます！"></textarea>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="app-permissions-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span class="close-circle" aria-hidden="true">×</span><span class="sr-only">Close</span></button>
          <h4 class="modal-title" id="app-permissions-title">デバイス許可設定を行って使いやすくする</h4>
        </div>
        <div class="modal-body">
          <ol>
            <li> 「選択したデバイスを共有」の横にある<span class="glyphicon glyphicon-chevron-down"></span>をクリックする</li>
            <li>「常に共有」を選択する</li><br>
            <div class="guide-screenshot"><img src="{{ asset('/skyway/permissions_jp.png') }}"></div><br>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="app-maxuser-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span class="close-circle" aria-hidden="true">×</span><span class="sr-only">Close</span></button>
          <h4 class="modal-title" id="app-maxuser-title">ルームの最大人数に達成しました</h4>
        </div>
        <div class="modal-body">
          １つのルームには最大8人までしか参加できません。
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="app-gum-error-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="app-gum-error-title" data-backdrop="static">カメラの取得に失敗しました。</h4>
        </div>
        <div class="modal-body">
          カメラの設定と権限を確認してから<a class="guide-reload-btn" href="#">ページを再度読み込み<span class="glyphicon glyphicon-refresh"></span>してください。</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="app-unsupported-browser-modal" data-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="app-unsupported-browser-title">ブラウザが対応していません</h4>
        </div>
        <div class="modal-body">
          このブラウザではビデオチャットを利用できません。<a href="https://www.google.com/chrome/browser/">Chrome</a>又は<a href="http://www.mozilla.jp/">Firefox</a>を利用してアクセスしてください。
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="app-unsupported-device-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="app-unsupported-device-title">端末が対応していません</h4>
        </div>
        <div class="modal-body">
          iOS端末ではビデオチャットを利用出来ません。Windows、OSX、又はAndroid端末を利用してアクセスしてください。
        </div>
      </div>
    </div>
  </div>

  <div class="permission-dummy" data-toggle="popover" data-placement="bottom" title="" data-content="「選択したデバイスを共有」の右の▼をクリックし「常に共有」を選択すると、次回からこのメッセージは表示されません。" data-original-title="次回からこのメッセージを表示しない">
  </div>

  <!--
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.webrtc.ecl.ntt.com/skyway-latest.js"></script>
  -->
  <script src="{{ asset('/skyway/ZeroClipboard.js') }}"></script>
  <script src="{{ asset('/skyway/jquery.js') }}"></script>
  <script src="{{ asset('/skyway/eventemitter2.js') }}"></script>
  <script src="{{ asset('/skyway/bootstrap.js') }}"></script>
  <script src="{{ asset('/skyway/ua-parser.js') }}"></script>
  <script src="{{ asset('/skyway/adapter-3.js') }}"></script>
  <script type="text/javascript" src="{{ asset('/skyway/skyway-latest.js') }}"></script>
  <script src="{{ asset('/skyway/screenshare-1.js') }}"></script>
  <script src="{{ asset('/skyway/roomui.js') }}"></script>
  <script src="{{ asset('/skyway/main.js') }}"></script>

<?php /*
</section>
*/ ?>

@endsection('content')
<?php /*
</body>
</html>
*/ ?>