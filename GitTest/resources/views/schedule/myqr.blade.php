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

    var package_name = "site.conit.schedule";
    var event_id = "<?= $id ?>";
    var scheme = "contact";
    var host = "siteschedule";

    if (isAndroid) {
        if (isChrome) {
            window.location.href = "intent://"+host+"/" + event_id + "#Intent;scheme="+scheme+";package=" + package_name + ';end';
            //window.location.href = 'intent://androidchat#Intent;scheme=myqr;package=jp.co.technocrea.shokuninchat;end';
        } else {
            var iframe = document.createElement('iframe');
            iframe.className = 'is-hidden';
            iframe.onload= function() {
                iframe.parentNode.removeChild(iframe);
                window.location.href = "http://play.google.com/store/apps/details?id=" + package_name;
            };
            //iframe.src = 'androidchat://event/' + event_id;
            iframe.src = scheme+"://"+host+"/" + event_id;
            document.body.appendChild(iframe);
        }
        $("#link_text a").attr("href", scheme+"://"+host+"/" + event_id);
    }
  });
</script>

</head>
<body>
    <div id="link_text">
        <a href="">自動的に遷移しない場合はこちら</a>
    </div>
</body>
</html>
