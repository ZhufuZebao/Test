<template>
  <div>
    <!--ファイル一覧画面-->
    <el-tabs type="card" class="taskcontent chat-list-tabs chatfile-content">
      <el-tabs :tab-position="tabPosition">
        <el-tab-pane v-for="(chatFile,index) in chatFiles" v-bind:key="'chatFile*'+chatFile.id+index">
            <span slot="label">
              <p class="note">{{chatFile.file_name}}</p>
              <p><i class="el-icon-date"></i>
                {{dayFormat(chatFile.created_at)}}
                <span v-if="chatFile.user_deleted" round class="chatTask-users">{{chatFile.user_deleted.name}}</span>
              </p>
            </span>
          <!--ファイルdownLoad画面-->
          <section class="chatcontent chat-file">
            <ul class="rowfrist">
              <h3><i class="el-icon-chat-line-round"></i>
                {{dayFormat(chatFile.created_at)}}&nbsp;&nbsp;&nbsp;&nbsp;
                担当者：
                <span round class="chat-button-username" v-if="chatFile.user_deleted">{{chatFile.user_deleted.name}}</span>
                <el-button class="chatfile-move" @click.prevent="leaveToChat(chatFile)">チャットに移動</el-button>
              </h3>
            </ul>
            <ul class="rowcontent">
              <li><p class="noteDetail">{{chatFile.file_name}}</p></li>
            </ul>
            <ul class="rowbuttom">
              <el-button class="buttonleft"><a :href="'download/'+chatFile.group_id+'/'+encodeURIComponent(chatFile.file_name)" download="">ダウンロードする</a>
              </el-button>
              <el-button class="buttonright" @click="filePreview(chatFile.file_name,chatFile.group_id)">プレビュー</el-button>
            </ul>
          </section>
        </el-tab-pane>
      </el-tabs>
    </el-tabs>
  </div>
</template>

<script>
  import Messages from "../../mixins/Messages";
  import UserProfile from "../../components/common/UserProfile";
  import Calendar from "../../mixins/Calendar";

  export default {
    name: "ChatFile",
    mixins: [
      Messages,
    ],
    components: {
      UserProfile,
    },
    props: [
      'searchFileWord'
    ],
    data: function () {
      return {
        tabPosition: 'left',
        chatFiles: {},
        src: '',
        timer:'',
      }
    },
    methods: {
      checkStatus() {
        if (window.videoChat && !window.rejectFlag) {
          if (window.videoChat.callInFalg && !window.videoChat.group_type) {
            this.$emit('conversion');
            return false;
          }
        }
      },
      leaveToChat(chatFile) {
        if (chatFile.isProj){
          this.$emit('leaveToChat', {messageId: chatFile.messageId, pro: chatFile.group_id});
        } else{
          this.$emit('leaveToChat', {messageId: chatFile.messageId, groupId: chatFile.group_id});
        }
      },
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj === ""
      },
      //日付のフォーマット
      dayFormat(day) {
        if (!this.isEmpty(day)) {
          return Calendar.dateFormat(day, 'yyyy年MM月dd日');
        }
        return '';
      },
      //ファイルプレビュー
      filePreview: function (fileName,type) {
        let path = '';
        let fileType = fileName;
        let index = fileType.lastIndexOf(".");
        fileType = fileType.substring(index + 1, fileType.length);
        if (fileType === 'docx' || fileType === 'docm' || fileType === 'dotm' ||
            fileType === 'dotx' || fileType === 'xlsx' || fileType === 'xlsb' || fileType === 'xls'
            || fileType === 'xlsm'|| fileType === 'pptx'|| fileType === 'ppsx' || fileType === 'ppt' || fileType === 'pps' || fileType === 'pptm'
            || fileType === 'potm' || fileType === 'ppam' || fileType === 'potx' || fileType === 'ppsm') {
          path = "https://view.officeapps.live.com/op/view.aspx?" +
              "src="+window.location.origin+window.location.pathname+"file/"+type  +'/'+encodeURIComponent(fileName);
        } else {
          path = "file/"+type+'/'+encodeURIComponent(fileName);
        }
        window.open(path, '_blank');
      },
      // チャットファイル一覧画面
      fetchChatFileList: function (searchFileWord) {
        let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getChatFileList', {
          searchFileWord: searchFileWord
        }).then((res) => {
          let data = [];
          if (res.data) {
            for (let i = 0; i < res.data.length; i++) {
              let chatFileDate = res.data[i];
              chatFileDate.file_name = chatFileDate.file_name.split(',')[0].split(':')[0];
              data.push(chatFileDate);
            }
            this.chatFiles = data;
          }
        }).catch(error => {
          loading.close();
        });
        loading.close();
      }
    },
    mounted() {
      this.timer = setInterval(this.checkStatus, 500);
    },
    beforeDestroy() {
      clearInterval(this.timer);
    },
  }
</script>
