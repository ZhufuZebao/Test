<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>SITE</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script>
$(function(){

    var ua = navigator.userAgent.toLowerCase();
    var isAndroid = /android/.test(ua);
    var isChrome = /chrome/.test(ua);

    var chat_package_name = "site.conit.chat";
    var schedule_package_name = "site.conit.schedule";
    var event_id = "<?= $id ?>";
    var kind = "<?= $kind ?>";
    var package_name;
    (kind == "chat")?package_name = chat_package_name:package_name = schedule_package_name;
    var scheme;
    (kind == "chat")?scheme = "myqr":scheme = "contact";
    var host;
    (kind == "chat")?host = "androidchat":host = "siteschedule";

    if (isAndroid) {
        if (isChrome) {
            window.location.href = "intent://"+host+"/param?user_id=<?=$id?>#Intent;scheme="+scheme+";package="+package_name+";end";
        } else {
            var iframe = document.createElement('iframe');
            iframe.className = 'is-hidden';
            iframe.onload= function() {
                iframe.parentNode.removeChild(iframe);
                window.location.href = "http://play.google.com/store/apps/details?id=" + package_name;
            };
            //iframe.src = 'androidchat://event/' + event_id;
            iframe.src = scheme+"://"+host+"/param?user_id=<?=$id?>"
            document.body.appendChild(iframe);
        }
        $("#link_text a").attr("href", scheme+"://"+host+"/param?user_id=<?=$id?>")
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
