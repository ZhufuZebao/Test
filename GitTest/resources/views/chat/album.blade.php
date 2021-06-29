@php
$bodyStyle = 'low chat';
@endphp
@extends('layouts.app')

@section('header')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="{{ asset('/css/chat.css') }}" rel="stylesheet">

    <!-- Fonts -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="//fonts.googleapis.com/css?family=Roboto:400,300" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

<script src="{{ asset('/js/chatFunction0.js') }}"></script>
@endsection('header')

@section('content')
        <header>
            <h1><a href="{{ url('/chat') }}">チャット</a></h1>

            <div class="title-wrap">

            @if ($groups->kind == 1)
                <div class="chatGroupName">{{ isset($chatGroupList[0]) ? $chatGroupList[0]->name : '' }}</div>
            @else
                <div class="chatGroupName">{{ $groups->name }}</div>
            @endif

                <div style="float:left; margin:10px 0 0 20px;">アルバム</div>

                <div style="float:left; display:none;">
                    <span class="chatMember">【オンライン・メンバー】</span>
                    <ul class="chatUsers">
                    </ul>
                </div>
            </div>
        </header>

        <!--friend-wrapper-->
        <section class="chat-wrapper">
            <ul class="chat-group-wrapper clearfix">
                <li>プロジェクト</li>
        @foreach($chatGroupList as $item)
            @if ($group_id == $item->group_id)
                <li><a href="{{ url('/chat/'. $item->group_id) }}">{{ $item->name }} ●</a></li>
            @else
                <li><a href="{{ url('/chat/'. $item->group_id) }}">{{ $item->name }}</a></li>
            @endif
        @endforeach
            </ul>

            <form id="input_form" name="input_form" method="POST" action="{{ url('/makealbum') }}">
                {{ csrf_field() }}
                <input id="csrfToken" type="hidden" name="csrf-token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="group_id" value="{{ $group_id }}" />
                <input type="hidden" name="task_id" value="" />

                <div class="album-deteil-wrap edit clearfix">

                    <dl class="clearfix">
                        <dt>アルバム名</dt>
                        <dd><input type="text" name="album_name" id="album_name" value=""></dd>
                    </dl>
<?php /*
                    <div class="clearfix"></div>
*/ ?>
                    <div class="button-wrap clearfix">
                        <div class="button-lower remark"><button type="submit" class="button-s submitBtn" id="btn-chat">新規アルバム作成</button></div>
                    </div>

                </div>

            </form>

            <div class="albumlist-deteil-wrap clearfix">
            @foreach($albums as $item)
                <div class="albums_name">
                    <div  class="album-name">
                        {{ $item->name }}
                    </div>
                    <div class="album-new-name" id="album-new-name{{ $item->id }}">
                        <form name="rename_form{{ $item->id }}" id="rename_form" method="POST" action="{{ url('/renamealbum') }}">
                            {{ csrf_field() }}
                            <input type="hidden" name="group_id" value="{{ $group_id }}" />
                            <input type="hidden" name="album_id" value="" />
                            <input type="hidden" name="base_album_name" id="base_album_name" value="" />
                            <input type="text" name="new_album_name" id="new_album_name" />
                            <button type="submit" class="button-s submitBtn">更新</button>
                        </form>
                    </div>
                    <div class="album-menu">
                        <a href="javascript:deleteAlbum({{ $item->id }}, '{{ $item->name }}');">アルバムを削除</a>
                    </div>
                    <div class="album-menu">
                        <a href="javascript:renameAlbum({{ $item->id }}, '{{ $item->name }}');">アルバム名を変更</a>
                    </div>
                    <div class="album-menu">
                        <form name="fileUpload" id="fileUpload" method="POST" action="{{ url('/addfilestoalbum') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <input type="hidden" name="group_id" value="{{ $group_id }}" />
                            <input type="hidden" name="user_id" value="{{ Auth::id() }}" />
                            <input type="hidden" name="album_id" value="{{ $item->id }}" />
                            <label for="upfile">
                                <p id="selectFile">写真を登録</p>
                                <input type="file" name="upfile" id="upfile" style="display:none;" onChange="$('#fileUpload').submit();" />
                            </label>
                        </form>
                    </div>
                </div>

                <div class="albums">
                @if (isset($item->files) && is_array($item->files))
                    @foreach($item->files as $file)
                    <?php $tmp = explode(".", $file); ?>
                    <a href="javascript:enlargeFile({{ $item->id }}, '{{ $item->name }}', '{{ $file }}');"><img src="{{ route('chat.albumImage',['gid'=>$group_id, 'albumid'=>$item->id, 'filename'=>$tmp[0], 'extension'=>$tmp[1]]) }}"></a>
                    @endforeach
                @endif
                </div>
            @endforeach
            </div>

            <form name="deleteAlbum" id="deleteAlbum" method="POST" action="{{ url('/deletealbum') }}">
                {{ csrf_field() }}
                <input type="hidden" name="group_id" value="{{ $group_id }}" />
                <input type="hidden" name="user_id" value="{{ Auth::id() }}" />
                <input type="hidden" name="album_id" value="" />
            </form>

        </section>
        <!--/friend-wrapper-->


<div class="modal openAL" id="openAL">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form id="photo_form" name="photo_form" method="POST" action="{{ url('/deletephoto') }}">
        {{ csrf_field() }}
        <input type="hidden" name="group_id" value="{{ $group_id }}" />
        <input type="hidden" name="album_id" value="" />
        <input type="hidden" name="file_name" value="" />

        <div class="modal-content clearfix">
            <div class="title">
                写真拡大
            </div>
            <img id="photo" src="" width="320">
        </div>

        <div class="modal-button-wrap clearfix">
            <div class="submit2"><button class="button-s submitBtn" id="btn-chat">写真削除</button></div>
<!--
            <div class="button-s submitBtn" id="btn-chat" onClick="deletePhoto()">写真削除</div>
-->
        </div>

        </form>
    </div><!--/#openAL-->

    <div class="modalBK"></div>
</div><!--/#modal-->

@endsection('content')

@section('footer')

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.3.4/socket.io.js"></script>

<script src="{{ asset('/js/chatFunction.js') }}"></script>

<script>
var edit_message_id = 0;
var album_id = 0;
var album_name = '';

    // 日時を0詰め
    var toDoubleDigits = function(num) {
        num += "";
        if (num.length === 1) {
            num = "0" + num;
        }
        return num;
    };

    jQuery(function($) {
        var $albumForm = $('#input_form');
        var $albumName = $('#album_name');

        var $renameForm = $('#rename_form');
        var $newAlbumName = $('#new_album_name');
        var $baseAlbumName = $('#base_album_name');

        var $deleteForm = $('#photo_form');

        var $chat = $('#chatBox');
        var $chatUsers = $('ul.chatUsers');

        var roomID = 'group_{{ $group_id }}';

        var file = null;
        var file_nm = null;

        var date = new Date();
        var timeStamp = date.getTime() ;             // UNIXタイムスタンプを取得する (ミリ秒単位)
        timeStamp = Math.floor(timeStamp / 1000) ;  // UNIXタイムスタンプを取得する (秒単位 - PHPのtime()と同じ)

        // socket オープン 接続
        var socket = new io.connect('http://tk2-224-21995.vs.sakura.ne.jp:3002', {
            'reconnection': true,
            'reconnectionDelay': 1000,
            'reconnectionDelayMax' : 5000,
            'reconnectionAttempts': 5
        });

        // 接続ユーザー（user id, name）
        socket.on('connect', function (user) {
            socket.emit('join', {
                room: roomID,
                id: "{{ Auth::user()->id }}",
                name: "{{ Auth::user()->name }}"
            });
        });

        // アルバム追加のメッセージ送信
        $albumForm.on('submit', function (e) {
            e.preventDefault();

            if ($albumName.val().length == 0) {
                alert("アルバム名を入力してください。");
                return;
            }

            var msg = '■アルバム「' + $albumName.val() + '」を追加しました。\n';

            socket.emit('chat.send.message', {
                msg: msg,
                nickname: '{{ Auth::user()->name }}',
                id: '{{ Auth::user()->id }}',
                mid: edit_message_id,
                file: file_nm,
                timeStamp: timeStamp,
                maxid: ''
            });

            document.input_form.submit();

            console.log('success');
        });

        // アルバム名変更のメッセージ送信
        $renameForm.on('submit', function (e) {
            e.preventDefault();

            if ($newAlbumName.val().length == 0) {
                alert("変更するアルバム名を入力してください。");
                return;
            }

            var msg = '■アルバム「' + $baseAlbumName.val() + '」を「' + $newAlbumName.val() + '」に変更しました。\n';

            socket.emit('chat.send.message', {
                msg: msg,
                nickname: '{{ Auth::user()->name }}',
                id: '{{ Auth::user()->id }}',
                mid: edit_message_id,
                file: file_nm,
                timeStamp: timeStamp,
                maxid: ''
            });

            document.forms['rename_form' + album_id].submit();
            album_id = 0;

            console.log('success');
        });

        // 写真削除のメッセージ送信
        $deleteForm.on('submit', function (e) {
            e.preventDefault();

            if (confirm("削除すると元には戻せません。本当に削除しますか？") == false) {
                return;
            }

            var msg = 'アルバム「' + album_name + '」から写真を1枚削除しました。\n';

            socket.emit('chat.send.message', {
                msg: msg,
                nickname: '{{ Auth::user()->name }}',
                id: '{{ Auth::user()->id }}',
                mid: edit_message_id,
                file: file_nm,
                timeStamp: timeStamp,
                maxid: ''
            });

            document.photo_form.submit();

            console.log('success');
        });

        // チャットに接続しているユーザーを表示
        socket.on('chat.users', function (nicknames) {
            var html = '';

            console.log(nicknames);

            $.each(nicknames, function (index, value) {
                html += '<li><a href="' + value.socketId + '">' + value.nickname + '</a></li>';
            });

            $chatUsers.html(html);
        });
    });


    function deleteAlbum(aid, anm) {
        var msg = 'アルバム「' + anm + '」の削除\n\n'
                + 'アルバムを削除するとアルバムの写真も削除されます。\n'
                + '一度削除すると元に戻すことはできません。\n'
                + 'このアルバムを削除してもよろしいでしょうか？\n';
        if (confirm(msg) == false) {
            return;
        }
        var form = document.deleteAlbum;

        form.album_id.value = aid;
        form.submit();
    }

    function enlargeFile(aid, anm, file) {

//        $('#openAL').trigger("click");
        wn = '.openAL';
        var mW = $(wn).find('.modalBody').innerWidth() / 2;
        var mH = $(wn).find('.modalBody').innerHeight() / 2;
        $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
        $(wn).fadeIn(100);

        document.photo_form.album_id.value = aid;
        document.photo_form.file_name.value = file;

        var type = file.split('.');

        var url = '{{ route("chat.albumImage", ["gid"=>$group_id, "albumid"=> ":aid", "filename"=>":filename", "extension"=>":extension"]) }}';
        url = url.replace(':aid', aid);
        url = url.replace(':filename', type[0]);
        url = url.replace(':extension', type[1]);

        document.getElementById('photo').src = "url";

        album_name = anm;
    }

    function renameAlbum(aid, anm) {
        document.getElementById('album-new-name' + aid).style.display = 'inline';
        document.forms['rename_form' + aid].elements['album_id'].value = aid;
        document.forms['rename_form' + aid].elements['base_album_name'].value = anm;
        album_id = aid
    }
</script>
<script>
$(function(){
    $('.modal-close,.modalBK').click(function(){
        $(wn).fadeOut(100);
    });
});
</script>

@endsection('footer')
