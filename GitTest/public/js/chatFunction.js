/*
// メッセージ入力欄の高さを自動調整
$("#message-input").height(100);//init
$("#message-input").css("lineHeight","20px");//init

$("#message-input").on("input", function(evt){
    if(evt.target.scrollHeight > evt.target.offsetHeight){
        $(evt.target).height(evt.target.scrollHeight);
    }else{
        var lineHeight = Number($(evt.target).css("lineHeight").split("px")[0]);
        while (true){
            $(evt.target).height($(evt.target).height() - lineHeight);
            if(evt.target.scrollHeight > evt.target.offsetHeight){
                $(evt.target).height(evt.target.scrollHeight);
                break;
            }
        }
    }
});
*/
// タイムスタンプの日時を文字列に変換
function timestampToString(tmsp) {

    var d = new Date( tmsp * 1000 );

    var year  = d.getFullYear();

    var month = d.getMonth() + 1;
    month = (month < 10) ? '0' + month : month;

    var day   = d.getDate();
    day = (day < 10) ? '0' + day : day;

    var hour  = ( d.getHours()   < 10 ) ? '0' + d.getHours()   : d.getHours();
    var min   = ( d.getMinutes() < 10 ) ? '0' + d.getMinutes() : d.getMinutes();
    var sec   = ( d.getSeconds() < 10 ) ? '0' + d.getSeconds() : d.getSeconds();

    var str = year + '-' + month + '-' + day + ' ' + hour + ':' + min + ':' + sec;

    return str;
}

// 置換用の文字列をエスケープ
function preg_quote (str, delimiter) {
    // *     example 1: preg_quote("$40");
    // *     returns 1: '\$40'
    // *     example 2: preg_quote("*RRRING* Hello?");
    // *     returns 2: '\*RRRING\* Hello\?'
    // *     example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
    // *     returns 3: '\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:'
    return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
}

// 絵文字マーククリック（絵文字が選択できる枠表示）
function miconClick() {
    if (document.getElementById('balloon').style.display == 'block') {
        document.getElementById('balloon').style.display = 'none';
    } else {
        document.getElementById('balloon').style.display = 'block';
    }
}

// Toマーククリック（To のリスト表示）
function micon2Click() {
    if (document.getElementById('balloon2').style.display == 'block') {
        document.getElementById('balloon2').style.display = 'none';
    } else {
        document.getElementById('balloon2').style.display = 'block';
    }
}

// 絵文字選択でテキストエリアに代入
function miconSelect(no) {
    var str = '[icon:' + no + ']';

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

    document.getElementById('balloon').style.display = 'none';
}

// To選択でテキストエリアに代入
function selectTo(id, nm) {
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

//    document.getElementById('balloon2').style.display = 'none';
}

// 返信クリックでテキストエリアに代入
function reply(mid, id, nm) {
    var str = '[mid:' + mid + ' re:' + id + ']' + nm + "さん\n";

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

// 引用クリックでテキストエリアに代入
function quote(mid, id, nm, time, file) {
    var msg = document.input_form.elements['chatmsg_' + mid].value;
//    var str = '[引用 time:' + time + ' id:' + id + ' mid:' + mid + ']' + msg + "[/引用]\n";
//    var str = '[qt][time:' + time + ' id:' + id + ' mid:' + mid + ']' + msg + "[/qt]\n";

    if (file != '' && typeof file !== 'undefined') {
        var str = '[qt][time:' + time + ' id:' + id + ' mid:' + mid + '][upload]' + file + '[/upload]' + msg + "[/qt]\n";
    } else {
        var str = '[qt][time:' + time + ' id:' + id + ' mid:' + mid + ']' + msg + "[/qt]\n";
    }

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

//タスククリックでテキストエリアに代入
function task(mid, id, nm, time, file) {

    wn = '.open01';
    var mW = $(wn).find('.modalBody').innerWidth() / 2;
    var mH = $(wn).find('.modalBody').innerHeight() / 2;
    $(wn).find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
    $(wn).fadeIn(100);

    var msg = document.input_form.elements['chatmsg_' + mid].value;

    if (file != '' && typeof file !== 'undefined') {
        var str = '[qt][time:' + time + ' id:' + id + ' mid:' + mid + '][upload]' + file + '[/upload]' + msg + "[/qt]\n";
    } else {
        var str = '[qt][time:' + time + ' id:' + id + ' mid:' + mid + ']' + msg + "[/qt]\n";
    }

    var obj = $('#note-input');
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

// 編集がクリックされた時
function editMessage(mid) {
    edit_message_id = mid;
    var msg = document.getElementById('chatmsg_' + mid).value;

    document.getElementById('message-input').value = msg;
    document.getElementById('message-input').style.backgroundColor = '#dcf0fa';
    document.getElementById('edit_message').style.display = 'block';
//    document.getElementById('btn-chat').style.display = 'none';
//    document.getElementById('btn-chat2').style.display = 'block';
}
// 編集が閉じられた時
function closeEdit() {
    edit_message_id = 0;
    document.getElementById('message-input').value = '';
    document.getElementById('message-input').style.backgroundColor = '#ffffff';
    document.getElementById('edit_message').style.display = 'none';
//    document.getElementById('btn-chat').style.display = 'block';
//    document.getElementById('btn-chat2').style.display = 'none';
}

// アップロードされたファイルのプレビュー
$(function(){
    var setFileInput = $('.imgInput'),
    setFileImg = $('.imgView');

    setFileInput.each(function(){
        var selfFile = $(this),
        selfInput = $(this).find('input[type=file]'),
        prevElm = selfFile.find(setFileImg),
        orgPass = prevElm.attr('src');

        selfInput.change(function(){
            var file = $(this).prop('files')[0],
            fileRdr = new FileReader();

            document.getElementById('selectFile').style.display = 'none';
            document.getElementById('imgViewArea').style.display = 'block';
            document.getElementById('fileName').innerHTML = file.name;

            if (!this.files.length){
                console.log('こっち１？');
                prevElm.attr('src', orgPass);
                return;
            } else {
                console.log(file.type);
                if (!file.type.match('image.*')){
                    console.log('こっち２？');
                    prevElm.attr('src', orgPass);
                    return;
                } else {
                    console.log('こっち３？');
                    console.log(file);
                    fileRdr.onload = function() {
                        console.log('こっち4？');
                        prevElm.attr('src', fileRdr.result);
                    }
                    fileRdr.readAsDataURL(file);
                }
            }
        });
    });
});

// Loading の処理を止める
function stopload(message_id){
    //alert('wrap_' + message_id);
    $('#wrap_' + message_id).css('display', 'block');
    $('#loader-bg').delay(900).fadeOut(800);
    $('#loader_' + message_id).delay(600).fadeOut(300);
}
