<!DOCTYPE html>
<html lang="ja">
<style>
body {
    background-color: #1da6d0;
}
div.box {
    margin:20px auto;
    padding:20px;
    width:90%;
    background-color: rgba(255,255,255,0.7);
    border-radius: 4px;
    text-align:center;
}
h1 {
    font-size: 16px;
    color: #000;
    line-height:30px;
}
h2 {
    font-size: 14px;
    color: #000;
    font-weight: bold;
    text-align:left;
    padding-left:20px;
}
div.message {
    margin:10px;
    padding:10px;
    border: 1px solid #2a88bd;
    background-color: #ffffff;
    border-radius: 4px;
    font-size: 14px;
    text-align:left;
}
#button {
    margin:10px auto;
    width: 200px;
    text-align: center;
}
#button a {
    padding: 10px 20px;
    display: block;
    border: 1px solid #2a88bd;
    background-color: #FFFFFF;
    color: #2a88bd;
    text-decoration: none;
    box-shadow: 2px 2px 3px #f5deb3;
}
#button a:hover {
    background-color: #2a88bd;
    color: #FFFFFF;
}
</style>
<body>

<div class="box">
    <h1>{{ $subject }}</h1>
    <p>
        <h2>承認依頼メッセージ</h2>

        <div class="message">{{ $text }}</div>

        コンタクトの承認は、ログイン後<br />
        おこなってください。<br />
    <?php /*
        「コンタクト管理」の「未承認」よりおこなってください。<br />
    */ ?>
    </p>
    <p id="button">
      <a href="{{ url('/chat') }}">チャット</a>
    </p>

    <p><small>※このメールアドレスは送信専用です。返信しても依頼者へは送信されませんのでご注意ください。</small></p>
</div>

</body>
</html>