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

                <div style="float:left; margin:10px 0 0 20px;">タスク一覧</div>

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

            <form id="send-message" name="input_form" method="POST" action="{{ url('/savetask') }}">
                {{ csrf_field() }}
                <input id="csrfToken" type="hidden" name="csrf-token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="group_id" value="{{ $group_id }}" />
                <input type="hidden" name="task_id" value="" />

            <div id="chatBox">
        @if (is_array($list) && !empty($list))

            @foreach($list as $message)

            <?php $msg = $message->note; ?>

            @if (isset($message) && isset($message->id))
                <input type="hidden" name="chatid_{{ $message->id }}" value="{{ $message->id }}" />
                <textarea name="chatmsg_{{ $message->id }}" id="chatmsg_{{ $message->id }}" style="display:none;">{{ $msg }}</textarea>
            @endif

                <div class="chat-contants-wrap" id="message_area_{{ $message->id }}">
                    <div class="talk-item-wrap clearfix">
                        <div class="talk-user-item-wrap clearfix">
                            <div class="talk-photo">
                            @if ($message->user_file)
                                <img src="{{ route('chat.photo',['id'=>$message->create_user_id]) }}" class="photoImg" />
                            @else
                                <img src="{{ asset('/photo/users/member-nophoto.png') }}" class="photoImg" />
                            @endif
                            </div>
                            <div class="talk-name">{{ $message->create_user_name }}</div>
                            <div class="talk-date">{{ $message->created_at }}</div>
                        </div>

                        <div class="icon-item-wrap talk-icon-item-wrap">
                            <span class="edit" onclick="editMessage({{ $message->id }});">編集</span>
                            <span class="dust" id="deleteMessage_{{ $message->id }}">ごみ箱</span>
                            <span class="complete" id="completeTask_{{ $message->id }}">完了</span>
                        </div>
                    </div>

                    <div class="talk-contents">
                        <div>
                            <?= showHtmlChatMessage($msg, $dir); ?>
                        </div>
                    </div>
                </div>

            @endforeach
        @endif
            </div>

            <div id="send-message-area"></div>

                <div class="chat-contants-wrap add-chat-contants-wrap">
                    <div class="talk-item-wrap clearfix">

                        <div style="float:left;">
                            新規タスク登録
                        </div>

                        <div style="width:300px;">
                            <div class="chat task_member">
                                <div class="searchmember" id="search_member2">
                                    @if (isset($members2) && is_array($members2) && !empty($members2))
                                        @foreach($members2 as $item)
                                        <label for="member_{{ $item->id }}">
                                            <div>
                                                <input type="checkbox" name="member[{{ $item->id }}]" id="member_{{ $item->id }}" value="1" class="member_check" />
                                                @if ($item->file != '')
                                                <img src="{{ asset('/photo/users/'. $item->file) }}" alt="{{ $item->name }}" width="18" />
                                                @else
                                                <img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" width="18" />
                                                @endif
                                                <span class="from_user_name" id="memberName_{{ $item->id }}">{{ $item->name }}</span>
                                            </div>
                                        </label>
                                        @if ($errors->has('member'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('member') }}</strong>
                                            </span>
                                        @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
<?php /*
                            <select id="member">
                                <option value="all">すべて選択</option>
                            @if (is_array($members2) && !empty($members2))
                                @foreach ($members2 as $key => $item)
                                <option value="{{ $item->id }}"{{ ($item->id == $user_id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            @endif
                            </select>
*/ ?>
                        </div>

                        <div style="float:left;">
                            有効期限：<input type="text" name="limit_date" id="limit_date" placeholder="例）2018/10/02" value="{{ date('Y/m/d') }}" />
                            @if ($errors->has('limit_date'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('limit_date') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="submit2"><button type="submit" class="submit" id="btn-chat">送信</button></div>
                    </div>

                    <div class="talk-contents">
                        <div id="edit_message" class="edit-message" style="display:none;">
                            <div class="close_b"><a href="javascript:closeEdit()">×</a></div>
                            編集中です...
                        </div>
                        <textarea name="note" id="message-input" placeholder="ここにメッセージを入力して下さい..." style="ime-mode:active;"></textarea>
                    @if ($errors->has('note'))
                        <span class="help-block">
                            <strong>{{ $errors->first('note') }}</strong>
                        </span>
                    @endif
                    </div>
                </div>

        </section>
        <!--/friend-wrapper-->

        </form>
@endsection('content')

@section('footer')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.3.4/socket.io.js"></script>

<script src="{{ asset('/js/chatFunction.js') }}"></script>

<script>
var edit_message_id = 0;

    // 日時を0詰め
    var toDoubleDigits = function(num) {
        num += "";
        if (num.length === 1) {
            num = "0" + num;
        }
        return num;
    };

    jQuery(function($) {
        var $messageForm = $('#send-message');
        var $messageBox = $('#message-input');
        var $limitDate = $('#limit_date');

        var $chat = $('#chatBox');
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

            if ($messageBox.val().length == 0) {
                alert("タスクの内容を入力してください。");
                return;
            }
            if ($limitDate.val().length == 0) {
                alert("有効期限を入力してください。");
                return;
            }

            var file = null;
            var file_nm = null;

            console.log('{{ Auth::user()->id }}');

            var msg = '■タスクを追加しました。\n'
                + $messageBox.val();

            socket.emit('chat.send.message', {
                msg: msg,
                nickname: '{{ Auth::user()->name }}',
                id: '{{ Auth::user()->id }}',
                mid: edit_message_id,
                file: file_nm,
                timeStamp: timeStamp,
                maxid: ''
            });

//            var members = $('input[name="member[]"]').map(function(){
//                return this.value;
//            }).get();

//alert(edit_message_id);
            if (edit_message_id > 0) {
                document.input_form.task_id.value = edit_message_id;
                document.input_form.action = '{{ url('/chattaskupdate') }}';
            }
            document.input_form.submit();

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

        // 削除クリックでメッセージを削除して、他のユーザーにも知らせる
        $('#chatBox').on('click', function (e) {
            var id = e.target.id;

             if (id.indexOf('deleteMessage') != -1) {

                 if (confirm('削除します。よろしいですか？') == false) {
                     return;
                 }

                 var idTmp = id.split("_");
                 var task_id = idTmp[1];

                 /*
                 var data = new FormData();
                 data.append("task_id", task_id);

                 $.ajaxSetup({
                     headers: {
                       'X-CSRF-TOKEN': $('#csrfToken').val()
                     }
                 });

                 console.log(data);

                 // DBから削除
                 $.ajax({
                     type: 'POST',
                     url: "{{ url('/chattaskdelete') }}",
                     data: data,

                     dataType : "text",

                     processData : false,
                     contentType : false,

                     complete: function (result) {
                         console.log(result);
                     }
                 });

                 location.reload();
*/

                 var msg = '■タスクを削除しました。\n'
                        + document.getElementById('chatmsg_' + task_id).value

                 socket.emit('chat.send.message', {
                     msg: msg,
                     nickname: '{{ Auth::user()->name }}',
                     id: '{{ Auth::user()->id }}',
                     mid: '',
                     file: '',
                     timeStamp: '',
                     maxid: ''
                 });

                 document.input_form.task_id.value = task_id;
                 document.input_form.action = '{{ url('/chattaskdelete') }}';
                 document.input_form.submit();
             }


             if (id.indexOf('completeTask') != -1) {

                 if (confirm('完了します。よろしいですか？') == false) {
                     return;
                 }

                 var idTmp = id.split("_");
                 var task_id = idTmp[1];

                var msg = document.getElementById('chatmsg_' + task_id).value;


                 var msg2 = '■タスクを完了しました。\n'+ msg;

                 socket.emit('chat.send.message', {
                     msg: msg2,
                     nickname: '{{ Auth::user()->name }}',
                     id: '{{ Auth::user()->id }}',
                     mid: '',
                     file: '',
                     timeStamp: '',
                     maxid: ''
                 });

                 document.input_form.task_id.value = task_id;
                 document.input_form.note.value = msg;
                 document.input_form.action = '{{ url('/chattaskcomplete') }}';
                 document.input_form.submit();
             }

        });
    });

</script>

@endsection('footer')
