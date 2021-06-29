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

<?php /*
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
*/ ?>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

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

                <div style="float:left; display:none;">
                    <span class="chatMember">【オンライン・メンバー】</span>
                    <ul class="chatUsers">
                    </ul>
                </div>

            @if ($groups->kind == 0)
                <div class="memberAdd">[メンバー]<br />
                    <span class="btns" date-tgt="open06" id="open06">追加</span> |
                    <span class="btns" date-tgt="open08" id="open08">削除</span>
                </div>
            @endif

                <a href="{{ url('/chatsetting') }}" class="menu-font">チャット設定</a>
                <a href="javascript:leaveGroup();" class="menu-font">グループチャットから退席</a>
                <a href="javascript:deleteGroup();" class="menu-font">グループチャット削除</a>
                <a href="{{ url('/chattasklist/gid/'. $group_id) }}" class="menu-font">タスク一覧</a>
                <a href="{{ url('/chatalbum/gid/'. $group_id) }}" class="menu-font">アルバム</a>
                <form name="leavegroup_form" method="POST">
                    {{ csrf_field() }}
                    <input type="hidden" name="group_id" value="{{ $group_id }}" />
                </form>

                <form class="form-horizontal" name="msg_search_form" role="form" method="POST" action="{{ url('/chatsearch/'. $group_id ) }}">
                {{ csrf_field() }}
                <input type="hidden" name="group_id" value="{{ $group_id }}" />
                <input type="hidden" name="mode" value="message_search" />

                <dl class="chat-serch clearfix">
                    <dt>フリーワード</dt>
                    <dd><input type="text" name="keyword2" id="keyword2" value="{{ old('keyword2') }}"></dd>
                </dl>

                <div class="button-s" onclick="document.msg_search_form.submit();">検索</div>

                </form>
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

            <form id="send-message" name="input_form" action="?">
                <input id="csrfToken" type="hidden" name="csrf-token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="group_id" value="{{ $group_id }}" />

            <div id="chatBox">
        @if (is_array($messages) && !empty($messages))

            @foreach($messages as $message)

            <?php $msg = $message->message; ?>

            @if (isset($message) && isset($message->id))
                <input type="hidden" name="chatid_{{ $message->id }}" value="{{ $message->id }}" />
                <textarea name="chatmsg_{{ $message->id }}" id="chatmsg_{{ $message->id }}" style="display:none;">{{ $msg }}</textarea>
            @endif

                <div class="chat-contants-wrap" id="message_area_{{ $message->id }}">
                    <div class="talk-item-wrap clearfix">
                        <div class="talk-user-item-wrap clearfix">
                            <div class="talk-photo">
                            @if ($message->file)
                                <img src="{{ route('chat.photo',['id'=>$message->from_user_id]) }}" class="photoImg" />
                            @else
                                <img src="{{ asset('/photo/users/member-nophoto.png') }}" class="photoImg" />
                            @endif
                            </div>
                            <div class="talk-name">{{ $message->name }}</div>
                            <div class="talk-date">{{ $message->created_at }}</div>
                        </div>

                        <div class="icon-item-wrap talk-icon-item-wrap">
                            <span class="response" onclick="reply({{ $message->id }}, {{ $message->from_user_id }}, '{{ $message->name }}');">返信</span>
                            <span class="task" onclick="task({{ $message->id }}, {{ $message->from_user_id }}, '{{ $message->name }}', '{{ strtotime($message->created_at) }}', '{{ $message->file_name }}');">タスク</span> |
                            <span class="bookmark">ブックマーク</span>
                            @if ($message->from_user_id != Auth::user()->id)
                            <span class="nice">いいね</span>
                            @endif
                            @if ($message->from_user_id == Auth::user()->id)
                            <span class="edit" onclick="editMessage({{ $message->id }});">編集</span>
                            <span class="dust" id="deleteMessage_{{ $message->id }}">ごみ箱</span>
                            @endif
                        </div>
                    </div>

                    <div class="talk-contents">
                        <div>
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
                                        <img src="{{ route('chat.uploadImage', ['gid'=>$group_id, 'filename'=>$type[0], 'extension'=>$extension]) }}" width="50" /><br />
                                    <?php
                                        }
                                    ?>
                                        <a href="{{ route('chat.uploadImage', ['gid'=>$group_id, 'filename'=>$type[0], 'extension'=>$extension]) }}" target="_blank" style="color:#333;">{{ $message->file_name }}</a><br />
                                    </div>
                                </div>
                            @endif
                            <?= showHtmlChatMessage($msg, $dir); ?>
                        </div>
<?php /*
                        <textarea id="height-adjustment" readonly><?= showHtmlChatMessage($msg, $dir); ?></textarea>
                        <textarea id="height-adjustment" readonly><?= $msg ?></textarea>
*/ ?>
                    </div>
                </div>

            @endforeach

        @else
            @if ($mode == 'message_search')
                メッセージがありません。
            @endif
        @endif
            </div>

            <div id="send-message-area"></div>

                <div class="chat-contants-wrap add-chat-contants-wrap">
                    <div class="talk-item-wrap clearfix">
                        <div class="talk-to">
                            <select id="toMember" onchange="selectToChange();">
                                <option>To</option>
                                <option value="all">すべて選択</option>
                            @if (is_array($members) && !empty($members))
                                @foreach ($members as $key => $item)
                                <option value="{{ $item->user_id }}">{{ $item->name }}</option>
                                @endforeach
                            @endif
                            </select>
                        </div>

                        <div class="icon-item-wrap add-talk-icon-item-wrap">

                            <span class="file btns" date-tgt="wd1" id="wd1">添付ファイル</span>
                            <span class="meeting" onclick="window.location.href='{{ url('/camera/'. $group_id) }}'">meeting</span>
                            <span class="emoji" onclick="miconClick()">絵文字</span>
                        </div>

                        <div class="chat-footer">
                            <div class="balloon" id="balloon" style="display:none">
                            @for($i = 1; $i <= 10; $i++)
                                <a href="javascript:miconSelect('{{ sprintf('%03d', $i) }}');"><img src="{{ asset('/images/m_icon/k_'. sprintf('%03d', $i)) }}.gif"></a>
                            @endfor
                            </div>
                        </div>

<?php /*
                        <div class="submit" id="btn-chat">送信</div>
*/ ?>
                        <div class="submit2"><button type="submit" class="submit" id="btn-chat">送信</button></div>
                    </div>

                    <div class="talk-contents">
                        <div id="edit_message" class="edit-message" style="display:none;">
                            <div class="close_b"><a href="javascript:closeEdit()">×</a></div>
                            編集中です...
                        </div>
                        <textarea name="message" id="message-input" placeholder="ここにメッセージを入力して下さい..." style="ime-mode:active;"></textarea>
                    </div>
                </div>

        </section>
        <!--/friend-wrapper-->

        </form>
@endsection('content')

@section('footer')

<!--modal-->
<div class="modal wd1">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form id="send-message" name="input_form2" action="?">
        <input id="csrfToken" type="hidden" name="csrf-token" value="{{ csrf_token() }}"/>
        <input type="hidden" name="group_id" value="{{ $group_id }}" />
    @if (isset($message) && isset($message->id))
        <input type="hidden" name="chatid_{{ $message->id }}" value="{{ $message->id }}" />
        <textarea name="chatmsg_{{ $message->id }}" id="chatmsg_{{ $message->id }}" style="display:none;">{{ $msg }}</textarea>
    @endif

        <dl class="modal-content clearfix">
            <dd><p>ファイル送信</p></dd>
            <dd>
                <div class="imgInput">
                    <label for="file" class="file">
                        <p id="selectFile">添付ファイルを選択</p>
                        <input type="file" name="file1" id="file" style="display:none;" />
                    </label>
                    <img src="{{ asset('/images/noimage.png') }}" alt="" class="imgView" id="imgViewArea">
                    <p id="fileName"></p>
                </div><!--/.imgInput-->

            </dd>

            <dd>
                <textarea name="message" id="message-input" placeholder="ここにメッセージを入力して下さい..." style="width:90%; ime-mode:active;"></textarea>
            </dd>
        </dl>


        <div class="modal-button-wrap clearfix">
            <div class="submit2"><button type="submit" class="button-s submitBtn" id="btn-chat">送信</button></div>
        </div>

        </form>
    </div>

    <div class="modalBK"></div>
</div>
<!--/modal-->

<div class="modal open01" id="open01">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form id="send-message" name="task_form" action="{{ url('/savetask') }}">
        <input id="csrfToken" type="hidden" name="csrf-token" value="{{ csrf_token() }}"/>
        <input type="hidden" name="group_id" value="{{ $group_id }}" />
    @if (isset($message) && isset($message->id))
        <input type="hidden" name="chatid_{{ $message->id }}" value="{{ $message->id }}" />
        <textarea name="chatmsg_{{ $message->id }}" id="chatmsg_{{ $message->id }}" style="display:none;">{{ $msg }}</textarea>
    @endif

        <div class="modal-content clearfix">
            <div class="title">
                タスク
            </div>
            <ul>
                <li>
                    <input type="hidden" name="group_id" value="{{ $group_id }}" />
                    <input type="hidden" name="mode" value="" />

                    <textarea name="note" id="note-input" placeholder="ここにメッセージを入力して下さい..." style="width:95%; ime-mode:active;"></textarea>
                    <br clear="all" />
                @if ($errors->has('note'))
                    <span class="help-block">
                        <strong>{{ $errors->first('note') }}</strong>
                    </span>
                @endif
                </li>
                <li>
                    有効期限：<input type="text" name="limit_date" placeholder="例）2018/10/02" value="{{ date('Y/m/d') }}" />
                @if ($errors->has('limit_date'))
                    <span class="help-block">
                        <strong>{{ $errors->first('limit_date') }}</strong>
                    </span>
                @endif
                </li>
                <li>
                    <div class="chat member">
                        <div class="searchmember" id="search_member2">
                            @if (isset($members2) && is_array($members2) && !empty($members2))
                                @foreach($members2 as $item)
                                <label for="member_{{ $item->id }}">
                                    <div>
                                        <input type="checkbox" name="member[{{ $item->id }}]" id="member_{{ $item->id }}" value="1" class="member_check" />
                                        @if ($item->file != '')
                                        <img src="{{ route('chat.photo',['id'=>$item->id]) }}" alt="{{ $item->name }}" width="20" />
                                        @else
                                        <img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" width="20" />
                                        @endif
                                        <span class="from_user_name" id="memberName_{{ $item->id }}">{{ $item->name }}</span>
                                    </div>
                                </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @if ($errors->has('member'))
                    <span class="help-block">
                        <strong>{{ $errors->first('member') }}</strong>
                    </span>
                @endif
                </li>
            </ul>
        </div>

        <div class="modal-button-wrap clearfix">
            <div class="submit2"><button type="submit" class="button-s submitBtn" id="btn-chat">タスクを追加</button></div>
        </div>

        </form>
    </div><!--/#open01-->

    <div class="modalBK"></div>
</div><!--/#modal-->

<div class="modal open06" id="open06">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form class="form-horizontal" name="search_form" role="form" method="POST" action="{{ url('/chat') }}">
        {{ csrf_field() }}

        <div class="modal-content clearfix">
            <div class="title">
                <div class="invite-link"><a href="{{ url('/chatinvite/gid/'. $group_id. '/lk/0') }}" target="_blank">このグループに招待する</a></div>
                メンバー追加
            </div>
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
                                    <img src="{{ route('chat.photo',['id'=>$item->id]) }}" alt="{{ $item->name }}" width="20" />
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
    </div><!--/#open06-->

    <div class="modalBK"></div>
</div><!--/#modal-->

<div class="modal open08" id="open08">
    <div class="modalBody">
        <div class="modal-close">×</div>

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
                                        <img src="{{ route('chat.photo',['id'=>$item->user_id]) }}" alt="{{ $item->name }}" width="20" />
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
    </div><!--/#open08-->

    <div class="modalBK"></div>
</div><!--/#modal-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.3.4/socket.io.js"></script>

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

//        var $chat = $('ul.chat_box');
        var $chat = $('#chatBox');
        var $chatUsers = $('ul.chatUsers');

        var roomID = 'group_{{ $group_id }}';

        var date = new Date();
        var timeStamp = date.getTime() ;             // UNIXタイムスタンプを取得する (ミリ秒単位)
        timeStamp = Math.floor(timeStamp / 1000) ;  // UNIXタイムスタンプを取得する (秒単位 - PHPのtime()と同じ)

        // socket オープン 接続
//        var homeUrl = {{ url('/') }};
//        var url = homeUrl.replace('/shokunin', ':3002');
        var socket = new io.connect('http://tk2-224-21995.vs.sakura.ne.jp:3002', {
//        var socket = new io.connect(url, {
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

            if ($messageBox.val().length == 0 && $("input[name='file1']").val() === '') {
                return;
            }

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
                    console.log('url:' + '{{ url('/messages/gid/'. $group_id) }}');
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
            console.log('receive message : ' + data);

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
                        var url = '{{ route("chat.uploadImage", ["gid"=>$group_id, "filename"=>":filename", "extension"=>":extension"]) }}';
                        url = url.replace(':filename', type[0]);
                        url = url.replace(':extension', extension);

                        tmp  = tmp +
                            '<div class="upload_file">' +
                                '<div class="upload_file_title">☆ファイルをアップロードしました。</div>' +
                                '<div class="upload_file_file">' +
                                    '<img src="' + url + '" width="50" /><br />' +
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
                                    '<img src="<?= "route('chat.uploadImage', ['gid'=>$group_id, 'filename'=>'"; ?> + file_name + <?= "'])"; ?>/" width="50" /><br />';
                    }

                    strFile = strFile +
                                '<a href="<?= "route('chat.uploadImage', ['gid'=>$group_id, 'filename'=>'"; ?> + file_name + <?= "'])"; ?>/" target="_blank" style="color:#333;">' + file_name + '</a><br />' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    strMsg + '<br />';

                } else {
//                    strFile = strMsg
                    strFile = '';
                }

                var photoFile = '<img src="<?= asset('/photo/users/member-nophoto.png'); ?>" class="photoImg" />'
                if (data.id == {{ $from_user_id }}) {
<?php if ($file != '') { ?>
                    photoFile = '<img src="<?= route('chat.photo', ['id'=>$from_user_id]); ?>" class="photoImg" />';
<?php } ?>
<?php if (is_array($members) && !empty($members)) { ?>
<?php     foreach ($members as $item) { ?>
                } else if (data.id == {{ $item->user_id }}) {
                    if ('{{ $item->file }}' != '') {
                        photoFile = '<img src="<?= route('chat.photo', ['id'=>$item->user_id]); ?>" class="photoImg" />';
                    }
<?php     } ?>
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
                    var strHtml =
                    '<div class="chat-contants-wrap">' +
                        '<div class="talk-item-wrap clearfix">' +
                            '<div class="talk-user-item-wrap clearfix">' +
                                '<div class="talk-photo">' + photoFile + '</div>' +
                                '<div class="talk-name">' + data.nickname + '</div>' +
                                '<div class="talk-date">' + time_str + '</div>' +
                            '</div>' +
                            '<div class="icon-item-wrap talk-icon-item-wrap">' +
                                '<span class="response">返信</span>' +
                                '<span class="bookmark">ブックマーク</span>' +
                                '<span class="nice">いいね</span>' +
                                '<span class="edit">編集</span>' +
                                '<span class="dust">ごみ箱</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="talk-contents">' +
                            '<div>' +
                                strFile +
                                strMsg +
                            '</div>' +
                        '</div>' +
                    '</div>';

                    $chat.append(strHtml);

                    if (file_name != '') {
                        var img = new Image();
                        $('#wrap_' + message_id).css('display', 'none');

                        img.src = "<?= "route('chat.uploadImage', ['gid'=>$group_id, 'filename'=>'"; ?> + file_name + <?= "'])"; ?>/";
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
$(function(){
    $('#wd1').click(function(){
        wn = '.wd1';
        var mW = $(wn).find('.modalBody').innerWidth() / 2;
        var mH = $(wn).find('.modalBody').innerHeight() / 2;
        $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
        $(wn).fadeIn(100);
    });
    $('#open06').click(function(){
        wn = '.open06';
        var mW = $(wn).find('.modalBody').innerWidth() / 2;
        var mH = $(wn).find('.modalBody').innerHeight() / 2;
        $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
        $(wn).fadeIn(100);
    });
    $('#open08').click(function(){
        wn = '.open08';
        var mW = $(wn).find('.modalBody').innerWidth() / 2;
        var mH = $(wn).find('.modalBody').innerHeight() / 2;
        $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
        $(wn).fadeIn(100);
    });
    $('.modal-close,.modalBK').click(function(){
        $(wn).fadeOut(100);
    });
});

//Toですべて選択でテキストエリアに代入
function selectAll() {
    @if (is_array($members) && !empty($members))
    @foreach($members as $key => $item)
    selectTo({{ $item->user_id }}, '{{ $item->name }}');
    @endforeach
    @endif
}

// TO セット
function selectToChange() {
    target = document.getElementById("toMember");

    selectBox = document.input_form.toMember;

    num = selectBox.selectedIndex;
    id = selectBox.options[num].value;
    nm = selectBox.options[num].text;

    console.log(id + " : " + nm);

    if (id == 'all') {
        @if (is_array($members) && !empty($members))
            @foreach($members as $key => $item)
                selectTo({{ $item->user_id }}, '{{ $item->name }}');
            @endforeach
        @endif
    } else {
        var str = '[To:' + id + ']' + nm + "さん\n";

        var obj = $('#message-input');
        obj.focus();
        if(navigator.userAgent.match(/MSIE/)) {
            var r = document.selection.createRange();
            r.text = str;
            r.select();
        } else {
            var s = obj.val();
            var p = obj.get(0).selectionStart;
            var np = p + str.length;
            obj.val(s.substr(0, p) + str + s.substr(p));
            obj.get(0).setSelectionRange(np, np);
        }
    }
}

function searchUsers() {
    document.search_form.mode.value = 'search';
    document.search_form.submit();
}
function leaveGroup() {
    var msg = 'チャットグループから退席\n\n'
        + '本当に「{{ $groups->name }}」から退席しますか？\n\n'
        + '退席するとこのチャットの内容を閲覧できなくなります。\n'
        + '再度参加したい場合は参加メンバーーに招待してもらう必要があります。\n\n';

    if (confirm(msg) == false) {
        return;
    }

    document.leavegroup_form.action = "{{ url('/leavegroup') }}";
    document.leavegroup_form.submit();
}
function deleteGroup() {
    var msg = 'チャットグループ削除\n\n'
        + '本当に「{{ $groups->name }}」を削除しますか？\n\n'
        + '自分だけでなく、メンバー全員のチャット一覧から削除されます。\n'
        + 'すべてのメッセージ、タスク、ファイルのデータが削除されます。\n'
        + '削除されたデータは二度と元には戻すことはできません。\n\n';

    if (confirm(msg) == false) {
        return;
    }

    document.leavegroup_form.action = "{{ url('/deletegroup') }}";
    document.leavegroup_form.submit();
}

<?php if ($mode == 'search') { ?>
$(window).on('load', () => {
    $('#open06').trigger("click");
});
<?php } else if ($mode == 'delete_user') { ?>
window.onload = function() {
    location.href = "#open08";
}
<?php } ?>
<?php if ($errors->has('note') || $errors->has('limit_date') || $errors->has('member')) { ?>
$(window).on('load', () => {
    wn = '.open01';
    var mW = $(wn).find('.modalBody').innerWidth() / 2;
    var mH = $(wn).find('.modalBody').innerHeight() / 2;
    $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
    $(wn).fadeIn(100);
});
<?php } ?>
</script>

@endsection('footer')
