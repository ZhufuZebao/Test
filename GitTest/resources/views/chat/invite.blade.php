<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/chat.css') }}">
    <title>グループチャットに招待する</title>

   <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<?php /*
<script src="https://cdnjs.cloudflare.com/ajax/libs/zeroclipboard/2.2.0/ZeroClipboard.min.js"></script>
*/ ?>
<script src="{{ asset('/js/ZeroClipboard.js') }}"></script>

</head>

<body>

<div class="wrapper low chat invite" id="top">

@if ($link == '0')
    <div class="invite-box">
        <div class="title">招待したい人にリンクを共有してください</div>

        <p>グループチャットに招待したい人に以下のリンク（URL）を伝えることで、<br />簡単にグループチャットのメンバーに追加することができます。</p>

        <input type="text" class="js_invitation_link" id="url" value="" />
        <div class="js_invitation_link_copy_button button" data-clipboard-text="">コピー</div>
        <br clear="all" />

        <script type="text/javascript"><!--
        $(function(){
            var url = location.href + '1';
            var invitation_link = $('.js_invitation_link');
            var copy_button = $('.js_invitation_link_copy_button');
            var swf_path = '/js/ZeroClipboard.swf';
            var text_copied = 'コピーしました';

            ZeroClipboard.config({moviePath: swf_path});
            var clip = new ZeroClipboard(copy_button);

            invitation_link.attr('value', url);
            copy_button.attr('data-clipboard-text', url);

            invitation_link.click(function(){
                this.select();
            })[0].select();

            clip.on('complete', function(){
                $(this).text(text_copied);
            });
        });
        //--></script>

        <span style="color:red;">チャットワークアカウントを持っていない方もこのリンクから登録可能です</span><br />
    </div>

    <div class="invite-box">
        <ul>
            <li class="group-img"><img src="{{ asset('/photo/groups/group-nophoto.png') }}" alt="グループチャット画像" width="30" /></li>
            <li class="group-name">{{ $group->name }}</li>
        </ul>
        <br clear="all" />

        <div class="button" data-clipboard-text=""><a href="{{ url('/chat/'. $group->id) }}">グループチャットを開く</a></div>

    </div>
@else
    <div class="invite-box">
        <ul>
            <li class="group-img"><img src="/photo/groups/group-nophoto.png" alt="グループチャット画像" width="30" /></li>
            <li class="group-name">{{ $group->name }}</li>
        </ul>
    </div>

    <div class="invite-box">
        <div class="title">このサイトのアカウントをお持ちの方</div>

        <p>下記ログインボタンよりログインいただき、<br />トップページのチャットへお進みください。</p>

        <div class="button2" data-clipboard-text=""><a href="{{ url('/login') }}">ログイン</a></div>

    </div>

    <div class="invite-box">
        <div class="title">このサイトのアカウントをお持ちでない方</div>

        <p>
            下記より登録可能です。（無料でご利用いただけます）<br />
            登録完了後、招待を受けているグループチャットに参加できます。
        </p>

        <div class="button3" data-clipboard-text=""><a href="{{ url('/register') }}">今すぐ無料で利用する</a></div>

    </div>
@endif

</div>

</body>
</html>