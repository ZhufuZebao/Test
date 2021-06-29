@php
$bodyStyle = 'low chat';
@endphp
@extends('layouts.app')

@section('header')
    <link href="//fonts.googleapis.com/css?family=Roboto:400,300" rel="stylesheet">
    <link href="{{ asset('/css/chat.css') }}" rel="stylesheet">

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
                <div class="setting-title">チャット設定</div>
            </div>
        </header>

        <!--friend-wrapper-->
        <section class="chat-wrapper">

            <div class="setting-box">
            
                <p><span class="btns" date-tgt="open04" id="open04">■ コンタクトを追加</p>
                <p><span class="btns" date-tgt="open06" id="open06">■ チャットグループを新規作成</span></p>
                <br />

                <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/chatsetting') }}">
                {{ csrf_field() }}

                <div class="search">
                    <p>
                        <div class="subTitle">■ ユーザー検索</div>
                        <input type="text" name="keyword" id="keyword" value="{{ old('keyword') }}" placeholder="チャットしたい人の名前"><br />
                        <div class="button gray"><a href="javascript:document.input_form.submit();">検索</a></div>
                    </p>
                </div>

                <div class="searchmember">
                @if (isset($result) && is_array($result))
                    @foreach($result as $item)
                    <a href="#open04" onClick="contactSend({{ $item->id }}, '{{ $item->name }}')">
                    <ul>
                        <li><img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" width="40" /></li>
                        <li><span class="btns from_user_name" date-tgt="open05" id="open05">{{ $item->name }}</span></li>
                    </ul>
                    </a>
                    @endforeach
                @endif
                </div>
                <br clear="all" />

                </form>

                @if (isset($uncontacts) && is_array($uncontacts) && !empty($uncontacts))
                <div class="searchmember">
                    <div class="subTitle">■ 未承認</div>
                    @foreach($uncontacts as $item)
                    <a href="#open03" onClick="agreeSend({{ $item->id }}, '{{ $item->name }}')">
                    <ul>
                        <li><img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" /></li>
                        <li><span class="btns from_user_name" date-tgt="open03" id="open03">{{ $item->name }}</span></li>
                    </ul>
                    </a>
                    @endforeach
                </div>
                @endif
                <br clear="all" />


                <form class="form-horizontal" name="setting_form" role="form" method="POST" action="{{ url('/chatregistsetting') }}">
                    {{ csrf_field() }}
                    <p>
                        <div class="subTitle">■ メッセージが届いた時の音設定</div>
                        <p>
                            <label for="sound-1"><input type="radio" name="sound" id="sound-1" value="1"{{ $sound == 1 ? ' checked' : '' }} />すべて音を出す</label>
                            <label for="sound-2"><input type="radio" name="sound" id="sound-2" value="2"{{ $sound == 2 ? ' checked' : '' }} />自分宛ての時だけ音を出す</label>
                            <label for="sound-3"><input type="radio" name="sound" id="sound-3" value="3"{{ $sound == 3 ? ' checked' : '' }} />音を出さない</label>
                        </p>
                    </p>

                    <br clear="all" />
                    <div class="button green" id="save" style="display:block;"><a href="javascript:document.setting_form.submit();">設定</a></div>
                </form>

            </div>


            <div style="margin:30px; padding:10px; line-height:30px; border:3px dotted gray;">
                ■仮設置<br />
                <a href="{{ asset('/apk/app-ChatAppli.apk') }}">チャット（Androidアプリ）ダウンロード</a><br />
                <a href="{{ asset('/apk/app-schedule.apk') }}">スケージュール管理（Androidアプリ）ダウンロード</a><br />
            </div>

        </section>

@endsection('content')

@section('footer')

<div class="modal open03" id="open03">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form class="form-horizontal" name="agree_form" role="form" method="POST" action="{{ url('/chatagree') }}">
        {{ csrf_field() }}
        <input type="hidden" name="from_user_id" id="from_user_id" value="">

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

        </form>
    </div><!--/#open03-->

    <div class="modalBK"></div>
</div><!--/#modal-->

<div class="modal open04" id="open04">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form class="form-horizontal" name="send_form" role="form" method="POST" action="{{ url('/chatcontactadd') }}">
        {{ csrf_field() }}
        <input type="hidden" name="to_user_id" id="to_user_id" value="">

        <div class="modal-content clearfix">
            <ul>
                <li>
                    <div class="title">コンタクト追加</div>
                </li>
                <li>
                    メールアドレス
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="例）abcde@fghij.co.jp" /><br />

                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
                </li>
                <li>
                    <textarea name="message1" rows="5" cols="100" placeholder="メッセージを入力（任意）"></textarea>
                </li>
            </ul>

            <div class="clearfix"></div>
            <div class="button green" id="save" style="display:block;"><a href="javascript:document.send_form.submit();">招待メールを送信</a></div>
        </div>

        </form>
    </div><!--/#open04-->

    <div class="modalBK"></div>
</div><!--/#modal-->

<div class="modal open05" id="open05">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form class="form-horizontal" name="send_form2" role="form" method="POST" action="{{ url('/chatcontact') }}">
        {{ csrf_field() }}
        <input type="hidden" name="to_user_id1" id="to_user_id1" value="">


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
            <div class="button green" id="save" style="display:block;"><a href="javascript:document.send_form2.submit();">送信</a></div>
        </div>

        </form>
    </div><!--/#open05-->

    <div class="modalBK"></div>
</div><!--/#modal-->

<div class="modal open06" id="open06">
    <div class="modalBody">
        <div class="modal-close">×</div>

        <form class="form-horizontal" name="group_form" role="form" method="POST" action="{{ url('/chatgroupadd') }}">
        {{ csrf_field() }}

        <div class="modal-content clearfix">
            <div class="title">グループチャットを新規作成</div>

            <dl class="modal-content clearfix">
                <dt>グループ名</dt>
                <dd><input type="text" name="group_name" id="group_name" value="" placeholder="" /></dd>
                <dt>メンバー</dt>
                <dd>
                    <div class="allcheck">
                        <label for="allcheck"><input type="checkbox" id="allcheck" />すべて選択</label>
                    </div>

                    @if (isset($others) && is_array($others) && !empty($others))
                    <div class="chatmember" id="chatmember">
                        @foreach($others as $item)
                        <label for="member_{{ $item->id }}">
                            <div>
                                <input type="checkbox" name="member[{{ $item->id }}]" id="member_{{ $item->id }}" class="member_check" />
                                @if ($item->file != '')
                                <img src="{{ asset('/photo/users/' . $item->file) }}" alt="{{ $item->name }}" />
                                @else
                                <img src="{{ asset('/photo/users/member-nophoto.png') }}" alt="{{ $item->name }}" />
                                @endif
                                <span class="from_user_name">{{ $item->name }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </dd>
            </dl>

            <div class="clearfix"></div>
            <div class="button green" id="save" style="display:block;"><a href="javascript:document.group_form.submit();">作成</a></div>
        </div>

        </form>
    </div><!--/#open06-->

    <div class="modalBK"></div>
</div><!--/#modal-->

@endsection('footer')

@section('footer2')
<script>
/*
$(function(){
    $('.btns').click(function(){
        wn = '.' + $(this).attr('date-tgt');
        var mW = $(wn).find('.modalBody').innerWidth() / 2;
        var mH = $(wn).find('.modalBody').innerHeight() / 2;
        $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
        $(wn).fadeIn(100);
    });
    $('.modal-close,.modalBK').click(function(){
        $(wn).fadeOut(100);
    });
});
*/
$(function(){
    $('#open03').click(function(){
        wn = '.open03';
        var mW = $(wn).find('.modalBody').innerWidth() / 2;
        var mH = $(wn).find('.modalBody').innerHeight() / 2;
        $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
        $(wn).fadeIn(100);
    });
    $('#open04').click(function(){
        wn = '.open04';
        var mW = $(wn).find('.modalBody').innerWidth() / 2;
        var mH = $(wn).find('.modalBody').innerHeight() / 2;
        $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
        $(wn).fadeIn(100);
    });
    $('#open05').click(function(){
        wn = '.open05';
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
    $('.modal-close,.modalBK').click(function(){
        $(wn).fadeOut(100);
    });
});

</script>
<script>
$(function() {

    $('#allcheck').on('click', function() {
        $('.member_check').prop('checked', this.checked);
    });

    $('.member_check').on('click', function() {
        if ($('#chatmember :checked').length == $('#chatmember :input').length){
            $('#allcheck').prop('checked', 'checked');
        }else{
            $('#allcheck').prop('checked', false);
        }
    });
});

function contactSend(user_id, user_name) {
    document.getElementById('to_user_id1').value = user_id;
    document.getElementById('user_name1').innerHTML = user_name;
}
function agreeSend(user_id, user_name) {
    document.getElementById('from_user_id').value = user_id;
    document.getElementById('user_name2').innerHTML = user_name;
}

<?php if ($errors->has('email')) { ?>
$(window).on('load', () => {
    $('#open04').trigger("click");
});
<?php } ?>
</script>
@endsection
