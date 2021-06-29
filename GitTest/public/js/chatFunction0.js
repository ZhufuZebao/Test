// チャットの書き込み（マウスオーバー）
function mouseover(id){
//    document.getElementById("message_area_" + id).style.backgroundColor = "#f0fff0";
    document.getElementById("re_area_" + id).style.display = 'block';
}

//チャットの書き込み（マウスアウト）
function mouseout(id){
//    document.getElementById("message_area_" + id).style.backgroundColor = "#ffffff";
    document.getElementById("re_area_" + id).style.display = 'none';
}
