@extends('layouts.app')
<title>チャット</title>

@section('header')
<?php /*
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/css/bootstrap.min.css">

    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
    <link href="{{ asset('/css/chat.css') }}" rel="stylesheet">
*/ ?>

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
<div class="container low chat member">
    <h1>チャット</h1>

    <ul class="chat-nav clearfix">
        <li class="current"><a href="{{ url('/chat') }}"></a></li>
    @if ($lastDirectId != null)
        <li<?= $directUnread ? ' class="new"' : '' ?>><a href="{{ url('/chatstart/gid/'. $lastDirectId) }}"></a></li>
    @else
        <li><a href="{{ url('/chat') }}"></a></li>
    @endif
    @if ($lastGroupId != null)
        <li<?= $groupUnread ? ' class="new"' : '' ?>><a href="{{ url('/chatstart/gid/'. $lastGroupId) }}"></a></li>
    @else
        <li><a href="{{ url('/chat') }}"></a></li>
    @endif
        <li><a href="{{ url('/chatnews') }}"></a></li>
        <li><a href="{{ url('/chatsetting') }}"></a></li>
    </ul>

    @if (isset($uncontacts) && is_array($uncontacts) && !empty($uncontacts))
    <div class="searchmember">
        未承認<br />
        @foreach($uncontacts as $item)
        <a href="#open05" onClick="agreeSend({{ $item->id }}, '{{ $item->name }}')">
        <ul>
            <li><img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" height="40" /></li>
            <li><span class="from_user_name">{{ $item->name }}</span></li>
        </ul>
        </a>
        @endforeach
    </div>
    @endif

    @if (isset($contacts) && is_array($contacts) && !empty($contacts))
    <div class="searchmember">
        チャットメンバー<br />
        @foreach($contacts as $item)
        <a href="{{ url('/chatstart/gid/'. $item->group_id) }}">
        <ul>
@if ($item->file != '')
            <li><img src="{{ asset('/photo/users/'. $item->file) }}" alt="{{ $item->name }}" height="40" /></li>
@else
            <li><img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" height="40" /></li>
@endif
            <li><span class="from_user_name">{{ $item->name }}<?= $item->unread ? '<span class="unread"> (未読あり)</span>' : ''; ?></span></li>
        </ul>
        </a>
        @endforeach
    </div>
    @endif

    @if (isset($groups) && is_array($groups) && !empty($groups))
    <div class="searchmember">
        チャットグループ<br />
        @foreach($groups as $item)
        <a href="{{ url('/chatstart/gid/'. $item->group_id) }}">
        <ul>
            <li class="li_1"><img src="{{ asset('/photo/groups/group-nophoto.png') }}" alt="{{ $item->group_name }}" height="40" /></li>
            <li class="li_2"><span class="from_user_name">{{ $item->group_name }}<?= $item->unread ? '<span class="unread"> (未読あり)</span>' : ''; ?></span></li>
        </ul>
        </a>
        @endforeach
    </div>
    @endif

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/chat') }}">
    {{ csrf_field() }}

    <div class="search">
        <input type="text" name="keyword" id="keyword" value="{{ old('keyword') }}" placeholder="チャットしたい人の名前">
        <div class="button gray"><a href="javascript:document.input_form.submit();">検索</a></div>
    </div>

    </form>

    <div class="searchmember">
    @if (isset($result) && is_array($result))
        @foreach($result as $item)
        <a href="#open04" onClick="contactSend({{ $item->id }}, '{{ $item->name }}')">
        <ul>
            <li><img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" width="40" /></li>
            <li><span class="from_user_name">{{ $item->name }}</span></li>
        </ul>
        </a>
        @endforeach
    @endif
    </div>

</div>

<div id="modal">
    <div id="open04">
        <form class="form-horizontal" name="send_form" role="form" method="POST" action="{{ url('/chatcontact') }}">
        {{ csrf_field() }}
        <input type="hidden" name="to_user_id1" id="to_user_id1" value="">

        <a href="#" class="close_overlay">close</a>

        <div class="modal_window">
            <div class="close"><a href="#">×</a></div>

            <div class="modal-content clearfix">
                <ul>
                    <li>
<div class="title">コンタクト承認依頼</div>
<br />
<span id="user_name1">〇〇</span>さんにコンタクトの承認依頼を送信しますか？<br />
※メールが同時に送信されます。<br />
<br />
承認依頼にメッセージを添えることもできます。
                    </li>
                    <li>
                        <textarea name="message1" rows="5" cols="100" placeholder="メッセージを入力（任意）"></textarea>
                    </li>
                </ul>

                <div class="clearfix"></div>
                <div class="button green" id="save" style="display:block;"><a href="javascript:document.send_form.submit();">送信</a></div>
            </div>
        </div><!--/.modal_window-->

        </form>
    </div><!--/#open04-->

</div><!--/#modal-->

<div id="modal">
    <div id="open05">
        <form class="form-horizontal" name="agree_form" role="form" method="POST" action="{{ url('/chatagree') }}">
        {{ csrf_field() }}
        <input type="hidden" name="from_user_id" id="from_user_id" value="">

        <a href="#" class="close_overlay">close</a>

        <div class="modal_window">
            <div class="close"><a href="#">×</a></div>

            <div class="modal-content clearfix">
                <ul>
                    <li>
<div class="title">コンタクト承認</div>
<br />
<span id="user_name2">〇〇</span>さんにコンタクトの承認を送信しますか？<br />
※メールが同時に送信されます。<br />
<br />
承認にメッセージを添えることもできます。
                    </li>
                    <li>
                        <textarea name="message2" rows="5" cols="100" placeholder="メッセージを入力（任意）"></textarea>
                    </li>
                </ul>

                <div class="clearfix"></div>
                <div class="button green" id="save" style="display:block;"><a href="javascript:document.agree_form.submit();">送信</a></div>
            </div>
        </div><!--/.modal_window-->

        </form>
    </div><!--/#open05-->

</div><!--/#modal-->

<?php /*
<form action="{{ url('/api/member') }}" method="post">
 {{ csrf_field() }}
        <input type="submit" value="send">
</form>
*/ ?>

@endsection

@section('footer2')
<script>
function contactSend(user_id, user_name) {
    document.getElementById('to_user_id1').value = user_id;
    document.getElementById('user_name1').innerHTML = user_name;
}
function agreeSend(user_id, user_name) {
    document.getElementById('from_user_id').value = user_id;
    document.getElementById('user_name2').innerHTML = user_name;
}
</script>
@endsection
