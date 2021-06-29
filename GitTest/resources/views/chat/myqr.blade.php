<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>SITE</title>
    <meta name="viewport" content="width=480">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

<script>
$(function(){

    var ua = navigator.userAgent.toLowerCase();
    var isAndroid = /android/.test(ua);
    var isChrome = /chrome/.test(ua);

    var package_name = "site.conit.chat";
    var event_id = "<?= $id ?>";

    if (isAndroid) {
        if (isChrome) {
            window.location.href = 'intent://androidchat/' + event_id + '#Intent;scheme=myqr;package=' + package_name + ';end';
            //window.location.href = 'intent://androidchat#Intent;scheme=myqr;package=jp.co.technocrea.shokuninchat;end';
        } else {
            var iframe = document.createElement('iframe');
            iframe.className = 'is-hidden';
            iframe.onload= function() {
                iframe.parentNode.removeChild(iframe);
                window.location.href = "http://play.google.com/store/apps/details?id=" + package_name;
            };
            //iframe.src = 'androidchat://event/' + event_id;
            iframe.src = 'androidchat://event/' + event_id;
            document.body.appendChild(iframe);
        }
    }
  });
</script>

</head>
<body>

</body>
</html>
