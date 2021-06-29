export default {
  //HTMLタグを処理
  HTMLDecode(text) {
    let temp = document.createElement("div");
    temp.innerHTML = text;
    let output = temp.innerText || temp.textContent;
    temp = null;
    return output;
  },

  quote(strMsg, fileName, group_id, taskFlag) {
    strMsg = this.HTMLDecode(strMsg);
    // 引用タグを置換
    strMsg = strMsg.replace(new RegExp(/\[qt\]/, "mg"), "<br>-------------<br>");
    strMsg = strMsg.replace(new RegExp(/\[\/qt\]/, "mg"), "<br>-------------<br>");
    let offset = 0;

    let msg =strMsg;
    let ids = [];
    if (localStorage.getItem('_group_file')){
      JSON.parse(localStorage.getItem('_group_file')).filter(item=>{
        if (item && item.mid){
          ids.push(item.mid);
        }
      });
    }
    // 引用する内容を編集
    while (true) {
      let qt_time = "";
      let pos = strMsg.indexOf("[time:", offset);
      if (pos === -1) {
        break;
      }

      let pos2 = strMsg.indexOf(']', pos + 1);
      if (pos2 === -1) {
          break;
      }
      let tmp = strMsg.substring(pos + 1, pos2);

      let s1 = strMsg.substring(0, pos);
      let s2 = strMsg.substring(pos2 + 1);
      strMsg = s1 + tmp + s2;
      let tmp2 = tmp.split(" ");
      let tmp4 = [];
      //tmp4=>time:0, id:1, mid:2
      for (let i = 0; i < tmp2.length; i++) {
        let item = tmp2[i];
        let tmp3 = item.split(":");
        if (tmp3.length > 0) {
            if(tmp3[1] > 0){
                tmp4.push(tmp3[1]);
            }else{
                strMsg=msg;
            }
        }
      }

      if (tmp4[0]) {
        try {
          qt_time = this.timestampToString(tmp4[0]);
        } catch (Exception) {
          alert(Exception)
        }
      }
      //  tmp = "[\\[" + tmp + "\\]]";
      let s = "";
//            s = "<"+ " " + username + " " + qt_time + "><br>";
      if(qt_time){
          if (strMsg.indexOf("から引用しました。") !== -1){
            strMsg=strMsg.replace(RegExp("から引用しました。\n", "g"), "から引用しました。<br>");
            strMsg=strMsg.replace(RegExp("<br><br>", "g"), "<br>");
            let i = qt_time.lastIndexOf(":");
            s = qt_time.substring(0, i);

          }else {
            s = "<" + " "  + qt_time + "><br>";
          }
          strMsg = strMsg.replace(tmp, s);
      }

      offset++;

    }

    strMsg = this.formatToMsg(strMsg);
    strMsg = this.formatReMsg(strMsg);

    // 返信の相手を編集
    let pattern = /\[mid:+[\d{1,}]+\]/g;
    return strMsg.replace(pattern,'[Re]');
    offset = 0;
    let oldLength = 0;
    let newLength = 0;
    //画像name表示
    while (true) {

      let pos = strMsg.indexOf("[upload]", offset);
      if (pos === -1) {
        break;
      }
      let item1 = []; //has fileName
      let item2 = []; //has fileSzie
      let pos2 = strMsg.indexOf("[/upload]", pos);
      if (pos2 === -1) {
          break;
      }
      let file_name = strMsg.substring((pos + 8), pos2);
      let files = file_name.split(',');
      newLength = files.length;
      for(let i = 0; i < files.length; i++) {
        if (files[i]) {
          if (files[i].split(':').length > 1) {
            item1.push(files[i].split(':')[0]);
            item2.push(files[i].split(':')[1])
          }else {
            item1.push(files[i].split(':')[0])
          }
        }
      }
      let resFileName = '';
      //upload mix pic
      if (newLength - oldLength > 1){
        for(let i = 0; i < (newLength - oldLength); i++) {
          resFileName = resFileName + item1[i] + '<br>';
        }
        //upload one pic
      }else{
        resFileName = item1[0] + '<br>';
      }
      oldLength = newLength;
      strMsg = strMsg.substring(0, pos) + resFileName + strMsg.substring(pos2 + 9);
      offset++;
    }

    if (taskFlag){
      strMsg = strMsg + this.formatImg(fileName,group_id)
    }
    return strMsg;
  },

  formatImg(fileName,group_id){
    //画像表示
    let copyFiles = [];
    let imgFiles = [];
    let offset = 0;
    let files = [];
    if (fileName) {
      files = fileName.split(',');
    }
    let fileLists = '';
    for (let i=0;i<files.length;i++){
      if (files[i]) {
        let type = files[i].split(':')[0].split('.');
        let extension = type[type.length - 1].toLowerCase();
        if (extension === ("jpg") || extension === ("jpeg") || extension === ("JPG") || extension === ("JPEG") ||
            extension === ("png") || extension === ("gif") ||
            extension === ("PNG") || extension === ("GIF")
        ) {
          imgFiles.push(files[i]);
        }else{
          copyFiles.push(files[i]);
        }
      }
    }
    if (imgFiles.length > 1){
      let imgOffset = 0;
      while(true) {
        if (imgFiles.length <= imgOffset) {
          break;
        }
        let file_name = imgFiles[imgOffset].split(':')[0];
        let size = this.getFileSize(imgFiles[imgOffset].split(':')[1]);
        let old_file_name = group_id+'/'+encodeURIComponent(file_name);
        let file =
            `<div v-if="` + file_name + `" class="files-download">
              <a href="download/` + old_file_name + `" target="_blank">
                <img src="file/` + old_file_name +`">
               </a>
            </div>`;
        fileLists = fileLists + file;
        imgOffset++;
      }
      fileLists = `<div class="text-wrapper-showImg">`+fileLists+`</div>`;
      files = copyFiles;
    }
    while(true) {

      if (files.length <= offset) {
        break;
      }
      let type = files[offset].split(':')[0].split('.');

      let preview_flg = false;
      let pdf_flg = false;
      if (type.length > 0) {
        let extension = type[type.length - 1].toLowerCase();
        if (extension === ("jpg") || extension === ("jpeg") || extension === ("JPG") || extension === ("JPEG") ||
            extension === ("png") || extension === ("gif") ||
            extension === ("PNG") || extension === ("GIF")
        ) {
          preview_flg = true;
        }else if(extension === ("pdf") || extension === ("PDF")){
          pdf_flg = true;
        }
      }
      //fileAndSize[0] =>fileName
      //fileAndSize[1] =>fileSize
      let size = '';

      let fileAndSize = files[offset].split(':');
      let file_name = fileAndSize[0];
      let old_file_name = group_id+'/'+encodeURIComponent(fileAndSize[0]);
      if (fileAndSize.length > 1){
        size = this.getFileSize(fileAndSize[1]);
      }else{
        //に対処する 古いデータ
        if (window._group_file && window._group_file.length && fileAndSize.length) {
          window._group_file.filter(item => {
            if (file_name === item.fileName) {
              size = this.getFileSize(item.fileSize);
              return;
            }
          });
        }else{
          size = '0KB';
        }
      }

      let file = null;
      if (preview_flg) {
        file = `<div v-if="` + file_name + `" class="file-download">
            <img src="file/` + old_file_name + `">
              <a href="download/` + old_file_name + `" target="_blank">
                ` + file_name + `(` + size + `)
              </a>
          </div>`;
      } else if(pdf_flg) {
        file = `<div v-if="` + file_name + `" class="file-download">
            <img src="file/` + old_file_name + `.png">
              <a href="download/` + old_file_name + `" target="_blank">
                ` + file_name + `(` + size + `)
              </a>
          </div>`;
      } else{
        file = `<div v-if="` + file_name + `" class="file-download">
            <img src="images/icon-file40.png">
              <a href="download/` + old_file_name + `" target="_blank">
                ` + file_name + `(` + size + `)
              </a>
          </div>`;
      }
      fileLists = fileLists + file
      offset++;
    }
    return fileLists;
  },

  timestampToString(tmsp) {
    Date.prototype.Format = function (fmt) {
      let o = {
        "M+": this.getMonth() + 1, // 月
        "d+": this.getDate(), // 日
        "h+": this.getHours(), // 時
        "m+": this.getMinutes(), // 分
        "s+": this.getSeconds(), // 秒
        "q+": Math.floor((this.getMonth() + 3) / 3),//四半期
        "S": this.getMilliseconds() // ミリ秒
      };
      if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + ""));
      for (let k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
      return fmt;
    }
    return new Date(parseInt(tmsp)*1000).Format('yy-MM-dd hh:mm:ss');
  },

  insertQuote(mid, id, time, msg, fname, taskFlag=0) {
    let sendMsg = '';
    msg = msg.replace(`<img src="./images/chat/101.png" width="16px" height="16px">`,`[icon:101]`);
    msg = msg.replace(`<img src="./images/chat/102.png" width="16px" height="16px">`,`[icon:102]`);

    time = (new Date(time.replace(/-/g, "/"))).getTime() / 1000;
    let offset = 0;
    let itemMessage="";
    if (taskFlag !== 0 || msg.indexOf("■タスク") !== -1){
      sendMsg = "[qt][time:" + time + " id:" + id
          + " mid:" + mid + "]" + msg + "[/qt]";
    }else {
      if (msg.indexOf("[time:") !== -1){
        let pos =  msg.indexOf("[time:", offset);

        let pos2 =  msg.indexOf("]", pos + 11);
        let tmp =  msg.substring(pos , pos2+1);
        itemMessage = msg.replace(tmp,"[time:" + time + " id:" + id
            + " mid:" + mid + "]");
        offset = pos2 + 1;
        sendMsg = "[qt]"+itemMessage+"[/qt]";

      }else{
        itemMessage = msg;
        sendMsg = "[qt][time:" + time + " id:" + id
            + " mid:" + mid + "]" +"から引用しました。"+ itemMessage + "[/qt]";
      }
      sendMsg = sendMsg.trim();
    }
    return sendMsg;
  },

  formatToMsg(strMsg) {
    // Toの相手を編集
    let pattern = /\[To:+[\d{1,}]+\]/g;
    return strMsg.replace(pattern,'[To]');
  },

  formatReMsg(strMsg){
    // 返信の相手を編集
    let pattern = /\[mid:+[\d{1,}]+\]/g;
    return strMsg.replace(pattern,'[Re]');
  },

  getFileSize(size=0){
    size = parseInt(size);
    let disp_size = "";
    if (size > 1024 * 1024) {
      let bd = ((size * 100)/(1024 * 1024 * 100)).toFixed(2);
      disp_size = bd + "MB";
    } else {
      let bd = ((size * 100)/(1024 * 100)).toFixed(2);
      disp_size = bd + "KB";
    }
    return disp_size;
  },
}
