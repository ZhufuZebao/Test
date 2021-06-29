@extends('layouts.app')

@section('header')
<title>登録画面</title>
<script>
function submitPage() {
    document.input_form.submit();
}
</script>
@endsection

@section('content')

<!--container-->
<div class="container low user">
    <h1>会員情報の確認</h1>

    <dl>
        <dt>名前</dt>
        <dd>{{ $data['name'] }}</dd>

        <dt>ユーザID</dt>
        <dd>{{ $data['email'] }}<span class="small-text">（メールアドレスをユーザIDとして使用します）</span></dd>

        <dt>パスワード</dt>
        <dd>**********</dd>

        <dt>誕生日</dt>
        <dd>{{ $data['birth'] }}</dd>

        <dt>性別</dt>
        <dd>男</dd>

<?php /*
        <dt>メールアドレス</dt>
        <dd>{{ $data['mail1'] }}</dd>

        <dt>携帯メールアドレス</dt>
        <dd>{{ $data['mail2'] }}</dd>
*/ ?>
        <dt>住所</dt>
        <dd>@if ($data['zip']) 〒{{ $data['zip'] }} @endif @if ($data['pref'] != '') {{ $prefs[$data['pref']] }} @endif {{ $data['addr'] }}</dd>

        <dt>電話番号</dt>
        <dd>{{ $data['telno1'] }}</dd>

        <dt>携帯電話番号</dt>
        <dd>{{ $data['telno2'] }}</dd>

        <dt>会社名・団体名</dt>
        <dd>{{ $data['comp'] }}</dd>

        <dt>部署名・役職</dt>
        <dd>{{ $data['class'] }}</dd>

@if ($data['file'] != '')
        <dt>写真</dt>
        <dd><img src="{{ asset('/photo/users/'. $data['file']) }}?t={{ time() }}" height="100" /></dd>
@endif

    </dl>

    <div class="button gray pencil"><a href="{{ url('/usermodify') }}">変更</a></div>
    <div class="button green"><a href="#open07">写真をアップロード</a></div>
</div>
<!--/container-->

<div id="modal">
    <div id="open07">
        <form class="form-horizontal" name="upload_form" role="form" method="POST" action="{{ url('/userupload') }}" enctype="multipart/form-data">
        {{ csrf_field() }}

        <a href="#" class="close_overlay">close</a>

        <div class="modal_window">
            <div class="close"><a href="#">×</a></div>

            <div class="modal-content clearfix">
                <div class="title">写真をアップロード</div>
                <ul>
                    <li>
@if (file_exists('/var/www/html/shokunin/public/photo/users/'. $id))
                        <img src="{{ asset('/photo/users/'. $id) }}.jpg" width="50" />
@endif
                        <div class="imgInput" style="height:120px;">
                            <div class="file2" style="float:left;">
                                ファイルを選択
                                <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                                <input type="file" name="file" id="file" multiple="multiple" />
                            </div>
                            <div style="float:left;">
                                <img src="{{ asset('/images/noimage.png') }}" alt="" class="imgView2" id="imgViewArea">
                            </div>
                        </div><!--/.imgInput-->
                    </li>
                </ul>

                <div class="clearfix"></div>
                <div class="button green" id="save" style="display:block;"><a href="javascript:document.upload_form.submit();">アップロード</a></div>
            </div>
        </div><!--/.modal_window-->

        </form>
    </div><!--/#open07-->

</div><!--/#modal-->

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
$(function(){
    var setFileInput = $('.imgInput'),
    setFileImg = $('.imgView2');

    setFileInput.each(function(){
        var selfFile = $(this),
        selfInput = $(this).find('input[type=file]'),
        prevElm = selfFile.find(setFileImg),
        orgPass = prevElm.attr('src');

        selfInput.change(function(){
            var file = $(this).prop('files')[0],
            fileRdr = new FileReader();

            document.getElementById('imgViewArea').style.display = 'block';

            if (!this.files.length){
                prevElm.attr('src', orgPass);
                return;
            } else {
                if (!file.type.match('image.*')){
                    prevElm.attr('src', orgPass);
                    return;
                } else {
                    fileRdr.onload = function() {
                        prevElm.attr('src', fileRdr.result);
                    }
                    fileRdr.readAsDataURL(file);
                }
            }
        });
    });
});
</script>

@endsection

