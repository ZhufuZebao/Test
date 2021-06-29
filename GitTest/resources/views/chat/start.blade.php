@extends('layouts.app')

@section('header')
<title>チャット</title>

<?php /*
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
*/ ?>
    <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/css/bootstrap.min.css">

    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
    <link href="{{ asset('/css/chat.css') }}" rel="stylesheet">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

<script src="{{ asset('/js/chatFunction0.js') }}"></script>

@endsection

@section('content')
<div class="container low chat">
    <h1>チャット</h1>

    <div class="col-md-3">

        <ul class="chat-nav clearfix">
            <li><a href="{{ url('/chat') }}"></a></li>
        @if ($lastDirectId != null)
            @if ($groups->kind == 1)
            <li class="current{{ $newFlg }}"><a href="{{ url('/chatstart/gid/'. $lastDirectId) }}"></a></li>
            @else
            <li><a href="{{ url('/chatstart/gid/'. $lastDirectId) }}"></a></li>
            @endif
        @else
            <li><a href="{{ url('/chat') }}"></a></li>
        @endif
        @if ($lastGroupId != null)
            @if ($groups->kind == 1)
            <li><a href="{{ url('/chatstart/gid/'. $lastGroupId) }}"></a></li>
            @else
            <li class="current{{ $newFlg }}"><a href="{{ url('/chatstart/gid/'. $lastGroupId) }}"></a></li>
            @endif
        @else
            <li><a href="{{ url('/chat') }}"></a></li>
        @endif
            <li><a href="{{ url('/chatnews') }}"></a></li>
            <li><a href="{{ url('/chatsetting') }}"></a></li>
        </ul>

        <div class="col-md-32">
        @if ($groups->kind == 1)
            <div class="chatGroupName">{{ $members[0]->name }}</div>
        @else
            <div class="memberAdd">[メンバー]<br /><a href="#open06">追加</a>｜<a href="#open08">削除</a>
            </div>
            <div class="chatGroupName">{{ $groups->name }}</div>
        @endif

            <div style="display:inline;">
                <span class="chatMember">【オンライン・メンバー】</span>
                <ul class="chatUsers">

                </ul>
            </div>
        </div>
        <br clear="all" />

        <div class="search">
            <form class="form-horizontal" name="msg_search_form" role="form" method="POST" action="{{ url('/chatsearch/gid/'. $group_id ) }}">
            {{ csrf_field() }}
            <input type="hidden" name="group_id" value="{{ $group_id }}" />
            <input type="hidden" name="mode" value="message_search" />

            <input type="text" name="keyword2" id="keyword2" value="{{ old('keyword2') }}" placeholder="メッセージ内容を検索">
            <div class="button gray"><a href="javascript:document.msg_search_form.submit();" class="search">検索</a></div>
            </form>
        </div>
    </div>
    <br clear="all" />

    <form id="send-message" name="input_form" action="?">
        <input type="hidden" name="group_id" value="{{ $group_id }}" />

<?php /*
        @include('chat._chat_template')
*/ ?>


    <div class="row">
        <div class="panel-body">
            <ul class="chat_box" id="chatBox">

        @if (is_array($messages) && !empty($messages))

            @foreach($messages as $message)

                <?php $msg = $message->message; ?>

                <?php
                $bgStyle = '';
                if ($message->from_user_id == Auth::user()->id) {
                    $bgStyle = 'style="color: #886060"';
                }

                $bgStyle2 = '';
                if (isset($message->to_re_id) && $message->to_re_id == Auth::user()->id) {
                    $bgStyle2 = 'style="background-color: #c0f0e0"';
                }
                ?>

                <li class="left clearfix message_list" {!! $bgStyle2 !!} id="message_area_{{ $message->id }}" onmouseover="mouseover({{ $message->id }})" onmouseout="mouseout({{ $message->id }})">
                    <input type="hidden" name="chatid_{{ $message->id }}" value="{{ $message->id }}" />
                    <textarea name="chatmsg_{{ $message->id }}" id="chatmsg_{{ $message->id }}" style="display:none;">{{ $msg }}</textarea>

                    <div class="chat-body clearfix"><!--会話全体を囲う-->

                        @if ($message->from_user_id != Auth::user()->id)
                        <!--左コメント始-->
                        <div class="balloon6">
                            <div class="faceicon">
                                <div class="header">
                                @if ($message->file)
                                    <img src="{{ route('chat.photo',['id'=>$message->from_user_id]) }}" class="photoImg" />
                                @else
                                    <img src="{{ asset('/photo/users/member-nophoto.png') }}" class="photoImg" />
                                @endif
                                </div>
                            </div>
                            <div class="chatting">
                                <div class="says">
                                    <p id="message_{{ $message->id }}">
                                    @if ($message->file_name)
                                        <div class="upload_file2">
                                            <div class="upload_file_title2">☆ファイルをアップロードしました。</div>
                                            <div class="upload_file_file">
                                            <?php
                                                $type = preg_split('/\./', $message->file_name);
                                                $extension = mb_strtolower($type[count($type) - 1], 'UTF-8');
                                                $preview_flg = false;
                                                if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'JPG' ||
                                                    $extension == 'png' || $extension == 'gif'
                                                ) {
                                            ?>
                                                <img src="{{ asset('/upload/'. $dir. '/'. $message->file_name) }}" width="50" /><br />
                                            <?php
                                                }
                                            ?>
                                                <a href="{{ url('/upload/'. $dir. '/'. $message->file_name) }}" target="_blank" style="color:#333;">{{ $message->file_name }}</a><br />
                                            </div>
                                        </div>
                                    @endif
                                        <?= showHtmlChatMessage($msg, $dir); ?>
                                    </p>
                                </div>
                            </div>
                            <br clear="all" />
                            <span class="user_name">{{ $message->name }}</span>
                            <br />
                            <small class="text-muted">
                                {{ $message->created_at }}
                            </small>
                        </div>
                        <!--/左コメント終-->

                        @else
                        <!--右コメント始-->
                        <div class="mycomment">
                            <div id="message_{{ $message->id }}" class="mycomment-message">
                            @if ($message->file_name)
                                ☆ファイルをアップロードしました。<br />
                                <?php
                                    $type = preg_split('/\./', $message->file_name);
                                    $extension = mb_strtolower($type[count($type) - 1], 'UTF-8');
                                    $preview_flg = false;
                                    if ($extension == 'jpg' || $extension == 'jpeg' || $extension== 'JPG' ||
                                        $extension == 'png' || $extension == 'gif'
                                    ) {
                                ?>
                                    <img src="{{ asset('/upload/'. $dir. '/'. $message->file_name) }}" width="50" /><br />
                                <?php
                                    }
                                ?>
                                    <a href="{{ url('/upload/'. $dir. '/'. $message->file_name) }}" target="_blank" style="color:#333;">{{ $message->file_name }}</a><br />
                                    ---------------------------------<br />
                            @endif
                                <?= showHtmlChatMessage($msg, $dir); ?>
                                <br clear="all" />
                            </div>
                            <br clear="all" />
                            <small class="text-muted">
                                {{ $message->created_at }}
                            </small>
                        </div>
                        <br clear="all" />
                        <!--/右コメント終-->
                        @endif

                    </div><!--/会話終了-->

                    <div class="re_area" id="re_area_{{ $message->id }}" style="display:none;">
                    @if ($message->from_user_id != Auth::user()->id)
                        <a href="javascript:reply({{ $message->id }}, {{ $message->from_user_id }}, '{{ $message->name }}');">返信</a>
                    @endif
                        <a href="javascript:quote({{ $message->id }}, {{ $message->from_user_id }}, '{{ $message->name }}', '{{ strtotime($message->created_at) }}', '{{ $message->file_name }}');">引用</a>
                    @if ($message->from_user_id == Auth::user()->id)
                        <a href="javascript:editMessage({{ $message->id }});">編集</a>
                        <a href="javascript:void(0);" id="deleteMessage_{{ $message->id }}">削除</a>
                    @endif
                    </div>
                </li>

            @endforeach

        @else
            @if ($mode == 'message_search')
                <li>メッセージがありません。</li>
            @endif
        @endif

            </ul>
            <br />
        </div>

        <div class="panel-footer" id="send-message-area">

            <div class="micon" title="絵文字：気持ちを表現できます">
                <a href="javascript:miconClick()"><img src="{{ asset('/images/m_icon/top.png') }}" alt="絵文字" /></a>
            </div>
            <div class="micon" title="TO：相手に呼び掛けることができます">
                <a href="javascript:micon2Click()"><img src="{{ asset('/images/m_icon/to.png') }}" alt="To" /></a>
            </div>
            <div class="micon" title="ファイルを送信できます">
                <div class="imgInput">
<?php if (isset($_SERVER['HTTP_USER_AGENT']) &&
        (strstr($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false &&
            strstr($_SERVER['HTTP_USER_AGENT'], 'rv:11.0') !== false)) {?>
                    <label for="file" class="file" style="width:250px; color:white; background-color:gray; padding:1px 5px; white-space:nowrap; border-radius:5px">
                        ファイル送信
                        <input type="file" name="file1" id="file" style="display:none;" />
                    </label>
<?php  } else { ?>
                    <label for="file" class="file">
                        <img src="{{ asset('/images/m_icon/clip.png') }}" height="21" style="cursor:pointer;" />
                        <input type="file" name="file1" id="file" style="display:none;" />
                    </label>
<?php  } ?>
                    <img src="{{ asset('/images/noimage.png') }}" alt="" class="imgView" id="imgViewArea">
                </div><!--/.imgInput-->
            </div>
            <div class="mbutton">
                <button type="submit" class="btn btn-warning btn-sm" id="btn-chat">send</button>
                <!--
                <button type="submit" class="btn btn-warning btn-sm" id="btn-chat2" style="display:none;">保存</button>
                -->
            </div>
            <div class="input-message">
                <div id="edit_message" class="edit-message" style="display:none;">
                    <div class="close_b"><a href="javascript:closeEdit()">×</a></div>
                    編集中です...
                </div>
                <textarea name="message" id="message-input" type="text" class="form-control input-sm input-msg" placeholder="ここにメッセージを入力して下さい..." style="ime-mode:active;"></textarea>

                <span class="input-group-btn">
                </span>
            </div>
        </div>
        <input id="csrfToken" type="hidden" name="csrf-token" value="{{ csrf_token() }}"/>

        <div class="chat-footer">
            <div class="balloon2" id="balloon2" style="display:none">
                <a href="javascript:selectAll()">すべて選択</a><br />
                <hr />
            @foreach($members as $key => $item)
                <a href="javascript:selectTo({{ $item->user_id }}, '{{ $item->name }}')">{{ $item->name }}</a><br />
            @endforeach
            </div>

            <div class="balloon" id="balloon" style="display:none">
            @for($i = 1; $i <= 10; $i++)
                <a href="javascript:miconSelect('{{ sprintf('%03d', $i) }}');"><img src="{{ asset('/images/m_icon/k_'. sprintf('%03d', $i)) }}.gif"></a>
            @endfor
            </div>
        </div>

    </div>

    </form>

    <audio id="overSound" preload="auto">
        <source src="{{ asset('/sound/receive.mp3') }}" type="audio/mp3">
        <source src="{{ asset('/sound/receive.ogg') }}" type="audio/ogg">
        <source src="{{ asset('/sound/receive.wav') }}" type="audio/wav">
        <p>※お使いのブラウザはHTML5のaudio要素をサポートしていないので音は鳴りません。</p>
    </audio>

</div>

<div id="modal">
    <div id="open06">

        <a href="#send-message-area" class="close_overlay">close</a>

        <div class="modal_window">

            <div class="close"><a href="#send-message-area">×</a></div>

            <form class="form-horizontal" name="search_form" role="form" method="POST" action="{{ url('/chatstart/gid/'. $group_id ) }}">
            {{ csrf_field() }}

            <div class="modal-content clearfix">
                <div class="invite-link"><a href="{{ url('/chatinvite/gid/'. $group_id. '/lk/0') }}" target="_blank">このグループに招待する</a></div>
                <div class="title">メンバー追加</div>
                <ul>
                    <li>
                        <input type="hidden" name="group_id" value="{{ $group_id }}" />
                        <input type="hidden" name="mode" value="" />

                        <div class="search">
                            <div style="float:left; width:80%;"><input type="text" name="keyword" id="keyword" value="{{ old('keyword') }}" placeholder="名前"></div>
                            <div class="searchButtonGroup"><a href="javascript:searchUsers();">検索</a></div>
                        </div>
                        <br clear="all" />
                    </li>
                    <li>
                        <div class="chat member">
                            <div class="searchmember" id="search_member">
                            @if (isset($result) && is_array($result) && !empty($result))
                                @foreach($result as $item)
                                <label for="add_{{ $item->id }}">
                                    <div>
                                        <input type="checkbox" name="add[{{ $item->id }}]" id="add_{{ $item->id }}" value="" class="member_check" />
                                        @if ($item->file != '')
                                        <img src="{{ asset('/photo/users/'. $item->file) }}" alt="{{ $item->name }}" width="20" />
                                        @else
                                        <img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" width="20" />
                                        @endif
                                        <span class="from_user_name" id="addUserName_{{ $item->id }}">{{ $item->name }}</span>
                                    </div>
                                </label>
                                @endforeach
                            @else
                                @if (old('keyword') != '')
                                    検索したユーザーは既にこのグループに登録済みか、ユーザー登録されていません。
                                @endif
                            @endif
                            </div>
                        </div>

                    </li>
                </ul>

                <div class="clearfix"></div>
                @if (isset($result) && is_array($result) && !empty($result))
                <div class="button green" style="display:block;"><a href="javascript:void(0);" id="addUser">追加</a></div>
                @endif
            </div>

            </form>
        </div><!--/.modal_window-->
    </div><!--/#open06-->

</div><!--/#modal-->

<div id="modal">
    <div id="open08">

        <a href="#send-message-area" class="close_overlay">close</a>

        <div class="modal_window">

            <div class="close"><a href="#send-message-area">×</a></div>

            <div class="modal-content clearfix">
                <div class="title">メンバー削除</div>

                <ul>
                    <li>
                        <div class="chat member">
                            <div class="searchmember" id="search_member2">
                                <form>
                                @if (isset($members) && is_array($members) && !empty($members))
                                    @foreach($members as $item)
                                    <label for="del_{{ $item->user_id }}">
                                        <div>
                                            <input type="checkbox" name="del[{{ $item->user_id }}]" id="del_{{ $item->user_id }}" value="" class="member_check" />
                                            @if ($item->file != '')
                                            <img src="{{ asset('/photo/users/'. $item->file) }}" alt="{{ $item->name }}" width="20" />
                                            @else
                                            <img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" width="20" />
                                            @endif
                                            <span class="from_user_name" id="deleteUserName_{{ $item->user_id }}">{{ $item->name }}</span>
                                        </div>
                                    </label>
                                    @endforeach
                                @endif
                                </form>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="clearfix"></div>
                @if (isset($members) && is_array($members) && !empty($members))
                <div class="button green" style="display:block;"><a href="javascript:void(0);" id="deleteUser">削除</a></div>
                @endif

            </div>

            </form>
        </div><!--/.modal_window-->
    </div><!--/#open08-->

</div><!--/#modal-->

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdn.socket.io/socket.io-1.3.4.js"></script>

<script src="{{ asset('/js/chatFunction.js') }}"></script>

<script>
    // 次のメッセージID
    var message_id = <?= isset($message) ? $message->id + 1 : '1' ?>;
    var edit_message_id = 0;

    // 日時を0詰め
    var toDoubleDigits = function(num) {
        num += "";
        if (num.length === 1) {
            num = "0" + num;
        }
        return num;
    };

    // ジャンプする場所
<?php if (isset($readMessageId) && $readMessageId != '') { ?>
    location.href = "#message_area_{{ $readMessageId }}";
<?php } else { ?>
    location.href = "#send-message-area";
<?php } ?>

    jQuery(function($) {
        var $messageForm = $('#send-message');
        var $messageBox = $('#message-input');
        var $chat = $('ul.chat_box');
        var $chatUsers = $('ul.chatUsers');

        var roomID = 'group_{{ $group_id }}';

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

//        socket.join(roomID);

        // 接続ユーザー（user id, name）
        socket.on('connect', function (user) {
            socket.emit('join', {
                room: roomID,
                id: "{{ Auth::user()->id }}",
                name: "{{ Auth::user()->name }}"
            });
        });

        // メッセージ送信
        $messageForm.on('submit', function (e) {
            e.preventDefault();

            var file = null;
            var file_nm = null;
            if ($("input[name='file1']").val() !== '') {
                file = $("input[name='file1']").prop("files")[0];
                file_nm = file['name'];
                console.log(file);
            }

            console.log('{{ Auth::user()->id }}');

            socket.emit('chat.send.message', {
                msg: $messageBox.val(),
                nickname: '{{ Auth::user()->name }}',
                id: '{{ Auth::user()->id }}',
                mid: edit_message_id,
                file: file_nm,
                timeStamp: timeStamp,
                maxid: message_id
            });

            var data = new FormData();
            if ($("input[name='file1']").val()!== '') {
                data.append( "file", $("input[name='file1']").prop("files")[0] );
            }
            data.append("dir", $("#file1").val());
            data.append("message", $messageBox.val());
            data.append("id", {{ Auth::user()->id }});
            data.append("mid", edit_message_id);
            data.append("timeStamp", timeStamp);

            document.getElementById('imgViewArea').style.display = 'none';

            $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': $('#csrfToken').val()
                }
            });
            // メッセージをDBに登録
            $.ajax({
                type: 'POST',
                url: '{{ url('/messages/gid/'. $group_id) }}',
                data: data,

                dataType : "text",

                processData : false,
                contentType : false,

                success: function(message) {
                    console.log('success!!', message);
                    $messageBox.val('');
                }
            })

            $messageBox.val('');
            $('#file').val('');

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


        // 送られて来たメッセージをすべてのユーザーに表示
        socket.on('chat.message', function (data) {

            data = JSON.parse(data);
            console.log(data);

            if(data.hasOwnProperty('system')) {
                toastr["success"](data.msg);
            } else {

                // 日付データを取得して変数hidukeに格納する
                var hiduke= new Date();

                var year = toDoubleDigits(hiduke.getFullYear());
                var month = toDoubleDigits(hiduke.getMonth()+1);
                var day = toDoubleDigits(hiduke.getDate());
                var hour = toDoubleDigits(hiduke.getHours());
                var minute = toDoubleDigits(hiduke.getMinutes()+1);
                var second = toDoubleDigits(hiduke.getSeconds());

                // メッセージを送信した日時
                var time_str = year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;

                // 送られてきたメッセージ
                var strMsg = data.msg;

                var qt_mark = new Array();
                var qt_str  = new Array();
                var offset = 0;

                // 引用する内容を編集
                while(true) {
                    var qt_name = '';
                    var qt_time = '';

                    var pos = strMsg.indexOf('[time:', offset);
                    if (pos === -1) {
                        break;
                    }

                    var pos2 = strMsg.indexOf(']', pos+1);
                    var tmp = strMsg.substring(pos+1, pos2);
                    var tmp2 = tmp.split(" ");
                    var tmp4 = new Array();

                    for (i = 0; i < tmp2.length; i++) {
                        var item = tmp2[i];
                        tmp3 = item.split(":");
                        tmp4[i] = tmp3[1];
                    }

                    // 0=time, 1=id, 2=mid
                    if (tmp4[1]) {

                        $.ajax({
                            type: 'GET',
                            url: '{{ url('/getUserName/id') }}/' + tmp4[1],
                            async: false,
                            complete: function (result) {
                                //console.log(result);
                                qt_name = result.responseText;
                            }
                        });
                    }
                    if (tmp4[0]) {
                        qt_time = timestampToString(tmp4[0]);
                    }
                    qt_mark.push(tmp);

                    qt_str.push('<p style="color:silver;">' + qt_name + ' ' + qt_time + '</p><br />');

                    offset = pos2 + 1;
                }

                // Toの相手を編集
                while (true){
                    var pos = strMsg.indexOf('[To:');
                    if (pos == -1) {
                        break;
                    }
                    var tmp = '';
                    if (pos > 0) {
                        tmp = tmp + strMsg.substring(0, pos);
                    }
                    tmp = tmp + '<img src="{{ asset('/images/m_icon/to2.png') }}" width="28" /> ';

                    pos2 = strMsg.indexOf(']', pos+1);
                    tmp = tmp + strMsg.substring(pos2*1+1);

                    strMsg = tmp;
                }

                // 返信の相手を編集
                while (true){
                    var pos = strMsg.indexOf('[mid:');
                    if (pos == -1) {
                        break;
                    }
                    var tmp = '';
                    if (pos > 0) {
                        tmp = tmp + strMsg.substring(0, pos);
                    }
                    tmp = tmp + '<img src="{{ asset('/images/m_icon/reply.png') }}" width="35" /> ';

                    pos2 = strMsg.indexOf(']', pos+1);
                    tmp = tmp + strMsg.substring(pos2*1+1);

                    strMsg = tmp;
                }

                // アップロードファイル
                while (true){
                    var pos = strMsg.indexOf('[upload]');
                    if (pos == -1) {
                        break;
                    }
                    pos2 = strMsg.indexOf('[/upload]', pos+1);
                    var file_name = strMsg.substring(parseInt(pos)+8, parseInt(pos2));

                    console.log('file_name=' + file_name);

                    var type = file_name.split('.');
                    var extension = type[type.length - 1].toLowerCase();
                    var preview_flg = false;
                    if (extension == 'jpg' || extension == 'jpeg' || extension == 'JPG' ||
                        extension == 'png' || extension == 'gif'
                    ) {
                        preview_flg = true;
                    }

                    tmp  = strMsg.substring(0, pos);

                    if (preview_flg == true) {
                        tmp  = tmp +
                            '<div class="upload_file">' +
                                '<div class="upload_file_title">☆ファイルをアップロードしました。</div>' +
                                '<div class="upload_file_file">' +
                                    '<img src="{{ asset('/upload/'. $dir) }}/' + file_name + '" width="50" /><br />' +
                                    file_name + '<br />'
                                '</div>' +
                            '</div>';
                    } else {
                        tmp  = tmp +
                            '<div class="upload_file">' +
                                '<div class="upload_file_title">☆ファイルをアップロードしました。</div>' +
                                '<div class="upload_file_file">' +
                                    file_name + '<br />'
                                '</div>' +
                            '</div>';
                    }
//                    console.log('tmp2=' + tmp);

                    tmp = tmp + strMsg.substring(parseInt(pos2)+9);

//                    console.log('tmp3=' + tmp);

                    strMsg = tmp;
                }

                // 絵文字を置換
                strMsg = strMsg.replace(/\[icon\:001]/g, '<img src="<?= asset('/images/m_icon/k_001.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:002]/g, '<img src="<?= asset('/images/m_icon/k_002.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:003]/g, '<img src="<?= asset('/images/m_icon/k_003.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:004]/g, '<img src="<?= asset('/images/m_icon/k_004.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:005]/g, '<img src="<?= asset('/images/m_icon/k_005.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:006]/g, '<img src="<?= asset('/images/m_icon/k_006.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:007]/g, '<img src="<?= asset('/images/m_icon/k_007.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:008]/g, '<img src="<?= asset('/images/m_icon/k_008.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:009]/g, '<img src="<?= asset('/images/m_icon/k_009.gif'); ?>">');
                strMsg = strMsg.replace(/\[icon\:010]/g, '<img src="<?= asset('/images/m_icon/k_010.gif'); ?>">');

                // 引用タグを置換
                strMsg = strMsg.replace(/\[qt\]/g, '<blockquote>');
                strMsg = strMsg.replace(/\[\/qt\]/g, '</blockquote>');

                if (qt_mark[0] != '') {
                    for (i = 0; i < qt_mark.length; i++) {
                        var s = '[' + qt_mark[i] + ']';
                        var w = preg_quote (s)
                        strMsg = strMsg.replace(new RegExp(w, 'g'), qt_str[i]);
                    }
                }

                // 改行コードをタグに置換
                strMsg = strMsg.replace(/\n/g, '<br />');

                // 返信、編集、削除のリンクを編集
                var strReply  = '';
                var strDelete = '';
                var strEdit   = '';
                if (data.id != {{ Auth::user()->id }}) {
                    strReply = '<a href="javascript:reply(' + message_id + ', ' + data.id + ', \'' + data.nickname + '\');">返信</a>';
                }
                if (data.id == {{ Auth::user()->id }}) {
                    strEdit = '<a href="javascript:editMessage(' + message_id + ');">編集</a>';
                    strDelete = '<a href="javascript:void(0);" id="deleteMessage_' + message_id + '">削除</a>';
                }

                var time = new Date() / 1000 | 0;

                console.log('data.file=' + data.file);

                // アップロードファイルを表示するように編集
                var file_name = '';
                if (data.file && data.file != '' && typeof data.file !== 'undefined') {
                    var tmpFile = data.file.split(".");
                    if (data.file.indexOf('Android_') !== -1) {
                        file_name = data.file;
                    } else {
                        file_name = tmpFile[0] + '_' + data.timeStamp + '.' + tmpFile[1];
                    }

                    console.log('indexOf=' + data.file.indexOf('Android_'));
                    console.log('file_name=' + file_name);

                    strFile =
<?php /*
                    '<div class="upload_file">' +
                        '<div class="upload_file_title">☆ファイルをアップロードしました。</div>' +
*/ ?>
                    '<div class="upload_file2">' +
                        '<div class="upload_file_title2">☆ファイルをアップロードしました。</div>' +
                        '<div class="upload_file_file">' +
                            '<div id="loader-bg_' + message_id + '">' +
                                '<div id="loader_' + message_id + '">' +
                                    '<img src="<?= asset('/images/loading.gif'); ?>" width="30" height="30" alt="Now Loading..." />' +
                                    '<p>Now Loading...</p>' +
                                '</div>' +
                            '</div>' +
                            '<div id="wrap_' + message_id + '">';

                    if (tmpFile[1] == 'jpg' || tmpFile[1] == 'jpeg' || tmpFile[1] == 'JPG' || tmpFile[1] == 'png' || tmpFile[1] == 'gif') {
                        strFile = strFile +
                                    '<img src="{{ asset('/upload/'. $dir) }}/' + file_name + '" width="50" /><br />';
                    }

                    strFile = strFile +
                                '<a href="{{ url('/upload/'. $dir) }}/' + file_name + '" target="_blank" style="color:#333;">' + file_name + '</a><br />' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    strMsg + '<br />';

                } else {
                    strFile = strMsg
                }

                var photoFile = '<img src="<?= asset('/photo/users/member-nophoto.png'); ?>" class="photoImg" />'
                if (data.id == {{ $from_user_id }}) {
<?php if ($file != '') { ?>
                    photoFile = '<img src="<?= asset('/photo/users/'. $file); ?>" class="photoImg" />';
<?php } ?>
<?php foreach ($members as $item) { ?>
                } else if (data.id == {{ $item->user_id }}) {
                    if ('{{ $item->file }}' != '') {
                        photoFile = '<img src="<?= asset('/photo/users/'. $item->file); ?>" class="photoImg" />';
                    }
<?php } ?>
                }
//alert(data.mid);

                // 既に送信済みのメッセージを編集
                if (data.mid > 0) {
                    var msg_el = document.getElementById('message_' + data.mid);
                    msg_el.innerHTML = strMsg;

                    closeEdit();

                // 新規のメッセージを表示
                } else {

                    bgStyle = '';
                    if (data.id == '{{ Auth::user()->id }}') {
                        bgStyle = 'style="color: #886060"';
                    }

//alert(data.msg);
                    bgStyle2 = '';
                    if (data.msg.indexOf('{{ Auth::user()->name }}') > -1) {
                        bgStyle2 = 'style="background-color: #c0f0e0"';
                    }

                    var strHtml =
                        '<li class="left clearfix message_list" ' + bgStyle2 + ' id="message_area_' + message_id + '" onmouseover="mouseover(' + message_id + ')" onmouseout="mouseout(' + message_id + ')">' +
                            '<input type="hidden" name="chatid_' + message_id + '" value="' + message_id + '" />' +
                            '<textarea name="chatmsg_' + message_id + '" id="chatmsg_' + message_id + '" style="display:none;">' + data.msg + '</textarea>' +
                            '<div class="chat-body clearfix"><!--会話全体を囲う-->';

                    if (data.id != '{{ Auth::user()->id }}') {

                        var strHtml = strHtml +
                        '<!--左コメント始-->' +
                        '<div class="balloon6">' +
                            '<div class="faceicon">' +
                                '<div class="header">' +
                                    photoFile +
                                '</div>' +
                            '</div>' +
                            '<div class="chatting">' +
                                '<div class="says">' +
                                    '<p id="message_' + message_id + '">' + strFile + '</p>' +
                                '</div>' +
                            '</div>' +
                            '<br clear="all" />' +
                            '<span class="user_name">' + data.nickname + '</span>' +
                            '<br />' +
                            '<small class="text-muted">' +
                            time_str +
                            '</small>' +
                        '</div>' +
                        '<!--/左コメント終-->';

                    } else {

                        var strHtml = strHtml +
                        '<!--右コメント始-->' +
                        '<div class="mycomment">' +
                            '<div id="message_' + message_id + '" class="mycomment-message">' +
//                                '<div class="header">' +
//                                    photoFile +
//                                '</div>' +
//                            '<p id="message_' + message_id + '">' + strFile + '</p>' +
                            strFile +
                            '</div>' +
                            '<br clear="all" />' +
                            '<small class="pull-right text-muted">' +
                            time_str +
                            '</small>' +
                        '</div>' +
                        '<br clear="all" />' +
                        '<!--/右コメント終-->';
                    }
                    var strHtml = strHtml +
                                '</div><!--/会話終了-->'+
                            '<div class="re_area" id="re_area_' + message_id + '" style="display:none;">' +
                            strReply +
                            '<a href="javascript:quote(' + message_id + ', ' + data.id + ', \'' + data.nickname + '\', \'' + time + '\');">引用</a>' +
                            strEdit +
                            strDelete +
                        '</div>' +
                    '</li>';


                    $chat.append(strHtml);

                    if (file_name != '') {
                        var img = new Image();
//alert(img.src);
                        $('#wrap_' + message_id).css('display', 'none');

                        img.src = '{{ asset('/upload/'. $dir) }}/' + file_name;
                        img.onload = function() {
//alert(img.src);
                            $('#loader-bg_' + message_id).delay(900).fadeOut(800);
                            $('#loader_' + message_id).delay(600).fadeOut(300);
                            $('#wrap_' + message_id).css('display', 'block');
                        }

                        // 10秒たったら強制的にロード画面を非表示
                        $(function(){
                            setTimeout('stopload(' + message_id + ')', 10000);
                        });
                    }
                }
//alert(strHtml);

<?php if ($sound == 1) {?>
                document.getElementById("overSound").currentTime = 0;
                document.getElementById("overSound").play();
<?php } else if ($sound == 2) { ?>
                if (data.msg.indexOf('{{ Auth::user()->name }}') > -1) {
                    document.getElementById("overSound").currentTime = 0;
                    document.getElementById("overSound").play();
                }
<?php } ?>
                location.href = "#send-message-area";

                message_id++;
            }
        });

        // 削除クリックでメッセージを削除して、他のユーザーにも知らせる
        $('#chatBox').on('click', function (e) {
            var id = e.target.id;

             if (id.indexOf('deleteMessage') != -1) {

                 if (confirm('削除します。よろしいですか？') == false) {
                     return;
                 }

                 var idTmp = id.split("_");
                 var mid = idTmp[1];

                 // DBから削除
                 $.ajax({
                     type: 'GET',
                     url: '{{ url('/deleteMessage/id') }}/' + mid,
                     async: false,
                     complete: function (result) {
                         console.log(result);
                     }
                 });

                 socket.emit('chat.delete.message', {
                     mid: mid
                 });
             }
        });

        // このチャットの全ユーザーのメッセージを削除する
        socket.on('chat.delete', function (data) {
            data = JSON.parse(data);
            console.log(data);

            var el = document.getElementById('message_area_' + data.mid);

            if (el.hasChildNodes()){
                // 子ノードを全削除
                for (var i = el.childNodes.length - 1; i >= 0; i--) {
                    el.removeChild(el.childNodes[i]);
                }

                var objParent = el.parentNode;
                objParent.removeChild(el);
            }
        });

        // ユーザーを追加
        $('#addUser').on('click', function () {

            var data = new FormData();
            data.append("mode", 'add');
            data.append("mid", message_id);

            data.append("dir", '');
            data.append("id", {{ Auth::user()->id }});
            data.append("mid", edit_message_id);
            data.append("timeStamp", timeStamp);

            message_id++;

            $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': $('#csrfToken').val()
                }
            });

            $('input[name^=add]').each(function(index , chkbox){
                console.log(index + ':' + chkbox.id + ':' +  chkbox.name + ':' + chkbox.value);

//                if (chkbox.value == 1){
                if (chkbox.checked){
                    var tmp = chkbox.id.split("_");
                    console.log('0:' + tmp[0] + ', 1:' +  tmp[1]);

                    var msg = "メンバーを追加しました。\n" + document.getElementById('addUserName_' + tmp[1]).innerText + 'さん';

                    // メンバー追加のメッセージを他のユーザーにも送る
                    socket.emit('chat.send.message', {
                        msg: msg,
                        nickname: '{{ Auth::user()->name }}',
                        id: '{{ Auth::user()->id }}',
                        mid: edit_message_id,
                        file: '',
                        timeStamp: timeStamp
                    });

                    data.append("uid", tmp[1]);

                    // 追加したユーザーをDBに登録
                    $.ajax({
                        type: 'POST',
                        url: '{{ url('/chatadduser/gid/'. $group_id) }}',
                        data: data,

                        dataType : "text",

                        processData : false,
                        contentType : false,

//                        async: false,
                        complete: function (result) {
                            console.log(result);
                        }
                    });

                    data.append("message", msg);

                    // メンバー追加のメッセージをDBに登録
                    $.ajax({
                        type: 'POST',
                        url: '{{ url('/messages/gid/'. $group_id) }}',
                        data: data,

                        dataType : "text",

                        processData : false,
                        contentType : false,

                        success: function(message) {
                            console.log('success!!', message);
                            $messageBox.val('');
                        }
                    });
                }
            });

            $('#keyword').val('');
//            location.reload(true);
            document.getElementById('search_member').innerHTML = '';

            location.href = "#send-message-area";

        });


        // ユーザーを削除
        $('#deleteUser').on('click', function () {

            var data = new FormData();
            data.append("mode", 'delete');
            data.append("mid", message_id);

            data.append("dir", '');
            data.append("id", {{ Auth::user()->id }});
            data.append("mid", edit_message_id);
            data.append("timeStamp", timeStamp);

            message_id++;

            $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': $('#csrfToken').val()
                }
            });

            $('input[name^=del]').each(function(index , chkbox){
                console.log(index + ':' + chkbox.id + ':' +  chkbox.name + ':' + chkbox.value);

//                if (chkbox.value == 1){
                if (chkbox.checked){
                    var tmp = chkbox.id.split("_");
                    console.log('0:' + tmp[0] + ', 1:' +  tmp[1]);

                    var msg = "メンバーを削除しました。\n" + document.getElementById('deleteUserName_' + tmp[1]).innerText + 'さん';

                    // メンバー削除のメッセージを他のユーザーにも送る
                    socket.emit('chat.send.message', {
                        msg: msg,
                        nickname: '{{ Auth::user()->name }}',
                        id: '{{ Auth::user()->id }}',
                        mid: edit_message_id,
                        file: '',
                        timeStamp: timeStamp
                    });

                    data.append("uid", tmp[1]);

                    // 削除したユーザーをDBに登録
                    $.ajax({
                        type: 'POST',
                        url: '{{ url('/chatdeleteuser/gid/'. $group_id) }}',
                        data: data,

                        dataType : "text",

                        processData : false,
                        contentType : false,

//                        async: false,
                        complete: function (result) {
                            console.log(result);
                        }
                    });

                    data.append("message", msg);

                    // メンバー削除のメッセージをDBに登録
                    $.ajax({
                        type: 'POST',
                        url: '{{ url('/messages/gid/'. $group_id) }}',
                        data: data,

                        dataType : "text",

                        processData : false,
                        contentType : false,

                        success: function(message) {
                            console.log('success!!', message);
                            $messageBox.val('');
                        }
                    });
                }
            });

            socket.emit('chat.delete.user', {
                nickname: '{{ Auth::user()->name }}',
                id: '{{ Auth::user()->id }}',
            });

        });

        // このチャットの全ユーザーを更新
        socket.on('chat.deleteUser', function (data) {
//            location.reload();
            location.href = '{{ url('/chatstart/gid/'. $lastGroupId) }}';
        });

    });

    // 閉じる または 離れる イベント
    window.addEventListener("beforeunload", function(eve){
        $.ajax({
            type: 'GET',
            url: '{{ url('/updateReadId/gid/'. $group_id. '/id') }}/' + (message_id > 1 ? message_id - 1 : 1),
            async: false,
            complete: function (result) {
                console.log(result);
            }
        });
    }, false);

</script>
<script>
//Toですべて選択でテキストエリアに代入
function selectAll() {
    @foreach($members as $key => $item)
    selectTo({{ $item->user_id }}, '{{ $item->name }}');
    @endforeach
}
</script>
<script>
$(function() {
    var $win = $(window),
        $main = $('.row'),
        $nav = $('.col-md-3'),
        navHeight = $nav.outerHeight(),
        navPos = $nav.offset().top,
        fixedClass = 'is-fixed';

    $win.on('load scroll', function() {
        var value = $(this).scrollTop();
        if ( value > navPos ) {
            $nav.addClass(fixedClass);
            $main.css('margin-top', navHeight);
        } else {
            $nav.removeClass(fixedClass);
            $main.css('margin-top', '0');
        }
    });
});
</script>
<script>
/*
window.onload = function() {
    @if (isset($readMessageId) && $readMessageId != '')
    window.location.href = "#message_area_{{ $readMessageId }}";
    @endif
}
*/
<?php if ($mode == 'search') { ?>
window.onload = function() {
    location.href = "#open06";
}
<?php } else if ($mode == 'delete_user') { ?>
window.onload = function() {
    location.href = "#open08";
}
<?php } ?>

function searchUsers() {
    document.search_form.mode.value = 'search';
    document.search_form.submit();
}

</script>

@endsection
